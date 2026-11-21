<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#140E0A">
  <meta name="color-scheme" content="dark">
  <title>Akses ditolak - {{ config('app.name', 'Daily Bun') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body class="masuk">

  <main class="masuk-kartu">
    <div class="masuk-merek">
      <span class="merek-ikon" aria-hidden="true">
        <img src="{{ asset('img/logo-icon.png') }}" alt="" width="42" height="42" decoding="async">
      </span>
      <span class="merek-teks">
        <strong>{{ config('app.name', 'Daily Bun') }}</strong>
        <small>Manajemen Toko</small>
      </span>
    </div>

    <h1 class="masuk-judul">Akses ditolak</h1>
    <p class="masuk-ket">Role akun Anda tidak memiliki akses ke halaman ini.</p>
    <p class="masuk-ket">Hubungi manajer atau admin bila akses ini dibutuhkan.</p>

    <a class="tombol utama tombol-lebar" href="{{ route('dashboard') }}">Kembali ke dashboard</a>
  </main>

</body>
</html>
