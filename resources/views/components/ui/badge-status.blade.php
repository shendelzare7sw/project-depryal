@props(['status'])
@php
/** @var \App\Enums\StatusAset|\App\Enums\StatusPeriode $status */
@endphp
<span class="badge badge-{{ $status->color() }} badge-sm font-medium">
    {{ $status->label() }}
</span>
