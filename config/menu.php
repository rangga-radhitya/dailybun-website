<?php

/*
 * Daftar menu sidebar (satu sumber). Dibaca oleh resources/views/layouts/app.blade.php.
 *
 * - 'route'  : nama route (di-resolve di Blade, bukan di sini).
 * - 'params' : parameter route (opsional).
 * - 'match'  : pola path untuk penanda menu aktif (request()->is()).
 * - 'ikon'   : nama ikon di resources/views/components/ikon.blade.php.
 * - 'modul'  : kunci modul di config/akses.php. Entri tanpa 'modul' selalu
 *              tampil untuk semua user yang sudah login (Dashboard).
 * - Entri tanpa 'items' adalah menu tunggal; entri dengan 'items' adalah grup
 *   (ditampilkan sebagai judul bagian + daftar menu, bukan dropdown).
 *
 * Halaman "Detail ..." tidak masuk sidebar: dibuka dari baris pada halaman daftar.
 * Halaman laporan sengaja tidak ditampilkan di sidebar pada tahap ini.
 */
return [
    [
        'label' => 'Dashboard',
        'route' => 'dashboard',
        'match' => ['dashboard'],
        'ikon' => 'dashboard',
    ],
    [
        'label' => 'Tabel Master',
        'items' => [
            ['label' => 'Role',             'modul' => 'role',       'route' => 'role',             'match' => ['role', 'role/*'],                         'ikon' => 'role'],
            ['label' => 'User',             'modul' => 'user',       'route' => 'user',             'match' => ['user', 'user/*'],                         'ikon' => 'user'],
            ['label' => 'Satuan',           'modul' => 'satuan',     'route' => 'satuan',           'match' => ['satuan', 'satuan/*'],                     'ikon' => 'satuan'],
            ['label' => 'Vendor',           'modul' => 'vendor',     'route' => 'vendor',           'match' => ['vendor', 'vendor/*'],                     'ikon' => 'vendor'],
            ['label' => 'Margin Penjualan', 'modul' => 'margin',     'route' => 'margin_penjualan', 'match' => ['margin_penjualan', 'margin_penjualan/*'], 'ikon' => 'margin'],
            ['label' => 'Barang',           'modul' => 'barang',     'route' => 'barang',           'match' => ['barang', 'barang/*'],                     'ikon' => 'barang'],
        ],
    ],
    [
        'label' => 'Transaksi',
        'items' => [
            ['label' => 'Pengadaan',  'modul' => 'pengadaan',  'route' => 'pengadaan.index',  'match' => ['pengadaan', 'pengadaan/*'],   'ikon' => 'pengadaan'],
            ['label' => 'Penerimaan', 'modul' => 'penerimaan', 'route' => 'penerimaan.index', 'match' => ['penerimaan', 'penerimaan/*'], 'ikon' => 'penerimaan'],
            ['label' => 'Penjualan',  'modul' => 'penjualan',  'route' => 'penjualan.index',  'match' => ['penjualan', 'penjualan/*'],   'ikon' => 'penjualan'],
            ['label' => 'Retur',      'modul' => 'retur',      'route' => 'retur.index',      'match' => ['retur', 'retur/*'],           'ikon' => 'retur'],
            ['label' => 'Kartu Stok', 'modul' => 'kartu_stok', 'route' => 'kartu_stok',       'match' => ['kartu_stok', 'kartu_stok/*'], 'ikon' => 'kartu_stok'],
        ],
    ],
];
