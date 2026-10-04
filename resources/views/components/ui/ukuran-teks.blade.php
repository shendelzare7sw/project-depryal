{{-- Tombol "Perbesar teks" (112,5%) untuk pengguna senior; pilihan disimpan di perangkat (localStorage). --}}
<button type="button" x-data="{ besar: document.documentElement.classList.contains('teks-besar') }"
    @click="besar = !besar; document.documentElement.classList.toggle('teks-besar', besar); try { localStorage.setItem('sikaset-teks', besar ? 'besar' : '') } catch (e) {}"
    :aria-pressed="besar" :title="besar ? 'Kembalikan ukuran teks' : 'Perbesar teks'" aria-label="Perbesar teks"
    {{ $attributes->class('flex h-11 w-11 shrink-0 items-center justify-center rounded-xl border border-zinc-200 bg-white font-extrabold text-zinc-600 transition hover:bg-zinc-50') }}
    :class="besar && '!border-brand-600 !bg-brand-50 !text-brand-700'">
    <span class="text-xs leading-none">A</span><span class="text-base leading-none">A</span>
</button>
