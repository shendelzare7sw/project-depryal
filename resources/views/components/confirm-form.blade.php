@props(['title' => 'Yakin?', 'text' => '', 'confirm' => 'Ya, lanjutkan', 'icon' => 'warning', 'method' => 'POST'])
<form {{ $attributes->merge(['method' => 'POST']) }} x-data="{ busy: false }"
      @submit.prevent="Swal.fire({
          title: @js($title),
          text: @js($text),
          icon: @js($icon),
          showCancelButton: true,
          confirmButtonText: @js($confirm),
          cancelButtonText: 'Batal',
          reverseButtons: true,
          confirmButtonColor: '#dc2626',
      }).then(r => { if (r.isConfirmed) { busy = true; $el.submit() } })">
    @csrf
    @if (strtoupper($method) !== 'POST') @method($method) @endif
    <span :class="busy ? 'opacity-50 pointer-events-none' : ''">{{ $slot }}</span>
</form>
