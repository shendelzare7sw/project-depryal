@props(['title' => null])
<div class="card bg-base-100 shadow-sm border border-base-200">
    @if ($title)
    <div class="card-body">
        <h2 class="card-title text-base font-semibold">{{ $title }}</h2>
        {{ $slot }}
    </div>
    @else
    <div class="card-body">
        {{ $slot }}
    </div>
    @endif
</div>
