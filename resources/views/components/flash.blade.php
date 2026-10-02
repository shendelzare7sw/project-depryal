@php($msg = session('success') ?? session('error'))
@if ($msg)
<div x-data x-init="Swal.fire({
    toast: true,
    position: 'top-end',
    timer: 3500,
    timerProgressBar: true,
    showConfirmButton: false,
    icon: @js(session('success') ? 'success' : 'error'),
    title: @js($msg)
})"></div>
@endif
