@props(['tindakan'])
@php
/** @var \App\Enums\TindakanAset $tindakan */
@endphp
<x-ui.badge :tone="$tindakan->color()" {{ $attributes }}>{{ $tindakan->label() }}</x-ui.badge>
