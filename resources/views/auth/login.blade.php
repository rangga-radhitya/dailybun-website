<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#140E0A">
  <meta name="color-scheme" content="dark">
  <title>Masuk - {{ config('app.name', 'Daily Bun') }}</title>
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

    <h1 class="masuk-judul">Masuk</h1>
    <p class="masuk-ket">Gunakan akun yang diberikan admin toko.</p>

    @if ($errors->any())
      <div class="peringatan gagal" role="alert">
        <ul class="daftar-galat">
          @foreach ($errors->all() as $galat)
            <li>{{ $galat }}</li>
          @endforeach
        </ul>
      </div>
    @endif

    <form method="POST" action="{{ route('login.attempt') }}" novalidate>
      @csrf

      <div class="field">
        <label for="username">Username</label>
        <input
          id="username"
          name="username"
          type="text"
          value="{{ old('username') }}"
          required
          autofocus
          autocomplete="username"
          autocapitalize="none"
          spellcheck="false"
          @error('username') aria-invalid="true" aria-describedby="galat-username" @enderror
        >
        @error('username')
          <span class="hint galat" id="galat-username">{{ $message }}</span>
        @enderror
      </div>

      <div class="field">
        <label for="password">Password</label>
        <input
          id="password"
          name="password"
          type="password"
          required
          autocomplete="current-password"
          @error('password') aria-invalid="true" aria-describedby="galat-password" @enderror
        >
        @error('password')
          <span class="hint galat" id="galat-password">{{ $message }}</span>
        @enderror
      </div>

      <button type="submit" class="tombol utama tombol-lebar">Masuk</button>
    </form>

    <p class="masuk-kaki">Daily Bun · Manajemen Toko Roti</p>
  </main>

</body>
</html>
