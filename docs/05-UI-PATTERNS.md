# 05 — Pola UI SIKASET (wajib untuk semua halaman)

> Diadaptasi dari pedoman CleanFlow (`docs/conversation-sipaduhok.md`) untuk stack SIKASET:
> Blade + Tailwind Play CDN + DaisyUI 4 + Alpine.js + SweetAlert2, **tanpa Vite/npm**.
> Identitas visual SIKASET sengaja berbeda dari proyek referensi (lihat §2).

## 1. Tujuan
1. Pengguna baru langsung tahu harus mulai dari mana (dashboard berorientasi tugas, bukan nama modul).
2. Konsisten, profesional, minimalis — tapi **tidak monoton**: hero berwarna, stat bertone, ikon di setiap judul kartu.
3. Mobile-first (uji 390 × 844) dan **memenuhi lebar** di desktop (uji 1440 × 900) tanpa gap samping.
4. Fungsi bisnis, validasi, hak akses, route, dan kontrak data tidak berubah karena perombakan tampilan.

## 2. Identitas visual
| Token | Nilai | Pemakaian |
|---|---|---|
| `brand-50…950` (petrol/teal tua) | dari `tailwind.config` di `assets.blade.php` | aksen utama, tombol primer, sidebar `brand-950`, hero |
| `amber` | aksen sekunder | indikator aktif sidebar, sorotan hero, peringatan |
| Netral `zinc` | teks & border | `text-zinc-900` judul, `text-zinc-500` sekunder, `border-zinc-200` |
| Tone status | emerald (baik/pertahankan), amber (perbaiki/peringatan), rose (hapus/error), sky (info), violet (aksen data) |
| Font | **Manrope** 400–800 | `font-extrabold` untuk judul & angka |
| Radius | kartu `rounded-2xl`, hero `rounded-3xl`, tombol/input `rounded-xl` |

Tema DaisyUI `data-theme="sikaset"` (variabel warna saja) didefinisikan sekali di `assets.blade.php`.

## 3. Shell & layout
- Shell pascalogin: `<x-layouts.app title="…" subtitle="…">`. **Judul & subjudul halaman tampil di topbar** (bukan diulang di konten).
- Topbar: tombol menu (mobile) · judul/subjudul · **lonceng notifikasi** · menu pengguna.
- Sidebar `brand-950`, menu dari `App\Support\Navigation` (satu sumber untuk sidebar & bottom-nav). Item yang route-nya belum ada disembunyikan otomatis.
- Pembungkus konten: `min-w-0 w-full space-y-5`. **Dilarang `mx-auto max-w-*` pada dashboard/daftar/tabel** (menimbulkan gap saat layar lebar/zoom). `max-w-*` hanya untuk dialog atau paragraf panjang.
- Tata letak dua kolom: `grid min-w-0 gap-5 xl:grid-cols-[minmax(0,1fr)_22rem]` (konten utama + rel samping).
- Tidak boleh ada horizontal scroll di dokumen; lebar besar hanya di pembungkus internal `overflow-x-auto`.

## 4. Komponen standar (`resources/views/components/ui`)
| Komponen | Pola |
|---|---|
| `<x-ui.hero>` | kartu gradien `brand` + lingkaran dekoratif, eyebrow, judul `!text-white`, slot aksi. Untuk dashboard & halaman kunci |
| `<x-ui.stat-grid :items>` | **2 kolom di ponsel**, `lg:grid-cols-4` (atau 3/6). Ikon selalu tampil (`h-9 w-9` → `sm:h-10`). Angka `text-xl sm:text-2xl`, label uppercase kecil. Tidak ada kartu tinggi kosong |
| `<x-ui.card title icon subtitle link>` | header ber-ikon + chip/tautan "Lihat semua"; `flush` untuk tabel |
| `<x-ui.btn tone icon href>` | tombol `h-10 rounded-xl text-xs font-bold`; tone `primary` (solid) / `soft-*` (bg-50 text-700) / `dark`; `compact` = label disembunyikan di ponsel |
| `<x-ui.table-action tone icon label>` | tombol ikon 36 px: `view` (sky), `edit` (amber), `delete` (rose), `success` (emerald) |
| `<x-ui.badge tone dot>` | badge lembut `bg-*-50 text-*-700 ring-1` |
| `<x-ui.empty-state>` | ikon dalam kotak `brand-50`, judul, teks, aksi |
| `<x-ui.panduan>` | panduan singkat halaman (otomatis dari `App\Support\Panduan` per route & peran); **setiap halaman/menu baru wajib menambahkan entri panduan** |
| `<x-ui.notification-bell>` | dropdown Alpine; panel `fixed inset-x-4` di ponsel, `sm:absolute sm:right-0 sm:w-96` di desktop |

