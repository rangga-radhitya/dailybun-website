<?php

/*
 * Matriks akses per role.
 *
 * Key adalah idrole pada tabel `role`, nilai adalah daftar modul yang boleh
 * dibuka. Dashboard tidak dicantumkan karena semua user yang sudah login
 * boleh membukanya. Role baru yang tidak ada di sini hanya mendapat Dashboard.
 *
 * Modul harus sama dengan nama yang dipakai middleware `akses:...` di
 * routes/web.php dan kunci `modul` di config/menu.php.
 */
return [
    // Manajer
    1 => [
        'role', 'user', 'satuan', 'vendor', 'margin', 'barang',
        'pengadaan', 'penerimaan', 'penjualan', 'retur', 'kartu_stok', 'laporan',
    ],

    // Admin
    2 => [
        'role', 'user', 'satuan', 'vendor', 'margin', 'barang',
        'kartu_stok', 'laporan',
    ],

    // Kasir
    3 => [
        'penjualan',
    ],

    // Staff Gudang
    4 => [
        'barang', 'penerimaan', 'kartu_stok',
    ],

    // Staff Pembelian
    5 => [
        'vendor', 'barang', 'pengadaan', 'kartu_stok', 'laporan',
    ],
];
