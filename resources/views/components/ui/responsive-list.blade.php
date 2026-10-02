{{--
  Responsive list: tampilkan tabel di ≥md, kartu di <md.
  Slot 'table' untuk konten tabel, slot 'cards' untuk konten kartu mobile.
--}}
<div class="hidden md:block overflow-x-auto">
    {{ $table }}
</div>
<div class="md:hidden space-y-2">
    {{ $cards }}
</div>
