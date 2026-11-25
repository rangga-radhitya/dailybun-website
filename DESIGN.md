# Daily Bun: design system

Aplikasi manajemen toko roti (Operate). Penggunanya manajer, admin, kasir, dan
staff gudang yang bekerja berjam-jam dengan tabel, angka, dan form. Antarmuka
harus tenang, cepat dipindai, dan tidak bersaing dengan datanya.

Dial: **ENERGY 1 / RHYTHM 2 / MOTION 1**
- ENERGY 1: satu aksen biru, sisanya netral. Tidak ada dekorasi tanpa informasi.
- RHYTHM 2: pola halaman konsisten; dashboard punya satu momen (hero).
- MOTION 1: masuk sekali di dashboard, hover tipis pada yang bisa ditekan.

## Arah

Kaca beku di atas cahaya pagi. Latar adalah mesh biru pucat dengan dua sumber
hangat di sudut, ditutup butir halus 20%. Panel kerja berupa putih tembus
pandang dengan garis rambut `rgba(15,23,42,.09)` dan rim-light atas; bukan
kartu putih solid, bukan pula kaca di setiap permukaan.

Blur disimpan hanya untuk lapisan yang mengapung di atas konten: sidebar,
topbar, modal, dan kartu masuk. Kartu data, KPI, filter, dan tabel tetap tajam
tanpa `backdrop-filter`, supaya teks kecil tidak kabur dan gulir tetap ringan.

Warna espresso/karamel versi lama dihapus seluruhnya, begitu pula foto toko di
latar. Identitas dibawa oleh logo biru dan tipografi.

## Palet

Token di `public/css/style.css` `:root`.

| Token | Nilai | Peran | Kontras |
|---|---|---|---|
| `--latar` | `#EDF1F7` | dasar halaman | — |
| `--teks` | `#0E1524` | teks utama | 16.1:1 |
| `--teks-redup` | `#3F4B5F` | teks sekunder | 7.8:1 |
| `--teks-samar` | `#5A6678` | label, metadata | 5.1:1 |
| `--aksen` | `#1D4ED8` | isi tombol utama, cincin fokus | 6.7:1 dengan putih |
| `--aksen-terang` | `#3B6FF5` | hover tombol utama | — |
| `--aksen-teks` | `#1A45BD` | link dan aksen teks | 7.0:1 |
| `--ok` | `#14653B` | status aktif/sukses | 6.3:1 |
| `--peringatan` | `#8A4B08` | stok rendah | 6.0:1 |
| `--bahaya` | `#B42318` | hapus, gagal | 5.8:1 |
| `--info` | `#0B5C8A` | catatan netral | 6.4:1 |

Aturan:
- Satu aksen. Biru dipakai untuk aksi utama, link, item nav aktif, dan cincin
  fokus. Warna status tidak pernah menjadi dekorasi.
- `--aksen` hanya untuk bidang berisi teks putih; teks biru di atas latar
  terang memakai `--aksen-teks`.
- Lencana memakai tint latar 8–10% + teks versi gelap dari warna yang sama.

## Tipografi

| Peran | Keluarga | Ukuran |
|---|---|---|
| Judul halaman, judul kartu, hero | Bricolage Grotesque 650 | `clamp(1.55rem, 2.15vw, 1.95rem)` |
| Teks UI | Onest 400/500/600 | 15px, line-height 1.55 |
| Angka, kode, nominal | Spline Sans Mono 400–600 | `.72rem`–`2.9rem`, `tabular-nums` |

Judul memakai tracking `-.028em` sampai `-.045em`; teks kerja tetap netral.
Label kecil 10–12px, uppercase, tracking `.09em`–`.14em`, warna `--teks-samar`.
Deskripsi dibatasi 68ch. Font dimuat satu request dari Google Fonts; bila gagal,
fallback ke system UI dan teks tetap terbaca.

## Kaca dan latar

| Lapis | Elemen | Blur | Alasan |
|---|---|---|---|
| Latar | mesh 3 radial + grain 20% | — | memberi kedalaman pada kaca |
| 1 | sidebar | 22px, saturate 1.4 | panel mengapung, konten lewat di belakang |
| 1 | topbar | 18px, saturate 1.35 | sticky di atas konten |
| 2 | modal, kartu login/403/welcome | 28px, saturate 1.5 | fokus tunggal |
| 3 | kartu, KPI, filter, tabel, peringatan | tanpa blur | permukaan kerja |

Kaca memakai `rgba(255,255,255,.42–.92)` bertingkat, border hairline, dan
`inset 0 1px 0 rgba(255,255,255,.92)` sebagai tepi menyala. Sidebar dan kartu
masuk menambah pantulan diagonal `linear-gradient(126deg)` tipis. Bayangan
memakai tinta biru-gelap `rgba(15,23,42,…)` dengan blur lebar dan spread negatif,
bukan hitam pekat.

