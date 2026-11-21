# Daily Bun: design system

Aplikasi manajemen toko roti (Operate). Penggunanya manajer toko yang bekerja
berjam-jam dengan tabel, angka, dan form. Kaca dan foto toko dipakai sebagai
suasana, bukan sebagai hiasan yang mengganggu kerja.

Dial: **ENERGY 2 / RHYTHM 2 / MOTION 2**
- ENERGY 2: hangat dan punya karakter, tetapi tidak berteriak.
- RHYTHM 2: pola konsisten untuk kerja, dengan satu momen (dashboard) yang beda.
- MOTION 2: transisi halus dan satu momen masuk; bukan animasi di mana-mana.

## Identitas

Roti artisan: kerak espresso, remah krim, karamel mentega. Latar gelap dibuat
dari warna kerak, bukan hitam netral, supaya foto roti terasa satu dunia dengan
antarmuka. Satu aksen saja (karamel) untuk aksi, angka penting, dan fokus.

## Palet

Tiga warna inti (R-29): espresso, krim, karamel. Warna status hanya dipakai
pada lencana dan peringatan, bukan sebagai dekorasi.

| Token | Hex | Peran | Alasan |
|---|---|---|---|
| `--latar` | `#140E0A` | latar halaman | kerak gelap; membuat foto roti dan karamel menonjol |
| `--latar-2` | `#1A120E` | latar lapis kedua | pemisah halus tanpa garis keras |
| `--latar-3` | `#231A14` | isi dropdown, permukaan pekat | tetap dalam keluarga coklat |
| `--teks` | `#FAF2E6` | teks utama | krim susu, 16.5:1 di atas latar |
| `--teks-redup` | `rgba(250,242,230,.80)` | teks sekunder | 5.9:1 di area foto paling terang |
| `--teks-samar` | `rgba(250,242,230,.68)` | label kecil, metadata | 5.1:1, lolos AA untuk teks kecil |
| `--neon` | `#E8A24A` | aksen utama | karamel mentega; 8.7:1 di atas latar |
| `--neon-terang` | `#F7C67B` | aksen hover, angka besar | mentega cair |
| `--karamel` | `#B9772F` | aksen tekan, gradasi lambang | kerak panggang |
| `--krim` | `#F6E3C8` | teks di atas karamel | kontras lembut, tidak putih keras |
| `--gula` | `#FFF6E9` | ring fokus | gula halus; terlihat di atas tombol karamel |
| `--ok` | `#A8C6A0` | status aktif | sage, bukan hijau neon |
| `--bahaya` | `#E98A73` | hapus, gagal | terakota, bukan merah alarm |
| `--info` | `#9FC7B4` | catatan netral | sage-teal, bukan biru dingin |
| `--peringatan` | `#E8C368` | stok rendah | gandum |

### Catatan riset

Palet toko roti yang lazim dan terbukti terbaca adalah keluarga coklat hangat,
krim, dan emas: kerak, mentega, karamel, gula. Rentang itu yang dipakai di sini.
Palet pastel (pink, mint, lavender) cocok untuk toko kue dan dessert, bukan untuk
aplikasi kerja yang menampilkan tabel angka sepanjang hari. Karena itu aksen
dibatasi satu warna karamel; warna lain hanya muncul sebagai status.

## Kaca

Kaca bertahap. Blur hanya di dua elemen yang mengapung; permukaan kerja
memakai kaca tembus tanpa blur, terasa dari transparansi, rim light, dan
refleksi tipis.

| Lapis | Elemen | Blur | Alasan |
|---|---|---|---|
| 1 | sidebar | 22px | kerangka mengapung di atas foto |
| 2 | modal | 28px | fokus tunggal di atas segalanya |
| 3 | kartu, panel, filter, tabel, KPI | tanpa blur | permukaan kerja; foto tetap terasa lewat transparansi |

Ini mengikuti R-10: glassmorphism sebagai aksen 1-2 elemen, bukan seluruh UI.
Efek sampingnya baik: lapisan blur berkurang banyak sehingga scroll lebih ringan,
dan panel kerja tidak lagi terasa seperti template kaca.

Bayangan membawa offset dan blur. Elevasi hanya untuk elemen yang memang bisa
ditekan (KPI, kartu laporan). Panel statis tidak terangkat saat hover karena
tidak bisa diklik.

## Gerak

- Tombol: kilau tipis sekali jalan, angkat 2px, tekan 1px dan mengecil 0.975 dengan 90ms.
- Kartu statis: sorot kursor + tepi menghangat. Tidak terangkat.
- Kartu yang bisa diklik: terangkat 2px, bayangan naik, panah bergeser.
- Satu momen masuk di dashboard (hero, KPI berurutan, dua kolom).
- Semua gerak berhenti pada `prefers-reduced-motion: reduce`.

## Masuk dan akses

Login dan halaman 403 memakai satu kartu `.masuk` di atas foto toko, tanpa blur
tambahan: fokusnya satu form, bukan lapisan kaca. Kartu dibatasi 380px, input
44px, satu tombol selebar kartu. Galat login memakai warna bahaya dan
`aria-invalid`, bukan banner. Halaman 403 memakai kartu yang sama dengan satu
tombol kembali, supaya penolakan akses terasa bagian dari aplikasi.

## Slot aset

| Aset | Lokasi | Catatan |
|---|---|---|
| Foto toko roti | `public/img/background-bakery.jpg` | interior toko roti; ganti berkas dengan nama yang sama. Rasio 3:2 atau 16:9, sisi terpanjang 1920px, di bawah 400KB. Bagian tengah foto yang paling penting karena dipotong `cover`. Versi sekarang 960x538 dan masih ada watermark, ganti dengan versi berlisensi bila dipakai presentasi. |
| Logo | `public/img/logo.png` (asli 1024x1024) dan `public/img/logo-icon.png` (potongan lambang 256x256 untuk sidebar dan favicon) | dipakai di `.merek-ikon` pada `resources/views/layouts/app.blade.php`. Jangan ganti tanpa arahan. |

Kalau nama berkas berubah, ubah `--foto-toko` di `public/css/style.css`.
