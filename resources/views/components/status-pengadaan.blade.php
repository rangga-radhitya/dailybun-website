@props(['status' => 'D'])
@if ($status === 'S')
  <x-badge jenis="sukses" teks="Selesai" />
@else
  <x-badge jenis="proses" teks="Belum selesai" />
@endif
