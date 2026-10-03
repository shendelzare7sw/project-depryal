@extends('errors.layout')
@section('kode', '500')
@section('judul', 'Terjadi kesalahan sistem')
@section('pesan', 'Maaf, terjadi kesalahan tak terduga. Silakan coba lagi; bila berulang, hubungi administrator.')
@section('ikon')<x-heroicon-o-exclamation-triangle class="h-8 w-8" />@endsection
