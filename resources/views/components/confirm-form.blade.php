{{--
    Form + konfirmasi SweetAlert2. Tombol konfirmasi merah untuk DELETE/danger, petrol untuk lainnya.
    when=false → submit langsung tanpa dialog (konfirmasi kondisional).
--}}
@props(['title' => 'Yakin?', 'text' => '', 'confirm' => 'Ya, lanjutkan', 'icon' => 'warning', 'method' => 'POST', 'danger' => null, 'when' => true])
@php($isDanger = $danger ?? strtoupper($method) === 'DELETE')
@if ($when)
<form {{ $attributes->merge(['method' => 'POST']) }} x-data="{ busy: false }" :class="busy && 'pointer-events-none opacity-60'"
      @submit.prevent="Swal.fire({
          title: @js($title),
          text: @js($text),
          icon: @js($icon),
          showCancelButton: true,
          confirmButtonText: @js($confirm),
          cancelButtonText: 'Batal',
          reverseButtons: true,
          confirmButtonColor: @js($isDanger ? '#e11d48' : '#1f5f59'),
          cancelButtonColor: '#71717a',
      }).then(r => { if (r.isConfirmed) { busy = true; $el.submit() } })">
@else
<form {{ $attributes->merge(['method' => 'POST']) }} x-data="{ busy: false }" @submit="busy = true">
@endif
    @csrf
    @if (strtoupper($method) !== 'POST') @method($method) @endif
    {{ $slot }}
</form>
