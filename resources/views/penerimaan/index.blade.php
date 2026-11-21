@extends('layouts.app')

@section('judul', 'Data Penerimaan')
@section('grup', 'Transaksi')

@section('aksi')
  <a class="tombol" href="{{ route('penerimaan.create') }}">
    <x-ikon nama="tambah" :ukuran="16" />
    Tambah Penerimaan
  </a>
@endsection

@section('isi')
  <x-filter-bar :reset="route('penerimaan.index')">
    <div class="grup-filter">
      <label for="q">Cari nama / ID</label>
      <input type="search" id="q" name="q" value="{{ $q }}" placeholder="Nama vendor, ID penerimaan/pengadaan">
    </div>
  </x-filter-bar>

  <div data-hasil-daftar>
  <div class="tabel-bungkus">
  <table>
    <thead>
      <tr><th class="kode">ID</th><th>Tanggal</th><th>Pengadaan</th><th>Vendor</th><th>Diterima oleh</th><th>Status</th><th class="angka">Jumlah item</th><th class="aksi">Aksi</th></tr>
    </thead>
    <tbody>
    @forelse ($daftar as $r)
      <tr>
        <td class="kode">{{ $r->idpenerimaan }}</td>
        <td>{{ $r->tanggal ?? '-' }}</td>
        <td>
          @if ($r->idpengadaan)
            @akses('pengadaan')
              <a href="{{ route('pengadaan.show', $r->idpengadaan) }}">ID {{ $r->idpengadaan }}</a>
            @else
              ID {{ $r->idpengadaan }}
            @endakses
          @else
            -
          @endif
        </td>
        <td>{{ $r->nama_vendor ?? '-' }}</td>
        <td>{{ $r->username ?? '-' }}</td>
        <td><x-status-penerimaan :status="$r->status" /></td>
        <td class="angka">{{ $r->detail_count }}</td>
        <td class="aksi">
          <a class="tombol sekunder kecil" href="{{ route('penerimaan.show', $r->idpenerimaan) }}">
            <x-ikon nama="info" :ukuran="14" />
            Detail
          </a>
          @if ($r->status !== 'C')
            <form method="POST" action="{{ route('penerimaan.destroy', $r->idpenerimaan) }}">
              @csrf
              @method('DELETE')
              <button type="button" class="tombol sekunder kecil"
                      data-konfirmasi="Hapus penerimaan ID {{ $r->idpenerimaan }}? Stok yang sudah masuk akan dikoreksi otomatis.">
                <x-ikon nama="hapus" :ukuran="14" />
                Hapus
              </button>
            </form>
          @endif
        </td>
      </tr>
    @empty
      <tr><td colspan="8" class="kosong">{{ $q !== '' ? 'Tidak ada penerimaan yang cocok dengan pencarian.' : 'Belum ada data penerimaan.' }}</td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
  {{ $daftar->links('components.pagination') }}
  </div>
@endsection
