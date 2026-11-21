@props(['status' => 'D'])
@if ($status === 'C')
  <x-badge jenis="sukses" teks="Terkonfirmasi" />
@else
  <x-badge jenis="proses" teks="Belum terkonfirmasi" />
@endif
