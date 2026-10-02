@props(['tindakan'])
@php
/** @var \App\Enums\TindakanAset $tindakan */
@endphp
<span class="badge badge-{{ $tindakan->color() }} badge-sm font-medium">
    {{ $tindakan->label() }}
</span>
