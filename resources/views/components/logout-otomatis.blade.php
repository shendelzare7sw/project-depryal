{{--
    Logout otomatis setelah $menit menit tanpa aktivitas (komputer kantor dipakai bergantian).
    Waktu aktivitas terakhir dibagi antar-tab lewat localStorage, sehingga tab lain yang masih aktif tidak ikut keluar.
--}}
@props(['menit'])
<form method="POST" action="{{ route('logout') }}" class="hidden" x-data="{ batas: {{ (int) $menit }} * 60000, tandai() { try { localStorage.setItem('sikaset-aktif', Date.now()) } catch (e) {} }, terakhir() { try { return +localStorage.getItem('sikaset-aktif') || Date.now() } catch (e) { return Date.now() } } }"
    x-init="tandai(); setInterval(() => { if (Date.now() - terakhir() > batas) $el.submit() }, 30000)"
    @click.window="tandai()" @keydown.window="tandai()" @touchstart.window="tandai()" @scroll.window.throttle.10000ms="tandai()" @mousemove.window.throttle.10000ms="tandai()">
    @csrf
    <input type="hidden" name="alasan" value="tidak-aktif">
</form>
