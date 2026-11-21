<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use PDOException;
use RuntimeException;

class PenjualanController extends Controller
{
    public function index(Request $request): View
    {
        $q = trim((string) $request->query('q', ''));

        $daftar = $this->daftar($q, 15, $request);

        return view('penjualan.index', compact('daftar', 'q'));
    }

    public function show(int $id): View
    {
        $penjualan = $this->cari($id);

        abort_if($penjualan === null, 404);

        $totalDetail = $this->totalPenjualan($id);

        return view('penjualan.show', compact('penjualan', 'totalDetail'));
    }

    public function create(): View
    {
        $daftarBarang = $this->barangAktif();
        $margin = $this->marginAktif();

        return view('penjualan.create', compact('daftarBarang', 'margin'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate(
            [
                'items' => ['required', 'array', 'min:1'],
                'items.*.idbarang' => ['required', 'integer'],
                'items.*.jumlah' => ['required', 'integer', 'min:1'],
            ],
            [],
            [
                'items' => 'daftar barang',
                'items.*.idbarang' => 'barang',
                'items.*.jumlah' => 'jumlah',
            ]
        );

        $idBarang = array_map(fn ($i) => (int) $i['idbarang'], $data['items']);

        if (count($idBarang) !== count(array_unique($idBarang))) {
            throw ValidationException::withMessages(['items' => 'Satu barang tidak boleh dipilih lebih dari sekali.']);
        }

        // Harga satuan tidak diambil dari form: selalu pakai harga master barang
        // agar tidak bisa diubah manual maupun lewat manipulasi request.
        $items = [];
        foreach ($data['items'] as $item) {
            $barang = $this->cariBarang((int) $item['idbarang']);

            if ($barang === null || (int) $barang->status !== 1) {
                throw ValidationException::withMessages(['items' => 'Ada barang yang tidak ditemukan atau sudah nonaktif.']);
            }

            $items[] = [
                'idbarang' => (int) $barang->idbarang,
                'harga_satuan' => (int) $barang->harga,
                'jumlah' => (int) $item['jumlah'],
            ];
        }

        $margin = $this->marginAktif();

        try {
            $id = $this->tambah(
                $this->idUser(),
                $margin === null ? null : (int) $margin->idmargin_penjualan,
                $margin === null ? 0.0 : (float) $margin->persen,
                $items,
                self::PPN_PERSEN
            );
        } catch (PDOException $e) {
            return back()->withInput()->with('gagal', $e->errorInfo[2] ?? 'Penjualan gagal disimpan.');
        } catch (RuntimeException $e) {
            return back()->withInput()->with('gagal', $e->getMessage());
        }

        return redirect()->route('penjualan.show', $id)->with('sukses', 'Penjualan berhasil disimpan dan stok sudah diperbarui.');
    }

    public function destroy(int $id): RedirectResponse
    {
        if ($this->cari($id) === null) {
            return redirect()->route('penjualan.index')->with('gagal', 'Penjualan tidak ditemukan.');
        }

        try {
            DB::transaction(function () use ($id) {
                $baris = DB::select(
                    'SELECT idbarang, jumlah FROM detail_penjualan
                     WHERE penjualan_idpenjualan = ?
                     ORDER BY iddetail_penjualan',
                    [$id]
                );

                foreach ($baris as $b) {
                    $idbarang = $b->idbarang === null ? null : (int) $b->idbarang;
                    $jumlah = (int) $b->jumlah;
                    $saldo = $idbarang === null ? 0 : $this->saldoTerakhir($idbarang);

                    DB::insert(
                        'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
                         VALUES (?, ?, 0, ?, NOW(), ?, ?)',
                        ['M', $jumlah, $saldo + $jumlah, $id, $idbarang]
                    );
                }

                DB::delete('DELETE FROM detail_penjualan WHERE penjualan_idpenjualan = ?', [$id]);
                DB::delete('DELETE FROM penjualan WHERE idpenjualan = ?', [$id]);
            });
        } catch (PDOException $e) {
            return redirect()->route('penjualan.index')->with('gagal', $e->errorInfo[2] ?? 'Penjualan gagal dihapus.');
        } catch (RuntimeException $e) {
            return redirect()->route('penjualan.index')->with('gagal', $e->getMessage());
        }

        return redirect()->route('penjualan.index')->with('sukses', 'Penjualan dihapus dan stok dikembalikan.');
    }

    private function daftar(string $q, int $perHalaman, Request $request): LengthAwarePaginator
    {
        $sql = "SELECT p.idpenjualan, p.subtotal_nilai, p.ppn, p.total_nilai,
                       DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS tanggal,
                       u.username, m.persen,
                       ROUND((SELECT COALESCE(SUM(d.subtotal), 0)
                              FROM detail_penjualan d
                              WHERE d.penjualan_idpenjualan = p.idpenjualan) * m.persen / 100) AS margin_nilai,
                       (SELECT COUNT(*) FROM detail_penjualan d WHERE d.penjualan_idpenjualan = p.idpenjualan) AS detail_count
                FROM penjualan p
                LEFT JOIN `user` u ON u.iduser = p.iduser
                LEFT JOIN margin_penjualan m ON m.idmargin_penjualan = p.idmargin_penjualan";
        $where = [];
        $bind = [];

        if ($q !== '') {
            if (ctype_digit($q)) {
                $where[] = '(p.idpenjualan = ? OR u.username LIKE ?)';
                $bind[] = (int) $q;
                $bind[] = '%'.$q.'%';
            } else {
                $where[] = 'u.username LIKE ?';
                $bind[] = '%'.$q.'%';
            }
        }

        if ($where !== []) {
            $sql .= ' WHERE '.implode(' AND ', $where);
        }

        $sql .= ' ORDER BY p.idpenjualan';

        $perHalaman = max(1, $perHalaman);
        $total = (int) (DB::selectOne('SELECT COUNT(*) AS jumlah FROM ('.$sql.') AS hasil', $bind)->jumlah ?? 0);

        $halaman = max(1, (int) LengthAwarePaginator::resolveCurrentPage());
        $offset = ($halaman - 1) * $perHalaman;

        $baris = $total === 0
            ? []
            : DB::select($sql.' LIMIT '.$perHalaman.' OFFSET '.$offset, $bind);

        return new LengthAwarePaginator($baris, $total, $perHalaman, $halaman, [
            'path' => $request->url(),
            'query' => $request->query(),
        ]);
    }

    private function cari(int $id): ?object
    {
        $header = DB::selectOne(
            "SELECT p.idpenjualan, p.subtotal_nilai, p.ppn, p.total_nilai, p.idmargin_penjualan,
                    DATE_FORMAT(p.created_at, '%d/%m/%Y %H:%i') AS tanggal,
                    u.username, m.persen,
                    ROUND((SELECT COALESCE(SUM(d.subtotal), 0)
                           FROM detail_penjualan d
                           WHERE d.penjualan_idpenjualan = p.idpenjualan) * m.persen / 100) AS margin_nilai
             FROM penjualan p
             LEFT JOIN `user` u ON u.iduser = p.iduser
             LEFT JOIN margin_penjualan m ON m.idmargin_penjualan = p.idmargin_penjualan
             WHERE p.idpenjualan = ?",
            [$id]
        );

        if ($header === null) {
            return null;
        }

        $header->detail = DB::select(
            'SELECT d.iddetail_penjualan, d.idbarang, d.harga_satuan, d.jumlah, d.subtotal,
                    b.nama, s.nama_satuan
             FROM detail_penjualan d
             LEFT JOIN barang b ON b.idbarang = d.idbarang
             LEFT JOIN satuan s ON s.idsatuan = b.idsatuan
             WHERE d.penjualan_idpenjualan = ?
             ORDER BY d.iddetail_penjualan',
            [$id]
        );

        return $header;
    }

    /**
     * Total satu transaksi penjualan dari detail (jumlah × harga satuan).
     * Null bila transaksi tidak ada, "0" bila transaksi belum punya detail.
     */
    private function totalPenjualan(int $idpenjualan): ?string
    {
        $ada = DB::selectOne('SELECT 1 AS x FROM penjualan WHERE idpenjualan = ?', [$idpenjualan]);

        if ($ada === null) {
            return null;
        }

        $total = DB::selectOne(
            'SELECT COALESCE(SUM(subtotal), 0) AS total
             FROM detail_penjualan
             WHERE penjualan_idpenjualan = ?',
            [$idpenjualan]
        )->total;

        return (string) $total;
    }

    /**
     * Simpan header + detail penjualan dalam satu transaksi, lalu kurangi stok.
     * Margin aktif ditambahkan ke subtotal barang sebelum PPN dihitung.
     *
     * @param  array<int, array{idbarang:int, harga_satuan:int, jumlah:int}>  $items
     */
    private function tambah(int $iduser, ?int $idmargin, float $marginPersen, array $items, int $ppnPersen): int
    {
        return DB::transaction(function () use ($iduser, $idmargin, $marginPersen, $items, $ppnPersen) {
            DB::insert(
                'INSERT INTO penjualan (created_at, subtotal_nilai, ppn, total_nilai, iduser, idmargin_penjualan)
                 VALUES (NOW(), 0, 0, 0, ?, ?)',
                [$iduser, $idmargin]
            );
            $id = (int) DB::getPdo()->lastInsertId();

            foreach ($items as $item) {
                $idbarang = (int) $item['idbarang'];
                $harga = (int) $item['harga_satuan'];
                $jumlah = (int) $item['jumlah'];

                if ($jumlah <= 0) {
                    throw new RuntimeException('Jumlah barang penjualan harus lebih dari 0.');
                }

                if ($harga < 0) {
                    throw new RuntimeException('Harga satuan tidak boleh negatif.');
                }

                $saldo = $this->saldoTerakhir($idbarang);

                if ($saldo < $jumlah) {
                    $barang = DB::selectOne('SELECT nama FROM barang WHERE idbarang = ?', [$idbarang]);

                    throw new RuntimeException(
                        'Stok '.($barang->nama ?? 'barang #'.$idbarang).' tidak cukup (tersedia '.$saldo.', diminta '.$jumlah.').'
                    );
                }

                $subTotal = $harga * $jumlah;

                DB::insert(
                    'INSERT INTO detail_penjualan (harga_satuan, jumlah, subtotal, penjualan_idpenjualan, idbarang)
                     VALUES (?, ?, ?, ?, ?)',
                    [$harga, $jumlah, $subTotal, $id, $idbarang]
                );

                DB::insert(
                    'INSERT INTO kartu_stok (jenis_transaksi, masuk, keluar, stock, created_at, idtransaksi, idbarang)
                     VALUES (?, 0, ?, ?, NOW(), ?, ?)',
                    ['K', $jumlah, $saldo - $jumlah, $id, $idbarang]
                );
            }

            $subtotalBarang = (int) DB::selectOne(
                'SELECT COALESCE(SUM(subtotal), 0) AS total FROM detail_penjualan WHERE penjualan_idpenjualan = ?',
                [$id]
            )->total;

            $marginNilai = (int) round($subtotalBarang * $marginPersen / 100);
            $subtotal = $subtotalBarang + $marginNilai;
            $ppn = (int) round($subtotal * $ppnPersen / 100);

            DB::update(
                'UPDATE penjualan SET subtotal_nilai = ?, ppn = ?, total_nilai = ? WHERE idpenjualan = ?',
                [$subtotal, $ppn, $subtotal + $ppn, $id]
            );

            return $id;
        });
    }

    /**
     * Saldo stok terakhir satu barang. Baris barang dikunci lebih dulu agar
     * perhitungan saldo berurutan dan tidak saling menimpa.
     */
    private function saldoTerakhir(int $idbarang): int
    {
        DB::selectOne('SELECT idbarang FROM barang WHERE idbarang = ? FOR UPDATE', [$idbarang]);

        $baris = DB::selectOne(
            'SELECT stock FROM kartu_stok WHERE idbarang = ? ORDER BY idkartu_stok DESC LIMIT 1',
            [$idbarang]
        );

        return $baris === null ? 0 : (int) $baris->stock;
    }

    /** @return array<int, object> */
    private function barangAktif(): array
    {
        return DB::select(
            "SELECT b.idbarang, b.nama, b.harga, s.nama_satuan,
                    COALESCE((SELECT ks.stock FROM kartu_stok ks
                               WHERE ks.idbarang = b.idbarang
                               ORDER BY ks.idkartu_stok DESC LIMIT 1), 0) AS stok
             FROM barang b
             LEFT JOIN satuan s ON s.idsatuan = b.idsatuan
             WHERE b.status = 1
             ORDER BY b.nama"
        );
    }

    private function cariBarang(int $id): ?object
    {
        return DB::selectOne('SELECT idbarang, nama, harga, status FROM barang WHERE idbarang = ?', [$id]);
    }

    private function marginAktif(): ?object
    {
        return DB::selectOne(
            'SELECT idmargin_penjualan, persen FROM margin_penjualan WHERE status = 1 ORDER BY idmargin_penjualan LIMIT 1'
        );
    }
}