Komponen baru hanya bila polanya dipakai ≥2 modul. Halaman satu-pemakai tetap satu view.

## 5. Pola halaman daftar (index)
Satu `<section>` kartu berisi semuanya:
1. **Header**: judul daftar + jumlah data · toolbar aksi (`grid grid-cols-N gap-2 sm:flex`, tombol `compact` di ponsel).
2. **Filter** dalam header: `grid gap-2 sm:grid-cols-2 xl:grid-cols-[minmax(220px,1fr)_repeat(n,minmax(140px,0.4fr))_auto]`; tombol Filter + Reset (muncul bila filter aktif). Input ber-ikon memakai `pl-10`.
3. **Ponsel/tablet (`lg:hidden`)**: `divide-y` kartu ringkas → info utama, status, kotak metadata `grid grid-cols-2 rounded-xl bg-zinc-50 p-3`, lalu baris tombol aksi `grid grid-cols-3`.
4. **Desktop (`hidden lg:block`)**: `table-fixed` + `<colgroup>`; nomor & status & aksi lebar tetap, nama fleksibel; `truncate` + `title` untuk teks panjang; `whitespace-nowrap` untuk kode/tanggal/angka; aksi = `x-ui.table-action` (36 px per tombol + gap).
5. **Footer** paginasi di dalam kartu.

## 6. Pola form
- Create & edit memakai satu `_form.blade.php`.
- Header kartu: tombol kembali (kotak 40 px) + judul + konteks (membuat/memperbarui).
- Ringkasan error di atas form bila ada error, dan error di bawah field.
- Kelompokkan field per maksud (identitas, nilai, kondisi, lampiran) dalam kartu/fieldset ber-ikon.
- Grid 1 kolom di ponsel; 2–3 kolom hanya untuk nilai pendek.
- Footer aksi konsisten (Batal · Simpan), sticky di ponsel di atas bottom-nav.
- Hindari utility spacing ganda untuk properti yang sama (mis. `p-4` + `px-6`); tulis sumbu eksplisit.
- Dropdown/popover di ponsel: jangan `absolute right-0 w-64`; gunakan `fixed inset-x-4` lalu `sm:absolute sm:w-*`.

## 7. Dialog, konfirmasi, umpan balik
- Hapus, logout, hitung/hitung ulang, finalisasi, buka kembali, import → `<x-confirm-form>` (SweetAlert2). Merah untuk DELETE, petrol untuk lainnya.
- Toast hasil via `<x-flash>`; pesan menjelaskan hasil ("12 aset diimpor"), bukan sekadar "berhasil".
- Loading mencegah submit ganda (`busy`).
- **Notifikasi** (tabel `notifications` Laravel): kejadian penting lintas role dikirim lewat `App\Notifications\SistemNotification` (judul, pesan, url, ikon, tone) — mis. import selesai, MOORA dihitung (→ pimpinan), periode final (→ operator/admin).

## 8. Larangan
- File CSS/JS custom, `<style>`/`<script>` di view (kecuali `assets.blade.php`), atribut `style=""` kecuali nilai dinamis tak terhindarkan (lebar bar persen).
- `confirm()`, `alert()`, modal DaisyUI untuk konfirmasi.
- `max-w-*` pada pembungkus halaman daftar/dashboard.
- Mengubah logika bisnis/validasi/route demi tampilan.

## 9. Gerbang selesai per halaman
1. Semua view modul (index, create, edit, show, import, preview) ikut dimigrasi.
2. `grep -rn "<style\|<script" resources/views | grep -v assets.blade` kosong.
3. `php artisan view:cache` sukses; `composer check` hijau.
4. Screenshot **390 × 844** dan **1440 × 900** diperiksa: tidak ada gap samping, tidak ada overflow horizontal, ikon stat tampil, tombol ≥ 40 px.
5. Interaksi non-destruktif dicek: dropdown, filter, langkah form, notifikasi.
