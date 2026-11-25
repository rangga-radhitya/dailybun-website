# Daily Bun

Aplikasi manajemen toko roti untuk tugas besar PBD: mencatat penjualan,
pengadaan, penerimaan, retur, dan pergerakan stok, dengan hak akses per role.

## Pengguna

| Role | Pekerjaan utama | Modul |
|---|---|---|
| Manajer | mengawasi semua transaksi dan laporan | semua modul |
| Admin | merawat data master dan laporan | role, user, satuan, vendor, margin, barang, kartu stok, laporan |
| Kasir | melayani penjualan harian | penjualan |
| Staff Gudang | menerima barang dan memantau stok | barang, penerimaan, kartu stok |
| Staff Pembelian | mengadakan barang dari vendor | vendor, barang, pengadaan, kartu stok, laporan |

## Yang harus berhasil

- Kasir menyelesaikan satu transaksi penjualan dalam hitungan detik: pilih
  barang, jumlah, lihat subtotal, margin, PPN, dan total tanpa menghitung manual.
- Staf gudang tahu stok tersedia saat menerima atau menjual, dan kartu stok
  menjelaskan setiap perubahan.
- Manajer melihat kondisi hari ini (nilai penjualan, jumlah transaksi, margin
  aktif) dan ringkasan modul dalam satu layar.
- Setiap role hanya melihat menu dan halaman yang menjadi haknya; penolakan
  akses dijelaskan, bukan halaman kosong.

## Batasan

- Laravel + Blade, tanpa framework JavaScript. Interaksi memakai `public/js/app.js`.
- Bahasa antarmuka Indonesia, format angka dan mata uang Indonesia (Rp, titik
  ribuan, koma desimal).
- Data uang dan stok berasal dari server; UI tidak boleh mengarang angka.
- Aplikasi dipakai di desktop toko dan ponsel; tabel boleh menggulir horizontal,
  target sentuh minimal 44px.
- Basis data MySQL `projek_pbd_094`; sebagian halaman butuh koneksi aktif.

## Di luar cakupan

- Pendaftaran akun mandiri (hanya admin yang membuat user).
- Multi-toko, multi-cabang, dan sinkronisasi luring.
- Pembayaran daring dan integrasi printer struk.
