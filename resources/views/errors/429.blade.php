@extends('errors.layout')
@section('kode', '429')
@section('judul', 'Terlalu banyak permintaan')
@section('pesan', 'Anda melakukan terlalu banyak permintaan dalam waktu singkat. Tunggu sebentar lalu coba lagi.')
@section('ikon')<x-heroicon-o-hand-raised class="h-8 w-8" />@endsection
