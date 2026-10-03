{{--
    Bar aksi form (Batal/Simpan). Ponsel: menempel tepat di atas bottom-nav (tinggi 3.75rem + safe area, tanpa celah).
    Desktop: kartu statis, atau tetap menempel di bawah layar bila sticky-desktop.
--}}
@props(['stickyDesktop' => false])
<div {{ $attributes->class([
    'sticky bottom-[calc(3.75rem+env(safe-area-inset-bottom))] z-20 -mx-3 border-t border-zinc-200/80 bg-white px-3 py-3 shadow-[0_-4px_12px_-6px_rgba(0,0,0,0.08)] sm:-mx-6 sm:px-6 lg:mx-0 lg:rounded-2xl lg:border lg:px-5 lg:shadow-none',
    'lg:bottom-0' => $stickyDesktop,
    'lg:static' => ! $stickyDesktop,
]) }}>
    {{ $slot }}
</div>
