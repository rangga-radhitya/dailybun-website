@extends('layouts.app')

@section('judul', 'Tambah Penjualan')
@section('grup', 'Transaksi')

@section('breadcrumb')
  <li>Transaksi</li>
  <li><a href="{{ route('penjualan.index') }}">Data Penjualan</a></li>
  <li aria-current="page">Tambah</li>
@endsection

@section('aksi')
  <a class="tombol sekunder" href="{{ route('penjualan.index') }}">
    <x-ikon nama="kiri" :ukuran="15" />
    Kembali
  </a>
  <button type="submit" form="form-penjualan" class="tombol utama">
    <x-ikon nama="centang" :ukuran="15" />
    Simpan Penjualan
  </button>
@endsection

@section('isi')
@include('partials.galat')

@php
  $barisLama = old('items', []);
  $barisLama = is_array($barisLama) && $barisLama !== [] ? array_values($barisLama) : [['idbarang' => '', 'jumlah' => 1]];

  // Harga dan stok selalu diambil dari master barang, bukan dari old input.
  $hargaMaster = [];
  $stokMaster = [];
  foreach ($daftarBarang as $br) {
      $hargaMaster[(string) $br->idbarang] = (string) $br->harga;
      $stokMaster[(string) $br->idbarang] = (int) $br->stok;
  }
@endphp

<form id="form-penjualan" class="form-transaksi" method="POST" action="{{ route('penjualan.store') }}">
  @csrf

  <section class="kartu">
    <h2>Informasi penjualan</h2>
    <div class="pilih-baris">
      <div class="grup-filter">
        <label>Kasir</label>
        <input type="text" value="manajer01" disabled>
      </div>
      <div class="grup-filter">
        <label>Margin aktif</label>
        <input type="text" value="{{ $margin !== null ? rtrim(rtrim(number_format((float) $margin->persen, 2, ',', '.'), '0'), ',').'% (ID '.$margin->idmargin_penjualan.')' : 'Tidak ada margin aktif' }}" disabled>
      </div>
      <div class="grup-filter">
        <label>Tanggal</label>
        <input type="text" value="{{ now()->format('d/m/Y H:i') }}" disabled>
      </div>
    </div>
  </section>

  <section class="kartu">
    <h2>Barang yang dijual</h2>
    <p class="catatan-kecil">Pilih barang untuk melihat stok tersedia.</p>
    <div class="tabel-bungkus">
      <table id="tabel-barang">
        <thead>
          <tr>
            <th class="kolom-aksi">No</th>
            <th>Barang</th>
            <th class="kolom-sempit angka">Stok tersedia</th>
            <th class="kolom-sempit angka">Harga satuan</th>
            <th class="kolom-sempit angka">Jumlah</th>
            <th class="kolom-sempit angka">Sub total</th>
            <th class="kolom-aksi">Hapus</th>
          </tr>
        </thead>
        <tbody id="isi-barang">
        @foreach ($barisLama as $i => $b)
          @php
            $idb = (string) ($b['idbarang'] ?? '');
            $harga = $hargaMaster[$idb] ?? '';
            $stok = $stokMaster[$idb] ?? 0;
            $jumlah = (string) ($b['jumlah'] ?? '1');
          @endphp
          <tr>
            <td class="kolom-aksi">{{ $i + 1 }}</td>
            <td>
              <select name="items[{{ $i }}][idbarang]" required>
                <option value="">Pilih barang</option>
                @foreach ($daftarBarang as $br)
                  <option value="{{ $br->idbarang }}" data-harga="{{ $br->harga }}" data-stok="{{ (int) $br->stok }}" @selected($idb === (string) $br->idbarang) @disabled((int) $br->stok <= 0)>{{ $br->nama }}{{ (int) $br->stok > 0 ? ' (stok '.$br->stok.')' : ' (stok habis)' }}</option>
                @endforeach
              </select>
            </td>
            <td class="angka"><output data-stok>{{ $stok }}</output></td>
            <td><input type="number" name="items[{{ $i }}][harga_satuan]" value="{{ $harga }}" min="0" step="1" readonly tabindex="-1" aria-readonly="true" required></td>
            <td><input type="number" name="items[{{ $i }}][jumlah]" value="{{ $jumlah }}" min="1" step="1" required></td>
            <td class="angka"><output data-subtotal>0</output></td>
            <td class="kolom-aksi">
              <button type="button" class="tombol sekunder kecil" data-hapus-baris>
                <x-ikon nama="hapus" :ukuran="14" />
                Hapus
              </button>
            </td>
          </tr>
        @endforeach
        </tbody>
      </table>
    </div>

    <p><button type="button" class="tombol sekunder kecil" id="tambah-baris">
      <x-ikon nama="tambah" :ukuran="14" />
      Tambah barang
    </button></p>

    <dl class="ringkas">
      <div><dt>Subtotal barang</dt><dd><output id="ringkas-subtotal-barang">0</output></dd></div>
      <div><dt>Margin ({{ $margin !== null ? rtrim(rtrim(number_format((float) $margin->persen, 2, ',', '.'), '0'), ',') : '0' }}%)</dt><dd><output id="ringkas-margin">0</output></dd></div>
      <div><dt>Subtotal</dt><dd><output id="ringkas-subtotal">0</output></dd></div>
      <div><dt>PPN ({{ \App\Http\Controllers\Controller::PPN_PERSEN }}%)</dt><dd><output id="ringkas-ppn">0</output></dd></div>
      <div><dt>Total</dt><dd><output id="ringkas-total">0</output></dd></div>
    </dl>
  </section>
</form>

