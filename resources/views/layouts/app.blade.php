<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <meta name="theme-color" content="#140E0A">
  <meta name="color-scheme" content="dark">
  <title>@yield('judul') - {{ config('app.name', 'Daily Bun') }}</title>
  <link rel="icon" type="image/png" href="{{ asset('img/favicon.png') }}">
  <link rel="stylesheet" href="{{ asset('css/style.css') }}">
  <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
@php
  // Menu dibaca dari config/menu.php (satu sumber untuk sidebar).
  // Item tanpa hak akses dibuang; grup yang kosong ikut disembunyikan.
  $menu = collect(config('menu', []))
      ->map(function (array $entri) {
          if (isset($entri['items'])) {
              $entri['items'] = array_values(array_filter(
                  $entri['items'],
                  fn (array $item) => \App\Support\Akses::boleh($item['modul'])
              ));
          }

          return $entri;
      })
      ->reject(fn (array $entri) => isset($entri['items']) && $entri['items'] === []);

  $tautan = fn (array $m) => route($m['route'], $m['params'] ?? []);
  $menuAktif = fn (array $m) => request()->is(...($m['match'] ?? []));
  $saya = auth()->user();
@endphp
<a class="lewati" href="#konten">Lewati ke konten</a>
<div class="aplikasi">

  <aside class="sisi" id="sisi" aria-label="Navigasi utama">
    <div class="sisi-merek">
      <a href="{{ route('dashboard') }}">
        <span class="merek-ikon" aria-hidden="true">
          <img src="{{ asset('img/logo-icon.png') }}" alt="" width="42" height="42" decoding="async">
        </span>
        <span class="merek-teks">
          <strong>{{ config('app.name', 'Daily Bun') }}</strong>
          <small>Manajemen Toko</small>
        </span>
      </a>
    </div>

    <nav>
      @foreach ($menu as $entri)
        @if (isset($entri['items']))
          <div class="grup">
            <p class="grup-judul">{{ $entri['label'] }}</p>
            <ul>
              @foreach ($entri['items'] as $item)
                <li>
                  <a href="{{ $tautan($item) }}" @class(['aktif' => $menuAktif($item)]) @if ($menuAktif($item)) aria-current="page" @endif>
                    <x-ikon :nama="$item['ikon'] ?? 'kotak'" :ukuran="18" />
                    <span>{{ $item['label'] }}</span>
                  </a>
                </li>
              @endforeach
            </ul>
          </div>
        @else
          <a @class(['sisi-tunggal', 'aktif' => $menuAktif($entri)]) href="{{ $tautan($entri) }}" @if ($menuAktif($entri)) aria-current="page" @endif>
            <x-ikon :nama="$entri['ikon'] ?? 'kotak'" :ukuran="18" />
            <span>{{ $entri['label'] }}</span>
          </a>
        @endif
      @endforeach
    </nav>

    <div class="sisi-kaki">
      <span class="sisi-kaki-tanda" aria-hidden="true"></span>
      <span>Daily Bun</span>
    </div>
  </aside>
  <div class="sisi-latar" data-tutup-sisi hidden></div>

  <div class="badan">
    <header class="bar-atas">
      <button type="button" class="tombol-menu" data-buka-sisi aria-controls="sisi" aria-expanded="false" aria-label="Buka menu">
        <x-ikon nama="menu" :ukuran="20" />
      </button>

      <a class="bar-merek" href="{{ route('dashboard') }}">{{ config('app.name', 'Daily Bun') }}</a>

      <nav class="remah" aria-label="Breadcrumb">
        <ol>
          <li><a href="{{ route('dashboard') }}">Beranda</a></li>
          @hasSection('breadcrumb')
            @yield('breadcrumb')
          @else
            @hasSection('grup')<li>@yield('grup')</li>@endif
            <li aria-current="page">@yield('judul')</li>
          @endif
        </ol>
      </nav>

      <div class="bar-kanan">
        @if ($saya)
          <div class="pengguna" title="Masuk sebagai {{ $saya->username }}">
            <span class="pengguna-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($saya->username, 0, 1)) }}</span>
            <span class="pengguna-teks">
              <strong>{{ $saya->username }}</strong>
              <small>{{ $saya->role?->nama_role ?? 'Tanpa role' }}</small>
            </span>
          </div>
          <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="tombol sekunder kecil keluar">
              <x-ikon nama="keluar" :ukuran="15" />
              <span class="keluar-teks">Keluar</span>
            </button>
          </form>
        @endif
      </div>
    </header>

    <main id="konten">
      <div class="kepala-halaman">
        <div class="kepala-teks">
          <h1>@yield('judul')</h1>
          @hasSection('deskripsi')<p class="deskripsi">@yield('deskripsi')</p>@endif
        </div>
        <div class="kepala-aksi">@yield('aksi')</div>
      </div>

      @if (session('sukses'))<div class="peringatan sukses" role="status">{{ session('sukses') }}</div>@endif
      @if (session('gagal'))<div class="peringatan gagal" role="alert">{{ session('gagal') }}</div>@endif

      @yield('isi')
    </main>
  </div>
</div>

@include('partials.modal-konfirmasi')
@stack('modal')
@stack('skrip')
</body>
</html>
