@props(['status'])
@php
/** @var \App\Enums\StatusAset|\App\Enums\StatusPeriode $status */
@endphp
<x-ui.badge :tone="$status->color()" dot>{{ $status->label() }}</x-ui.badge>
