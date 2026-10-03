@extends('errors.layout')
@section('kode', '403')
@section('judul', 'Akses ditolak')
@section('pesan', 'Halaman atau aksi ini tidak tersedia untuk peran Anda. Hubungi administrator bila Anda memerlukan akses.')
@section('ikon')<x-heroicon-o-lock-closed class="h-8 w-8" />@endsection