<template id="pola-baris">
  <tr>
    <td class="kolom-aksi">__NO__</td>
    <td>
      <select name="items[__INDEX__][idbarang]" required>
        <option value="">Pilih barang</option>
        @foreach ($daftarBarang as $br)
          <option value="{{ $br->idbarang }}" data-harga="{{ $br->harga }}" data-stok="{{ (int) $br->stok }}" @disabled((int) $br->stok <= 0)>{{ $br->nama }}{{ (int) $br->stok > 0 ? ' (stok '.$br->stok.')' : ' (stok habis)' }}</option>
        @endforeach
      </select>
    </td>
    <td class="angka"><output data-stok>0</output></td>
    <td><input type="number" name="items[__INDEX__][harga_satuan]" value="" min="0" step="1" readonly tabindex="-1" aria-readonly="true" required></td>
    <td><input type="number" name="items[__INDEX__][jumlah]" value="1" min="1" step="1" required></td>
    <td class="angka"><output data-subtotal>0</output></td>
    <td class="kolom-aksi">
      <button type="button" class="tombol sekunder kecil" data-hapus-baris>
        <x-ikon nama="hapus" :ukuran="14" />
        Hapus
      </button>
    </td>
  </tr>
</template>
@endsection

@push('skrip')
<script>
(function () {
  'use strict';

  var PPN = {{ \App\Http\Controllers\Controller::PPN_PERSEN }};
  var MARGIN = {{ $margin !== null ? (float) $margin->persen : 0 }};
  var body = document.getElementById('isi-barang');
  var pola = document.getElementById('pola-baris');
  var tombolTambah = document.getElementById('tambah-baris');
  if (!body || !pola) return;

  function rupiah(n) {
    return Number(n || 0).toLocaleString('id-ID');
  }

  function nomori() {
    var baris = body.querySelectorAll('tr');
    baris.forEach(function (tr, i) {
      tr.querySelector('td').textContent = i + 1;
      tr.querySelectorAll('[name]').forEach(function (el) {
        el.name = el.name.replace(/items\[\d+\]/, 'items[' + i + ']');
      });
    });
  }

  function stokOpsi(select) {
    var opt = select.options[select.selectedIndex];
    if (!opt || opt.value === '') return 0;
    return parseInt(opt.getAttribute('data-stok') || '0', 10) || 0;
  }

  function segarkanBaris(tr) {
    var select = tr.querySelector('[name$="[idbarang]"]');
    if (!select) return;

    var stok = stokOpsi(select);
    var outStok = tr.querySelector('output[data-stok]');
    if (outStok) outStok.textContent = stok;

    var jumlah = tr.querySelector('[name$="[jumlah]"]');
    if (!jumlah) return;

    if (stok > 0) {
      jumlah.max = stok;
      if ((parseInt(jumlah.value, 10) || 0) > stok) jumlah.value = stok;
    } else {
      jumlah.removeAttribute('max');
    }
  }

  function hitung() {
    var subtotalBarang = 0;
    body.querySelectorAll('tr').forEach(function (tr) {
      var harga = parseFloat(tr.querySelector('[name$="[harga_satuan]"]').value) || 0;
      var jumlah = parseFloat(tr.querySelector('[name$="[jumlah]"]').value) || 0;
      var sub = harga * jumlah;
      subtotalBarang += sub;
      var out = tr.querySelector('[data-subtotal]');
      if (out) out.textContent = rupiah(sub);
    });
    var marginNilai = Math.round(subtotalBarang * MARGIN / 100);
    var subtotal = subtotalBarang + marginNilai;
    var ppn = Math.round(subtotal * PPN / 100);
    var rBarang = document.getElementById('ringkas-subtotal-barang');
    var rMargin = document.getElementById('ringkas-margin');
    var r1 = document.getElementById('ringkas-subtotal');
    var r2 = document.getElementById('ringkas-ppn');
    var r3 = document.getElementById('ringkas-total');
    if (rBarang) rBarang.textContent = rupiah(subtotalBarang);
    if (rMargin) rMargin.textContent = rupiah(marginNilai);
    if (r1) r1.textContent = rupiah(subtotal);
    if (r2) r2.textContent = rupiah(ppn);
    if (r3) r3.textContent = rupiah(subtotal + ppn);
  }

  function tanganiPerubahan(e) {
    var el = e.target;
    if (!(el instanceof Element)) return;
    if (el.matches('[name$="[idbarang]"]')) {
      var opt = el.options[el.selectedIndex];
      var harga = opt ? opt.getAttribute('data-harga') : null;
      var inputHarga = el.closest('tr').querySelector('[name$="[harga_satuan]"]');
      if (inputHarga) {
        inputHarga.value = harga !== null && harga !== '' ? harga : '';
      }
      segarkanBaris(el.closest('tr'));
    }
    hitung();
  }

  body.addEventListener('input', tanganiPerubahan);
  body.addEventListener('change', tanganiPerubahan);

  body.addEventListener('click', function (e) {
    var t = e.target;
    if (!(t instanceof Element)) return;
    if (!t.closest('[data-hapus-baris]')) return;
    var baris = body.querySelectorAll('tr');
    if (baris.length <= 1) {
      return;
    }
    t.closest('tr').remove();
    nomori();
    hitung();
  });

  if (tombolTambah) {
    tombolTambah.addEventListener('click', function () {
      var index = body.querySelectorAll('tr').length;
      var html = pola.innerHTML.replace(/__INDEX__/g, index).replace(/__NO__/g, index + 1);
      body.insertAdjacentHTML('beforeend', html);
      hitung();
    });
  }

  body.querySelectorAll('tr').forEach(segarkanBaris);
  hitung();
})();
</script>
@endpush
