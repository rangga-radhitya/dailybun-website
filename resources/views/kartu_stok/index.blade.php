@extends('layouts.app')

@section('judul', 'Kartu Stok')
@section('grup', 'Transaksi')

@section('isi')

  <x-filter-bar :reset="route('kartu_stok')">
    <div class="grup-filter">
      <label for="q">Cari nama / ID</label>
      <input type="search" id="q" name="q" value="{{ $q }}" placeholder="Nama barang atau ID transaksi">
    </div>
  </x-filter-bar>

  <div data-hasil-daftar>
  <div class="tabel-bungkus">
  <table>
    <thead>
      <tr><th>Tanggal</th><th>Barang</th><th>Jenis transaksi</th><th class="angka">Masuk</th><th class="angka">Keluar</th><th class="angka">Saldo (stock)</th><th class="kode">ID transaksi</th></tr>
    </thead>
    <tbody>
    @forelse ($daftar as $r)
      <tr>
        <td>{{ $r->tanggal ?? '-' }}</td>
        <td>{{ $r->nama_barang ?? ($r->idbarang ? 'Barang #'.$r->idbarang : '-') }}</td>
        <td>
          <x-badge :jenis="(int) ($r->masuk ?? 0) > 0 ? 'sukses' : 'bahaya'"
                   :teks="(int) ($r->masuk ?? 0) > 0 ? 'Masuk' : 'Keluar'"
                   :title="(int) ($r->masuk ?? 0) > 0 ? 'Stok masuk' : 'Stok keluar'" />
        </td>
        <td class="angka">{{ $r->masuk ?? 0 }}</td>
        <td class="angka">{{ $r->keluar ?? 0 }}</td>
        <td class="angka">{{ $r->stock ?? '-' }}</td>
        <td class="kode">{{ $r->idtransaksi ?? '-' }}</td>
      </tr>
    @empty
      <tr><td colspan="7" class="kosong">{{ $q !== '' ? 'Tidak ada riwayat stok yang cocok dengan pencarian.' : 'Belum ada riwayat stok (tabel kartu_stok masih kosong).' }}</td></tr>
    @endforelse
    </tbody>
  </table>
  </div>
  {{ $daftar->links('components.pagination') }}
  </div>
@endsection