## Gerak

- Tombol: naik 1px saat hover, tekan `scale(.985)` 90ms.
- KPI dan kartu laporan yang bisa diklik: naik 2px, border membiru, bayangan
  naik. Kartu statis tidak bergerak.
- Dashboard: hero, kartu sisi, dan KPI masuk `translateY(10px)` + fade
  berurutan 50ms; dua kolom menyusul.
- `prefers-reduced-motion: reduce` menghentikan semua transform dan animasi.

## Komponen

- **Sidebar** 244px, mengapung (`inset: 12px auto 12px 12px`), radius 22px,
  kaca. Logo biru 36px + nama toko. Item 38px, pil putih penuh saat aktif
  dengan teks/ikon biru. Grup berlabel 10px uppercase. Kaki berisi titik status
  dan nama toko.
- **Topbar** 62px, sticky, kaca putih 52%. Breadcrumb 12px dengan pemisah titik;
  chip pengguna pil; tombol keluar sekunder kecil. Di bawah 1024px sidebar
  menjadi drawer dengan backdrop blur, remah disembunyikan, tombol menu muncul.
- **Kepala halaman**: judul + deskripsi + baris aksi, rata bawah.
- **KPI**: grid 4 kolom (3 di 1180px, 2 di 900px), tile kaca dengan label
  uppercase, angka mono 1.45rem, tautan panah yang bergeser saat hover.
- **Tabel**: header 10px uppercase di atas putih 50%, hairline antar-baris,
  hover baris biru 3.5%, `min-width: 700px` di dalam pembungkus gulir. Angka
  rata kanan dan tabular. Tabel terakhir di dalam kartu menyentuh tepi kartu.
- **Form**: input 42px (44px di layar sentuh), putih 74%, radius 8px, fokus
  cincin biru 3px. Label field 12px semibold; hint 11px.
- **Tombol**: primer biru solid dengan gradien atas 14%; sekunder kaca putih;
  bahaya tint merah. Aksi baris tabel berupa tombol hantu 30px tanpa bayangan.
- **Modal**: kaca radius 22px di atas backdrop `rgba(15,23,42,.30)` + blur 7px;
  kepala dan kaki menempel saat isi digulir; masuk `translateY(10px)` 200ms.
- **Peringatan**: hairline + tint + titik status di kiri teks, teks berwarna
  status. Varian `sukses`, `gagal`, `info`.
- **Lencana**: pil 999px dengan titik 5px; varian `aktif/sukses`, `nonaktif`,
  `netral`, `proses`, `bahaya`.
- **Kosong**: ikon garis biru 35% + satu kalimat, tanpa ilustrasi.

## Masuk dan akses

Login, 403, dan welcome memakai satu kartu kaca 408px (464px untuk welcome) di
tengah mesh, tanpa foto. Input 42px, satu tombol selebar kartu. Galat memakai
`aria-invalid` + warna bahaya dan daftar pesan di dalam kartu, bukan banner.
Halaman welcome tidak memuat Tailwind/Vite, supaya satu bahasa visual.

## Slot aset

| Aset | Lokasi | Catatan |
|---|---|---|
| Logo sumber | `public/img/logo-icon.png` | kotak krem; tidak dipakai di UI terang |
| Logo biru | `public/img/logo-biru.png` | transparan, untuk sidebar dan kartu masuk |
| Logo putih | `public/img/logo-putih.png` | transparan, cadangan untuk latar gelap |
| Foto toko | `public/img/background-bakery.jpg` | tidak dipakai CSS; disimpan untuk promosi |
| Ikon | `resources/views/components/ikon.blade.php` | garis 24px, stroke 1.7, `currentColor` |

## Aksesibilitas dan fallback

- Fokus keyboard: outline 2px `--aksen`, offset 2px.
- Target sentuh minimum 44px pada `pointer: coarse` (tombol, input, pagination,
  item sidebar, tombol tutup modal).
- `prefers-reduced-transparency: reduce` mengganti semua kaca dengan putih solid.
- `@supports not (backdrop-filter)` memakai latar opak untuk sidebar, topbar,
  modal, filter, peringatan, dan kartu masuk.
- Semua token teks lolos AA 4.5:1 pada ukuran aslinya.
- Cetak: latar putih, sidebar/topbar/filter/pagination disembunyikan, border abu.

## Berkas

- `public/css/style.css` — satu stylesheet, token di `:root`.
- `public/js/app.js` — drawer sidebar, modal, pencarian langsung, hitung KPI.
- `resources/views/layouts/app.blade.php` — kerangka sidebar/topbar/konten.
- `PRODUCT.md` — kebenaran produk (role, kriteria, batasan); bukan dokumen ini.
