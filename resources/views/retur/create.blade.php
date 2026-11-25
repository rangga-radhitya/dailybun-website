@extends('layouts.app')

@section('judul', 'Tambah Retur')
@section('grup', 'Transaksi')

@section('breadcrumb')
  <li>Transaksi</li>
  <li><a href="{{ route('retur.index') }}">Retur</a></li>
  <li aria-current="page">Tambah</li>
@endsection

@php
  $adaSisa = collect($rincian)->contains(fn ($r) => (int) $r->sisa > 0);

  $lama = [];
  foreach ((array) old('items', []) as $baris) {
      if (isset($baris['iddetail_penerimaan'])) {
          $lama[(string) $baris['iddetail_penerimaan']] = $baris;
      }
  }
@endphp

@section('aksi')
  <a class="tombol sekunder" href="{{ route('retur.index') }}">
    <x-ikon nama="kiri" :ukuran="15" />
    Kembali
  </a>
  @if ($penerimaan !== null && $adaSisa)
    <button type="submit" form="form-retur" class="tombol utama">
      <x-ikon nama="centang" :ukuran="15" />
      Simpan Retur
    </button>
  @endif
@endsection

@section('isi')
@include('partials.galat')

@if ($penerimaan === null)
  <x-catatan jenis="info">Pilih penerimaan terlebih dahulu. Yang ditampilkan hanya penerimaan yang masih memiliki sisa barang bisa diretur.</x-catatan>
@endif

<section class="kartu">
  <h2>Pilih penerimaan</h2>
  <div class="pilih-baris">
    <div class="grup-filter">
      <label for="pilih-penerimaan">Penerimaan</label>
      <select id="pilih-penerimaan">
        <option value="">Pilih penerimaan</option>
        @foreach ($daftarPenerimaan as $p)
          <option value="{{ $p->idpenerimaan }}" @selected((int) $idpenerimaan === (int) $p->idpenerimaan)>
            ID {{ $p->idpenerimaan }}, {{ $p->nama_vendor }} ({{ $p->tanggal }}, sisa {{ $p->sisa }})
          </option>
        @endforeach
      </select>
    </div>
    @if ($penerimaan !== null)
      <div class="grup-filter">
        <label>Vendor</label>
        <input type="text" value="{{ $penerimaan->nama_vendor ?? '-' }}" disabled>
      </div>
      <div class="grup-filter">
        <label>Tanggal penerimaan</label>
        <input type="text" value="{{ $penerimaan->tanggal ?? '-' }}" disabled>
      </div>
    @endif
  </div>
  @if ($daftarPenerimaan === [])
    <x-catatan jenis="info">Belum ada penerimaan yang bisa diretur. Semua barang sudah diretur atau belum ada penerimaan.</x-catatan>
  @endif
</section>

@if ($penerimaan !== null)
  <form id="form-retur" class="form-transaksi" method="POST" action="{{ route('retur.store') }}">
    @csrf
    <input type="hidden" name="idpenerimaan" value="{{ $penerimaan->idpenerimaan }}">

    <section class="kartu">
      <h2>Informasi retur</h2>
      <div class="pilih-baris">
        <div class="grup-filter">
          <label>Penerimaan asal</label>
          <input type="text" value="ID {{ $penerimaan->idpenerimaan }} ({{ $penerimaan->tanggal }})" disabled>
        </div>
        <div class="grup-filter">
          <label>Dibuat oleh</label>
          <input type="text" value="{{ auth()->user()?->username ?? '-' }}" disabled>
        </div>
      </div>
    </section>

    <section class="kartu">
      <h2>Barang yang diretur</h2>
      <div class="tabel-bungkus">
        <table>
          <thead>
            <tr>
              <th class="kolom-aksi">No</th>
              <th>Barang</th>
              <th class="kolom-sempit angka">Jumlah diterima</th>
              <th class="kolom-sempit angka">Sudah diretur</th>
              <th class="kolom-sempit angka">Sisa</th>
              <th class="kolom-sempit angka">Jumlah retur</th>
              <th>Alasan</th>
            </tr>
          </thead>
          <tbody>
          @foreach ($rincian as $i => $r)
            @php
              $idr = (string) $r->iddetail_penerimaan;
              $sisa = (int) $r->sisa;
              $kosong = $sisa <= 0;
              $jumlah = isset($lama[$idr]) ? (string) $lama[$idr]['jumlah'] : '0';
              $alasan = isset($lama[$idr]) ? (string) ($lama[$idr]['alasan'] ?? '') : '';
            @endphp
            <tr @class(['baris-kosong' => $kosong])>
              <td class="kolom-aksi">{{ $i + 1 }}</td>
              <td>
                {{ $r->nama ?? 'Barang #'.$r->idbarang }}
                <small class="hint">{{ $r->nama_satuan ?? '-' }}</small>
                <input type="hidden" name="items[{{ $i }}][iddetail_penerimaan]" value="{{ $idr }}">
              </td>
              <td class="angka">{{ $r->jumlah_terima }}</td>
              <td class="angka">{{ $r->sudah_retur }}</td>
              <td class="angka">{{ $sisa }}</td>
              <td>
                <input type="number" name="items[{{ $i }}][jumlah]" value="{{ $kosong ? 0 : $jumlah }}"
                       min="0" max="{{ $sisa }}" step="1" @if ($kosong) readonly @endif>
              </td>
              <td>
                <input type="text" name="items[{{ $i }}][alasan]" value="{{ $alasan }}" maxlength="200"
                       placeholder="Contoh: barang rusak" @if ($kosong) readonly @endif>
              </td>
            </tr>
          @endforeach
          </tbody>
        </table>
      </div>
      <p class="hint">Isi jumlah 0 untuk barang yang tidak diretur. Jumlah retur tidak boleh melebihi sisa. Stok berkurang otomatis saat retur disimpan.</p>
    </section>
  </form>
@endif
@endsection

@push('skrip')
<script>
(function () {
  'use strict';

  var pilih = document.getElementById('pilih-penerimaan');
  if (pilih) {
    pilih.addEventListener('change', function () {
      var base = @json(route('retur.create'));
      window.location = pilih.value === '' ? base : base + '?idpenerimaan=' + encodeURIComponent(pilih.value);
    });
  }
})();
</script>
@endpush
