<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#F2F5FA">
  <meta name="color-scheme" content="light">
  <title>{{ config('app.name', 'Daily Bun') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="masuk">

  <main class="masuk-kartu lebar">
    <div class="masuk-merek">
      <span class="merek-ikon" aria-hidden="true">
        <img src="{{ asset('img/logo-biru.png') }}" alt="" width="42" height="42" decoding="async">
      </span>
      <span class="merek-teks">
        <strong>{{ config('app.name', 'Daily Bun') }}</strong>
        <small>Manajemen Toko</small>
      </span>
    </div>

    <h1 class="masuk-judul">Manajemen toko roti</h1>
    <p class="masuk-ket">Penjualan, pengadaan, penerimaan, dan stok dalam satu tempat. Masuk dengan akun yang diberikan admin toko.</p>

    <div class="masuk-aksi">
      @auth
        <a class="tombol utama tombol-lebar" href="{{ url('/dashboard') }}">
          Buka dashboard
        </a>
      @else
        <a class="tombol utama tombol-lebar" href="{{ route('login') }}">
          Masuk
        </a>
      @endauth
    </div>

    <p class="masuk-kaki">Daily Bun · Manajemen Toko Roti</p>
  </main>

</body>
</html>
