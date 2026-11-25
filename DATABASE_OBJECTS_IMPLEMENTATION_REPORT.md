# DATABASE_OBJECTS_IMPLEMENTATION_REPORT — Daily Bun

Tahap: implementasi **3 Function, 2 Stored Procedure, dan 2 Trigger** yang dapat didemonstrasikan langsung dari frontend Laravel (tanpa `SQLSTATE`/`SIGNAL`, tanpa Eloquent ORM, dan tanpa Query Builder).

| Objek | Nama | Dipakai di frontend |
|---|---|---|
| FUNCTION | `fn_hitung_subtotal(harga_satuan, jumlah)` | perhitungan sub total saat menyimpan pengadaan |
| FUNCTION | `fn_hitung_ppn(subtotal, persen)` | perhitungan PPN saat menyimpan pengadaan & penjualan |
| FUNCTION | `fn_stok_barang(idbarang)` | menampilkan stok terkini di `/barang` & `/penjualan/create` |
| PROCEDURE | `sp_aktifkan_margin_penjualan(idmargin)` | tombol **Aktifkan** di `/margin_penjualan` |
| PROCEDURE | `sp_ubah_status_barang(idbarang)` | tombol ubah status aktif/nonaktif di `/barang` |
| TRIGGER | `trg_detail_penerimaan_after_insert` | otomatis saat detail penerimaan dibuat (stok masuk `'M'`) |
| TRIGGER | `trg_detail_penjualan_after_insert` | otomatis saat detail penjualan dibuat (stok keluar `'K'`) |

Skema tabel, kolom, index, dan data tidak diubah.

---

## 1. Functions

### `fn_hitung_subtotal(p_harga, p_jumlah)`
- Mengembalikan hasil kali `p_harga * p_jumlah` sebagai `INT`.
- Nilai kosong/`NULL` atau negatif dianggap `0` memakai `CASE` tanpa percabangan `IF`.
- Bersifat `DETERMINISTIC` dan `NO SQL`.
- Dipakai di `PengadaanController::tambah()` untuk mengisi `detail_pengadaan.sub_total`.

### `fn_hitung_ppn(p_subtotal, p_persen)`
- Mengembalikan `ROUND(subtotal * persen / 100)` sebagai `INT`.
- Nilai kosong/`NULL` atau negatif dianggap `0` memakai `CASE` tanpa percabangan `IF`.
- Bersifat `DETERMINISTIC` dan `NO SQL`.
- Dipakai di `PengadaanController::tambah()` dan `PenjualanController::tambah()` dengan tarif tetap `Controller::PPN_PERSEN = 11`.

### `fn_stok_barang(p_idbarang)`
- Mengembalikan saldo `stock` terakhir dari `kartu_stok` untuk `idbarang` terkait (`COALESCE(..., 0)` bila belum ada riwayat).
- Bersifat `READS SQL DATA`.
- Dipakai di `BarangController::daftar()` dan `PenjualanController::barangAktif()`.

---

## 2. Stored Procedures

### `sp_aktifkan_margin_penjualan(p_idmargin)`
1. Semua margin yang sedang aktif (`status = 1`) dinonaktifkan, kecuali margin yang dipilih.
2. Margin yang dipilih diaktifkan (`status = 1`) dan `updated_at` diperbarui.
- Dipanggil `MarginPenjualanController::aktifkan()` saat tombol **Aktifkan** ditekan di `/margin_penjualan`.

### `sp_ubah_status_barang(p_idbarang)`
- Mengubah `status` barang pada tabel `barang` (`1` menjadi `0`, selain `1` menjadi `1`).
- Dipanggil `BarangController::status()` saat tombol ubah status ditekan di `/barang`.

---

## 3. Triggers

### `trg_detail_penerimaan_after_insert`
- `AFTER INSERT ON detail_penerimaan`, satu baris `kartu_stok` per detail (`jenis_transaksi = 'M'`, `masuk = NEW.jumlah_terima`, `keluar = 0`, `stock = saldo terakhir + jumlah_terima`, `idtransaksi = NEW.idpenerimaan`, `idbarang = NEW.barang_idbarang`).
- Baris `barang` dikunci (`FOR UPDATE`) agar dua penerimaan bersamaan tetap berurutan.

### `trg_detail_penjualan_after_insert`
- `AFTER INSERT ON detail_penjualan`, satu baris `kartu_stok` per detail (`jenis_transaksi = 'K'`, `masuk = 0`, `keluar = NEW.jumlah`, `stock = saldo terakhir - jumlah`, `idtransaksi = NEW.penjualan_idpenjualan`, `idbarang = NEW.idbarang`).
- Baris `barang` dikunci (`FOR UPDATE`) agar perhitungan saldo berurutan.
- Aplikasi (`PenjualanController::tambah()`) tetap memvalidasi kecukupan stok sebelum insert, lalu hanya menyisipkan `detail_penjualan` tanpa menulis `kartu_stok` manual agar tidak tercatat ganda.

Kedua trigger **tidak melakukan validasi maupun `SQLSTATE`**. Validasi data tetap dilakukan aplikasi di controller masing-masing.

---

## 4. Penghapusan Penerimaan & Penjualan

- `PenerimaanController::destroy()` menolak bila penerimaan sudah punya retur, lalu menulis baris koreksi `'K'` ke `kartu_stok` dan menghapus detail serta header penerimaan.
- `PenjualanController::destroy()` menulis baris pengembalian `'M'` ke `kartu_stok` dan menghapus detail serta header penjualan.

---

## 5. Sumber SQL & Pengujian

- Definisi ketujuh objek: `database/sql/routines.sql` (aman dijalankan ulang, memakai `DROP ... IF EXISTS`).
- Skrip uji: `database/sql/routines_test.sql` (dijalankan di database uji `projek_pbd_094_uji`, seluruh data uji di-`ROLLBACK`).
- Berisi 11 uji function (`A1`–`A11`), 5 uji trigger & stok (`B1`–`B5`), 4 uji procedure (`C1`–`C4`), dan 1 uji rollback (`D`). Kolom `hasil` bernilai `LULUS`/`GAGAL`.

---

## 6. Catatan

1. `kartu_stok.idtransaksi` bertipe `BIGINT`, selaras dengan `penerimaan.idpenerimaan` (`BIGINT`) dan `penjualan.idpenjualan` (`INT`).
2. Trigger hanya menangani `INSERT` pada `detail_penerimaan` dan `detail_penjualan`.
3. Retur menulis `kartu_stok` dari aplikasi (`ReturController`: `R` saat retur, `M` saat retur dihapus) dan tidak memakai trigger.
4. Kode `jenis_transaksi`: `M` = masuk (trigger penerimaan / hapus penjualan / hapus retur), `K` = keluar/koreksi (trigger penjualan / hapus penerimaan), `R` = retur (aplikasi).
5. Tidak ada `SQLSTATE`/`SIGNAL` maupun percabangan `IF` di objek database mana pun. Penolakan data tidak valid dilakukan aplikasi.
6. Seluruh query di aplikasi menggunakan raw SQL (`DB::select`, `DB::selectOne`, `DB::insert`, `DB::update`, `DB::delete`) tanpa Eloquent ORM maupun Query Builder.
