@extends('layouts.app')

@section('judul', 'Detail Penjualan #'.$penjualan->idpenjualan)

@section('breadcrumb')
  <li>Transaksi</li>
  <li><a href="{{ route('penjualan.index') }}">Data Penjualan</a></li>
  <li aria-current="page">Penjualan #{{ $penjualan->idpenjualan }}</li>
@endsection

@section('aksi')
  <a class="tombol sekunder" href="{{ route('penjualan.index') }}">
    <x-ikon nama="kiri" :ukuran="15" />
    Kembali
  </a>

  </button>
  <form method="POST" action="{{ route('penjualan.destroy', $penjualan->idpenjualan) }}">
    @csrf
    @method('DELETE')
    <button type="button" class="tombol bahaya"
            data-konfirmasi="Hapus penjualan #{{ $penjualan->idpenjualan }}? Stok barang yang terjual akan dikembalikan otomatis.">
      <x-ikon nama="hapus" :ukuran="15" />
      Hapus
    </button>
  </form>
@endsection

@section('isi')
  <section class="kartu">
    <h2>Informasi penjualan</h2>
    <dl class="info-daftar">
      <div><dt>ID penjualan</dt><dd>{{ $penjualan->idpenjualan }}</dd></div>
      <div><dt>Tanggal</dt><dd>{{ $penjualan->tanggal ?? '-' }}</dd></div>
      <div><dt>Kasir</dt><dd>{{ $penjualan->username ?? '-' }}</dd></div>
      <div><dt>Margin tercatat</dt><dd>@if ($penjualan->persen !== null)<x-persen :nilai="$penjualan->persen" /> (ID {{ $penjualan->idmargin_penjualan }}) · <x-rp :nilai="$penjualan->margin_nilai" />@else - @endif</dd></div>
    </dl>
  </section>

  <section class="kartu">
    <h2>Barang yang dijual</h2>
    <div class="tabel-bungkus">
    <table>
      <thead>
        <tr><th>No</th><th>Barang</th><th>Satuan</th><th class="angka">Harga satuan</th><th class="angka">Jumlah</th><th class="angka">Subtotal</th></tr>
      </thead>
      <tbody>
      @forelse ($penjualan->detail as $d)
        <tr>
          <td>{{ $loop->iteration }}</td>
          <td>{{ $d->nama ?? 'Barang #'.$d->idbarang }}</td>
          <td>{{ $d->nama_satuan ?? '-' }}</td>
          <td class="angka"><x-rp :nilai="$d->harga_satuan" /></td>
          <td class="angka">{{ $d->jumlah }}</td>
          <td class="angka"><x-rp :nilai="$d->subtotal" /></td>
        </tr>
      @empty
        <tr><td colspan="6" class="kosong">Penjualan ini belum memiliki detail barang.</td></tr>
      @endforelse
      </tbody>
      <tfoot>
        @if ($totalDetail !== null)
          @php $marginNilai = (float) ($penjualan->margin_nilai ?? 0); @endphp
          <tr>
            <td colspan="5" class="angka">Subtotal barang (jumlah × harga satuan)</td>
            <td class="angka">
              <x-rp :nilai="$totalDetail" />
              @if ($penjualan->subtotal_nilai !== null)
                <x-badge :jenis="(float) $totalDetail + $marginNilai === (float) $penjualan->subtotal_nilai ? 'sukses' : 'bahaya'" :teks="(float) $totalDetail + $marginNilai === (float) $penjualan->subtotal_nilai ? 'sesuai' : 'berbeda dari subtotal tercatat'" />
              @endif
            </td>
          </tr>
          @if ($penjualan->persen !== null)
            <tr><td colspan="5" class="angka">Margin (<x-persen :nilai="$penjualan->persen" />)</td><td class="angka"><x-rp :nilai="$penjualan->margin_nilai" /></td></tr>
          @endif
        @endif
        <tr><td colspan="5" class="angka">Subtotal</td><td class="angka"><x-rp :nilai="$penjualan->subtotal_nilai" /></td></tr>
        <tr><td colspan="5" class="angka">PPN</td><td class="angka"><x-rp :nilai="$penjualan->ppn" /></td></tr>
        <tr><td colspan="5" class="angka">Total</td><td class="angka"><x-rp :nilai="$penjualan->total_nilai" /></td></tr>
      </tfoot>
    </table>
    </div>
  </section>
@endsection
