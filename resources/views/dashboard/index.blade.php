@extends('layouts.app')

@section('judul', 'Dashboard')

@section('isi')
  @php
    // Dashboard dipakai semua role; isi yang tidak boleh diakses disembunyikan.
    $bolehPenjualan = \App\Support\Akses::boleh('penjualan');
    $bolehMargin = \App\Support\Akses::boleh('margin');
    $kpiModul = ['pengadaan', 'penerimaan', 'retur', 'kartu_stok', 'barang', 'vendor', 'user'];
    $adaKpiModul = false;
    foreach ($kpiModul as $modul) {
        if (\App\Support\Akses::boleh($modul)) {
            $adaKpiModul = true;
            break;
        }
    }
  @endphp

  @if ($bolehPenjualan)
    <div class="hero-grid{{ $bolehMargin ? '' : ' hero-tunggal' }}">
      <section class="hero-penjualan">
        <div class="hero-label">
          <x-ikon nama="penjualan" :ukuran="15" />
          Penjualan hari ini
        </div>
        <div class="hero-nilai" data-hitung="{{ $ringkas['nilai_hari_ini'] }}" data-format="rp"><x-rp :nilai="$ringkas['nilai_hari_ini']" /></div>
        <div class="hero-meta">
          <span>{{ $ringkas['penjualan_hari_ini'] }} transaksi</span>
          <span aria-hidden="true">·</span>
          <span>{{ \Carbon\Carbon::parse($hari)->format('d/m/Y') }}</span>
          <span aria-hidden="true">·</span>
          <span title="Zona waktu aplikasi menggunakan UTC">UTC</span>
        </div>
        <a class="hero-tautan" href="{{ route('penjualan.index') }}">
          Lihat penjualan
          <x-ikon nama="panah" :ukuran="14" />
        </a>
      </section>

      @if ($bolehMargin)
        <div class="hero-sisi">
          <section class="kartu kartu-sempit">
            <div class="hero-label">
              <x-ikon nama="margin" :ukuran="15" />
              Margin aktif
            </div>
            <div class="nilai-kecil">
              @if (! empty($marginAktif))
                @foreach ($marginAktif as $m)
                  <x-persen :nilai="$m->persen" />@if (! $loop->last), @endif
                @endforeach
              @else
                -
              @endif
            </div>
            <div class="ket">
              Berlaku untuk transaksi penjualan.
              <a href="{{ route('margin_penjualan') }}">Kelola margin</a>
            </div>
          </section>
        </div>
      @endif
    </div>
  @endif

  @if ($adaKpiModul)
    <section class="kartu kpi-strip" aria-label="Ringkasan operasional">
    @akses('pengadaan')
      <a class="kpi" href="{{ route('pengadaan.index') }}">
        <span class="kpi-label">Pengadaan</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['pengadaan'] }}">{{ number_format((int) $ringkas['pengadaan'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data pengadaan <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('penerimaan')
      <a class="kpi" href="{{ route('penerimaan.index') }}">
        <span class="kpi-label">Penerimaan</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['penerimaan'] }}">{{ number_format((int) $ringkas['penerimaan'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data penerimaan <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('retur')
      <a class="kpi" href="{{ route('retur.index') }}">
        <span class="kpi-label">Retur</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['retur'] }}">{{ number_format((int) $ringkas['retur'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data retur <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('kartu_stok')
      <a class="kpi" href="{{ route('kartu_stok') }}">
        <span class="kpi-label">Kartu stok</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['kartu_stok'] }}">{{ number_format((int) $ringkas['kartu_stok'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Riwayat stok <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('barang')
      <a class="kpi" href="{{ route('barang') }}">
        <span class="kpi-label">Barang</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['barang_aktif'] }}">{{ number_format((int) $ringkas['barang_aktif'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data barang <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('vendor')
      <a class="kpi" href="{{ route('vendor') }}">
        <span class="kpi-label">Vendor</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['vendor_aktif'] }}">{{ number_format((int) $ringkas['vendor_aktif'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data vendor <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    @akses('user')
      <a class="kpi" href="{{ route('user') }}">
        <span class="kpi-label">User</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['user'] }}">{{ number_format((int) $ringkas['user'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Data user <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    @endakses
    </section>
  @endif

  @akses('penjualan')
    <section class="kartu kpi-strip kpi-tiga" aria-label="Ringkasan penjualan">
      <a class="kpi" href="{{ route('penjualan.index') }}">
        <span class="kpi-label">Transaksi</span>
        <span class="kpi-nilai" data-hitung="{{ $ringkas['penjualan'] }}">{{ number_format($ringkas['penjualan'], 0, ',', '.') }}</span>
        <span class="kpi-tautan">Semua waktu <x-ikon nama="panah" :ukuran="13" /></span>
      </a>
    </section>
  @endakses

  <div class="dua-kolom">
    @akses('penjualan')
      <section class="kartu">
        <div class="panel-kepala">
          <h2>Penjualan terakhir</h2>
          <a class="panel-meta" href="{{ route('penjualan.index') }}">Lihat semua</a>
        </div>
        <div class="tabel-bungkus">
          <table>
            <thead><tr><th class="kode">ID</th><th>Tanggal</th><th>Kasir</th><th class="angka">Total</th></tr></thead>
            <tbody>
            @forelse ($penjualanTerakhir as $p)
              <tr>
                <td class="kode"><a href="{{ route('penjualan.show', $p->idpenjualan) }}">#{{ $p->idpenjualan }}</a></td>
                <td>{{ $p->tanggal ?? '-' }}</td>
                <td>{{ $p->username ?? '-' }}</td>
                <td class="angka"><x-rp :nilai="$p->total_nilai" /></td>
              </tr>
            @empty
              <tr><td colspan="4" class="kosong">Belum ada penjualan.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </section>
    @endakses

    @akses('pengadaan')
      <section class="kartu">
        <div class="panel-kepala">
          <h2>Pengadaan terakhir</h2>
          <a class="panel-meta" href="{{ route('pengadaan.index') }}">Lihat semua</a>
        </div>
        <div class="tabel-bungkus">
          <table>
            <thead><tr><th class="kode">ID</th><th>Tanggal</th><th>Vendor</th><th class="angka">Total</th></tr></thead>
            <tbody>
            @forelse ($pengadaanTerakhir as $p)
              <tr>
                <td class="kode"><a href="{{ route('pengadaan.show', $p->idpengadaan) }}">#{{ $p->idpengadaan }}</a></td>
                <td>{{ $p->tanggal ?? '-' }}</td>
                <td>{{ $p->nama_vendor ?? '-' }}</td>
                <td class="angka"><x-rp :nilai="$p->total_nilai" /></td>
              </tr>
            @empty
              <tr><td colspan="4" class="kosong">Belum ada pengadaan.</td></tr>
            @endforelse
            </tbody>
          </table>
        </div>
      </section>
    @endakses
  </div>

  <x-catatan ringkas judul="Catatan data">
    @akses('kartu_stok')
      Stok berjalan per barang dapat dilihat di halaman <a href="{{ route('kartu_stok') }}">Kartu Stok</a>.
    @endakses
    Ringkasan stok rendah belum dihitung pada dashboard.
  </x-catatan>
@endsection
