![alt text](image.png)# CleanFlow — Ringkasan Konteks, Aturan, dan Prompt Multi-Agent

Pembaruan terakhir: 11 September 2026
Branch kerja: `cleanflow`
Fokus aktif: role Sekretaris, Ketua PKBM, dan Bendahara selesai dimigrasikan end-to-end. Batch berikutnya adalah audit dan migrasi Waka/Wali Kelas, kemudian Guru/Siswa/Wali Siswa.

## 1. Tujuan utama

Pola floating global: pada desktop tombol Bantuan berukuran konsisten 132 x 56 px, scroll-up 56 x 56 px, keduanya sejajar pusat, berjarak minimal 24 px, dan batas drag Bantuan tidak boleh memasuki lajur scroll-up; pada mobile keduanya tetap tersusun vertikal. Jangan mengandalkan padding untuk lebar FAB karena utility reset dapat berkonflik—gunakan dimensi desktop eksplisit. Payload jawaban AI wajib dinormalisasi sebelum render. JSON lengkap, JSON terbungkus string, maupun JSON yang terpotong harus dipulihkan menjadi teks, callout, tombol, dan tautan terkait yang aman; payload transport tidak boleh terlihat mentah dan migrasi localStorage harus merapikan riwayat lama dengan pola yang sama.

CleanFlow bukan sekadar mengganti warna atau class Bootstrap. Target akhirnya adalah:

1. Pengguna baru langsung memahami harus mulai dari menu apa.
2. Navigasi dan urutan kerja mengikuti tujuan pengguna, bukan nama teknis modul.
3. Tampilan konsisten, profesional, minimalis, mobile-first, dan tetap fleksibel di desktop.
4. Fungsi bisnis, validasi, hak akses, route, dan struktur data tetap bekerja seperti sebelumnya.
5. Tampilan memakai Tailwind sebagai sumber CSS dan Alpine untuk interaksi ringan.
6. Kode halaman tidak lagi bergantung pada file CSS/JS khusus yang tersebar.
7. Konfirmasi berbahaya dan dialog konsisten memakai SweetAlert atau dialog Tailwind/Alpine.

## 2. Batasan pekerjaan

### Boleh dilakukan

- Mengubah layout, hierarki visual, posisi tombol, pengelompokan menu, dan urutan informasi.
- Mengganti tabel desktop menjadi kartu pada ponsel.
- Menggabungkan create/edit yang memiliki field sama ke satu `form.blade.php`.
- Mengubah aksi singkat menjadi dialog/panel pada halaman yang sama.
- Mengganti JavaScript halaman dengan state Alpine atau input HTML native.
- Memperbaiki fallback gambar/file, label pilihan, overflow, kontras, dan state tombol.
- Menghapus CSS/JS/view mati setelah referensinya terbukti tidak digunakan dan seluruh validasi lulus.

### Tidak boleh dilakukan

- Mengubah logika bisnis, hasil perhitungan, validation rule, scope data, atau hak akses tanpa kebutuhan bug yang terbukti.
- Mengganti nama field, route, method HTTP, parameter, dan contract controller secara sembarangan.
- Menghapus fitur hanya karena sulit dipindahkan ke UI baru.
- Menggunakan Bootstrap untuk halaman yang sudah dimigrasikan.
- Menambahkan `<style>`, style inline, atau stylesheet khusus halaman baru.
- Membuat file JavaScript halaman jika perilakunya dapat ditulis ringkas dengan Alpine.
- Memecah satu halaman menjadi banyak partial satu-pemakai.
- Menggunakan lebar pembungkus utama `max-w-*` pada dashboard/list/table sehingga timbul gap saat zoom.
- Menyatakan modul selesai hanya karena halaman index sudah berubah; create, edit, show, import, print, modal, dan partial terkait harus ikut diaudit.
- Menghapus file sebelum memastikan tidak ada referensi aktif dan runtime sudah lulus.

## 3. Arsitektur tampilan yang dipakai

- Layout pascalogin memakai shell CleanFlow dari `layouts.app`/layout terkait.
- Tailwind adalah sumber tampilan utama.
- Alpine dipakai langsung pada Blade untuk dropdown, dialog, state kondisional, preview upload, pencarian lokal, dan pilihan panjang.
- `resources/js/admin.js` hanya untuk perilaku lintas aplikasi: shell/sidebar, menu search, notifikasi, SweetAlert, scroll-up, bantuan, dan komponen Alpine yang benar-benar reusable.
- Floating shell tidak boleh disalin per role. Scroll-up dimiliki `components/cleanflow/app-footer`; seluruh UI AI Assistant juga sudah menjadi Tailwind pada satu component shared. Keduanya berlaku lintas-role.
- Alpine adalah pilihan pertama. Satu modul JavaScript global masih boleh untuk komponen async lintas-role yang benar-benar kompleks bila memindahkan seluruh logika ke Blade merusak keterbacaan; AI Assistant adalah contoh yang sah karena menangani API, upload, riwayat lokal, lightbox, dan drag. Modul ini tidak boleh disalin per role atau dijadikan JS halaman.
- `vite.config.js` menemukan aset berdasarkan referensi Blade. Jangan menambah daftar entry manual per halaman.
- Token warna wajib berasal dari `tailwind.config.js`, terutama `brand-50` sampai `brand-950`.
- Font utama adalah Plus Jakarta Sans melalui konfigurasi global.

## 4. Pola layout dan responsive

### Pembungkus halaman

- Gunakan `min-w-0 w-full` pada pembungkus utama.
- Gunakan `space-y-5` atau jarak konsisten yang tidak berlebihan.
- Jangan membatasi lebar halaman daftar/dashboard dengan `mx-auto max-w-*`.
- `max-w-*` hanya untuk dialog atau blok teks panjang yang memang perlu dibatasi.
- Konten harus memenuhi ruang shell ketika viewport berubah atau browser di-zoom.

### Mobile-first

- Statistik ringkas biasanya 2 kartu per baris pada ponsel; 3/4/lebih pada desktop sesuai data.
- Hindari kartu tinggi dengan ruang kosong besar.
- Tombol utama harus mudah disentuh, minimum sekitar 40–44 px.
- Toolbar berubah dari kolom pada ponsel menjadi baris pada desktop.
- Tidak boleh ada horizontal overflow pada dokumen utama.
- Jika data benar-benar memerlukan lebar besar, scroll hanya boleh terjadi di pembungkus internal.

### Kontras

- Semua judul pada hero berwarna harus memakai warna eksplisit, umumnya `!text-white`.
- Jangan mengandalkan warna warisan dari parent.
- Periksa normal, hover, focus, active, disabled, loading, dan selected.
- Teks sekunder tetap harus terbaca; hindari abu-abu terlalu pucat di atas biru.

## 5. Pola tabel data

### Desktop (`lg` ke atas)

- Gunakan `table-fixed` dan `colgroup`.
- Checkbox/nomor dibuat tetap dan sempit.
- Status dan aksi memiliki lebar tetap.
- Nama/deskripsi/cabang memperoleh kolom fleksibel.
- Gunakan `truncate` dan `title` untuk nilai panjang.
- Gunakan `whitespace-nowrap` untuk nilai satu kesatuan seperti `12 SMP`, kode, tanggal, dan status.
- Aksi CRUD menggunakan ukuran, warna, ikon, dan jarak yang konsisten; prioritaskan `x-cleanflow.table-action` bila cocok.
- Kolom aksi memakai lebar tetap yang dihitung dari jumlah tombol (`36px` per tombol + gap), bukan persentase lebar tabel. Hindari ruang kosong lebar antara status dan aksi.

### Mobile/tablet

- Jangan memaksa tabel desktop mengecil.
- Buat kartu ringkas dengan informasi utama dahulu, metadata sesudahnya, lalu tombol aksi.
- Label harus menjelaskan konteks data, bukan hanya menyalin header tabel.

## 6. Pola form

- Create dan edit yang memiliki field sama memakai satu `form.blade.php`.
- Judul dan subtitle menjelaskan apakah pengguna sedang membuat atau memperbarui data.
- Tampilkan ringkasan error di atas serta error dekat field.
- Kolom wajib diberi tanda yang konsisten.
- Kelompokkan field berdasarkan maksud: identitas, periode, target, status, dan lampiran.
- Gunakan grid satu kolom di ponsel dan 2/3 kolom hanya bila nilai cukup pendek.
- Upload gambar memakai preview Alpine dalam file Blade yang sama.
- Input yang memiliki ikon di dalam field wajib memberi jarak aman dengan utility penting (misalnya `!pl-10`) selama compatibility CSS legacy masih aktif; cek jarak ikon, placeholder, teks ketikan, serta tombol hapus di mobile dan desktop.
- Hindari utility spacing yang menulis properti CSS sama pada satu elemen, termasuk lintas breakpoint: jangan gabungkan `p-*` dengan `px/py/pt/pr/pb/pl-*`, `px-*` dengan `pl/pr-*`, `py-*` dengan `pt/pb-*`, atau pola margin setara. Uraikan sejak awal menjadi sumbu/sisi eksplisit agar hasil tidak bergantung pada urutan CSS hasil build.
- Dropdown/popover mobile tidak boleh memakai lebar tetap dan anchor satu sisi terhadap tombol (`absolute right-0 w-64`, misalnya). Pada mobile, anchor panel ke container aksi selebar baris dengan `inset-x-0 w-auto`; aktifkan kembali `sm:right-0 sm:w-*` hanya pada breakpoint yang ruangnya cukup. Ukur `left >= 0` dan `right <= viewportWidth` pada DOM nyata ketika panel terbuka.
- Pilihan kondisional menggunakan `x-show`, `x-model`, dan `:required`.
- Tombol Batal dan Simpan berada pada footer form yang konsisten.
- Panel panduan hanya ditampilkan jika benar-benar membantu flow, bukan dekorasi.

## 7. Pola flow dan navigasi

- Menu awal tahun mengikuti urutan: Tahun Ajaran → Cabang → Pengguna → Kelas/Penugasan → Mata Pelajaran → Jadwal.
- Dashboard menampilkan pekerjaan berdasarkan tujuan pengguna, bukan istilah modul.
- Search pada topbar digunakan untuk menemukan menu atau pekerjaan.
- Pilihan kelas minimal menampilkan `nama kelas · jenjang · cabang` dan memakai ID kelas sebagai value.
- Daftar pilihan panjang tidak menggunakan `<select size>`; gunakan pencarian dan kartu radio/hasil.
- Aksi satu langkah seperti atur wali atau ganti guru tampil sebagai dialog/panel pada halaman yang sama.
- Alur kompleks seperti duplikasi periode harus menunjukkan sumber → tujuan, mencegah pilihan sama, dan menjelaskan data yang akan disalin.
- Untuk aksi massal, pengguna harus melihat jumlah pilihan, scope filter, serta akibat tindakan sebelum konfirmasi.

## 8. Dialog, konfirmasi, dan feedback

- Hapus, logout, rollback, proses massal, pembatalan, dan aksi berisiko menggunakan SweetAlert.
- Form standar dapat memakai atribut global `data-confirm`, `data-confirm-title`, `data-confirm-message`, dan `data-confirm-text`.
- Dialog kompleks boleh memakai `<dialog>`/Alpine dengan tampilan Tailwind.
- Jangan membuat modal Bootstrap.
- Loading harus mencegah submit ganda.
- Pesan sukses/gagal harus menjelaskan hasil tindakan, bukan hanya “berhasil” atau “error”.

## 9. Struktur file dan clean code

- Satu halaman tetap satu view utama jika markup hanya digunakan di sana.
- Jangan membuat wrapper/partial kecil tanpa reuse lintas modul.
- Reuse component hanya untuk pola yang nyata-nyata lintas banyak modul.
- Jangan membuat pasangan `resources/css/.../index.css` dan `resources/js/.../index.js` setelah migrasi.
- Alpine ringkas tetap berada di Blade; perilaku global/reusable saja yang masuk `resources/js/admin.js`.
- Komponen floating yang muncul pada banyak role wajib dimiliki layout/component bersama dan diuji minimal pada satu halaman tiap jenis shell; jangan memperbaikinya hanya melalui namespace admin.
- Jangan membuat CSS custom di Blade maupun file terpisah.
- Selalu cari referensi dengan `rg` sebelum menghapus file.
- Pertahankan perubahan pengguna lain pada worktree yang sudah kotor.

## 10. Gerbang penyelesaian setiap modul

Urutan wajib sebelum modul ditandai selesai:

1. Audit seluruh view, route, controller, model accessor, aset, dan test terkait.
2. Catat contract field/action/variable yang wajib dipertahankan.
3. Migrasikan semua view aktif dalam modul.
4. Cari ulang Bootstrap, `<style>`, style inline, dan referensi aset lokal.
5. Jalankan `php artisan view:clear` lalu `php artisan view:cache`.
6. Jalankan `npm run build`.
7. Uji route live dengan akun dummy pada desktop dan mobile.
8. Periksa status HTTP, error console/page, Alpine, gambar/file rusak, dan overflow horizontal.
9. Uji interaksi non-destruktif: dropdown, filter, dialog, preview, tab, dan navigasi.
10. Jalankan test terkait; jalankan seluruh suite secara berkala.
11. Hapus aset lama hanya bila tidak ada referensi dan seluruh pemeriksaan lulus.
12. Hapus folder kosong dan perbarui `docs/cleanflow-admin-progress.md`.

Viewport minimum pengujian:

- Mobile: 390 × 844.
- Desktop: 1440 × 900.
- Tambahkan desktop sempit/tablet bila halaman memiliki tabel atau dialog kompleks.

Lingkungan live lokal:

- Aplikasi: `http://sipaduhok.test`
- Vite dev: `http://[::1]:5174`
- Akun dummy admin: `admin1` / `password`
- Database berisi data dummy dan boleh digunakan untuk pemeriksaan tampilan; hindari aksi destruktif bila tidak diperlukan.

## 11. Progres saat ini

Modul admin selesai:

- Dashboard Admin
- Tahun Ajaran
- Cabang
- Pengguna
- Catatan
- Mata Pelajaran
- Pengaturan Istirahat
- Kelas
- Jadwal Pelajaran
- Guru Pengajar
- Wali Kelas
- Manajemen Siswa
- Monitoring
- Recovery Ticket
- AI Settings
- LMS Settings
- Akademik/Publikasi: Pengumuman, Berita, Flyer, dan Kalender Akademik
- Akademik/Publikasi: Kenaikan Kelas (`settings`, `kkm`, `rekap`, dan `print`)
- Keuangan: konfigurasi, validasi, dispensasi, Pembayaran, seluruh Tagihan, Laporan Pembayaran, dan enam jalur cetak admin (26 view, 0 aset UI lokal)
- Laporan admin: dua kelompok route memakai satu pusat laporan dan tujuh template cetak bersama (8 view, 0 aset UI lokal); view `admin/cetak-laporan` yang duplikat sudah dihapus.
- Landing Page: index, editor semua slug, dan item editor memakai Tailwind + Alpine (3 view, 0 aset UI lokal).

Dua puluh satu modul admin telah melewati migrasi view utama tanpa aset halaman khusus. Monitoring LMS bersama kini selesai untuk index, detail kelas, dialog catatan, serta preview materi/tugas/ujian; delapan aset CSS/JS lamanya telah dihapus setelah audit referensi dan runtime.

Fondasi global yang dipakai seluruh role kini juga selesai untuk Notification (index/detail), Profile, Account Settings, scroll-up, serta UI AI Assistant. Notification dan Profile memakai Tailwind + Alpine/HTML native tanpa aset halaman. Lima aset lamanya dihapus. Stylesheet AI shared juga dihapus; satu modul JS AI tetap dipertahankan sebagai modul global kohesif karena memuat alur async/API, upload, riwayat, dan drag—bukan styling atau perilaku satu halaman.

Catatan validasi terakhir:

- Build produksi berhasil.
- Pemeriksaan live seluruh Kenaikan Kelas berhasil pada 12 kombinasi route/viewport desktop dan mobile tanpa overflow, error console, atau overlay Vite.
- Empat test Promotion lulus dengan 23 assertion.
- Konfigurasi pembayaran lolos test backend 6 assertion serta live test mobile/desktop tanpa overflow atau error runtime.
- Validasi dan riwayat dispensasi lolos live test mobile/desktop; dialog pengajuan satuan dan massal berfungsi tanpa Bootstrap.
- Seluruh alur Pembayaran (index, riwayat siswa, input tunai, detail, dan kwitansi) lolos Blade cache dan audit live tanpa overflow/error console; aset halaman dan aset publik kwitansi sudah dihapus.
- Tagihan index/show/import/edit lolos audit live pada desktop dan 390 px. Filter kelas menyertakan cabang, aksi tabel konsisten 36×36, drag-and-drop serta mask nominal berfungsi, dan dialog simpan/hapus/reset memakai SweetAlert.
- Seluruh Tagihan lanjutan (duplikasi, carryover, massal, custom, Generate SPP, cetak tagihan siswa, dan cetak laporan) serta tiga Laporan Pembayaran admin beserta tiga halaman cetaknya telah dimigrasikan. Uji live desktop 1440x900 dan mobile 390x844 lulus tanpa overflow/error; dialog berisiko hanya dibuka lalu dibatalkan.
- Pusat Laporan umum dan Cetak Laporan telah disatukan ke delapan view Tailwind. Seluruh 15 route diuji live pada 1440x900 dan 390x844: HTTP 200, tanpa overflow dokumen, error console, overlay Vite, atau markup legacy.
- Landing Page index dan 14 editor slug telah diuji pada desktop/mobile. Navigasi bagian, visibilitas, cloning nama field, tambah/hapus item, preview gambar, dan dialog reset berjalan melalui Alpine tanpa Bootstrap atau aset halaman terpisah. Beberapa gambar dummy lama mengarah ke file storage yang sudah tidak ada; pratinjau menyembunyikannya tanpa merusak layout.
- Folder aset halaman khusus Keuangan, Laporan, dan Landing Page, dua aset cetak publik Tagihan, serta view laporan duplikat telah dihapus setelah audit referensi. Tes regresi terfokus terakhir: 14 tes, 503 assertion lulus.
- `/admin/keuangan/tagihan/255/cetak`, index Tagihan, dan kartu siswa telah diuji ulang pada 1440x900 serta 390x844: HTTP 200, tanpa overflow atau error console. Halaman cetak tidak memiliki marker Bootstrap; padding kiri pencarian Tagihan terhitung 40px.
- Validasi dispensasi memperoleh kolom aksi yang menghitung lebar tombol dan padding; Riwayat Dispensasi memakai susunan stat card 1+2 pada mobile; tiga aksi filter Laporan Keuangan tetap satu baris; wrapper Catatan kini memenuhi lebar shell.
- Seluruh Monitoring LMS shared telah didesain ulang dengan Tailwind + Alpine/HTML native dan delapan aset lokalnya dihapus. Audit live langsung mencakup detail kelas serta preview materi, tugas, dan ujian pada desktop/mobile; audit menemukan lalu mengunci perbaikan parse preview ujian.
- Audit browser terakhir menelusuri 36 tautan menu admin pada 1440x900 dan 390x844 tanpa HTTP error, overflow dokumen, error JavaScript, atau overlay Vite.
- FAB Bantuan shared telah dipindahkan ke utility Tailwind tanpa selector CSS khusus; pusatnya sejajar dengan scroll-up pada desktop, drag horizontal tetap aktif, dan keduanya bertumpuk dengan gap 14 px pada mobile.
- Notification, Profile, dan Account Settings diuji live pada 1440x900 serta 390x844: seluruh route HTTP 200, overflow 0 px, tanpa error console atau overlay Vite. Jendela AI dan sidebar riwayat berhasil dibuka pada kedua viewport dan seluruh bounding box tetap di dalam layar.
- Audit Profile menemukan bug lama pemetaan nomor: input `no_telepon` kini benar-benar disimpan ke `siswa.telepon_orangtua` atau `tenaga_pendidik.telepon` sesuai role.
- Full test suite terbaru: 183 lulus (1.734 assertion), 1 gagal pada fixture lama `SiswaImportStatusTest` karena baris fixture tidak memiliki `nama_kelas` dan `agama`; test pendamping dengan fixture valid lulus dan kegagalan tidak terkait migrasi UI.
- Role Sekretaris selesai: dashboard dan sidebar baru, Berita/Flyer/Kalender/Pengumuman memakai sembilan view Tailwind bersama dengan Admin, 17 aset role + 2 aset dashboard serta sembilan view duplikat dihapus. Empat feature test dengan 97 assertion dan audit live 22 kombinasi route/viewport lulus; feed kalender serta dua PDF tetap aktif.
- Audit regresi lintas Admin, Waka shared, Sekretaris, dan komponen global menormalkan 22 benturan shorthand/sumbu spacing. Dropdown Buat Tagihan, Aksi Jadwal, dan Ekspor Jadwal sekarang memakai panel mobile selebar container; pengukuran live 390x844 menunjukkan overflow kiri, kanan, dan dokumen 0 px. Guard spacing/panel permanen, build produksi, Blade cache, serta 41 tes dengan 973 assertion lulus.
- Role Ketua PKBM selesai: dashboard/sidebar dan halaman keputusan khusus memakai Tailwind + Alpine/native dialog; Monitoring, Laporan/cetak, Catatan, dan Riwayat Dispensasi memakai view bersama yang sadar namespace route. Lima belas view duplikat, 19 aset CSS/JS role, dan stylesheet dashboard dihapus. Audit live 16 kombinasi route/viewport menunjukkan HTTP 200 dan overflow 0 px tanpa error console; 13 tes terfokus lulus dengan 169 assertion.
- Role Bendahara selesai: dashboard/sidebar, validasi dispensasi, Validasi Akses, lima halaman Pembayaran, sepuluh halaman Tagihan, dan enam Laporan/cetak memakai Tailwind + Alpine. Dua puluh empat view duplikat dan 31 aset role telah dihapus; folder Pembayaran, Tagihan, dan Laporan yang kosong turut dibersihkan. Dashboard memakai Alpine langsung tanpa CSS/JS halaman. Config Pembayaran tetap khusus Admin. Pengujian juga menemukan lalu memperbaiki agregasi laporan `DAY()` agar kompatibel MySQL, PostgreSQL, dan SQLite.
- Audit final Bendahara: 11 feature test/181 assertion lulus. Dashboard dan index Tagihan diuji live pada 1440x900 serta 390x844 dengan overflow 0 px, tanpa marker Bootstrap atau error console; dropdown Buat Tagihan tetap di viewport dan HTML Bendahara tidak memuat endpoint Import/Reset Admin. Bridge global sempat menambahkan border kembali ke item sidebar tunggal, sehingga direct link kini dikunci `!border-0 !ring-0`; pengukuran browser menghasilkan 0 px untuk item tunggal dan 1 px untuk grup dropdown.
- Guard penugasan Guru diperketat end-to-end. Kandidat UI, request Admin/Waka (biasa, multi-jenjang, ganti, dan massal), import, rebuild tabel turunan, statistik, serta pengirim notifikasi kini memakai scope kelayakan tunggal: akun aktif dengan role legacy dan `role_id` yang konsisten sebagai `guru_pengajar`. Perubahan role Guru ditolak selama tanggung jawab akademik masih ada. Database dibersihkan secara transaksional: 7 jadwal invalid dikosongkan dengan audit history, 18 penugasan turunan dan 2 notifikasi salah sasaran dihapus; audit ulang menghasilkan nol temuan.
- Penyesuaian indeks pencarian menu per role dicatat sebagai backlog global berikutnya. Hasil pencarian harus berasal dari capability/navigation role aktif, bukan katalog Admin yang sekadar disaring di sisi tampilan.
- Sidebar Admin, Sekretaris, Ketua, dan Bendahara kini memakai hierarki visual yang sama: item tunggal tanpa border, parent dropdown dan submenu tetap berborder. Sidebar Bendahara telah diubah dari markup legacy menjadi Tailwind; guard source mencegah pola border tunggal muncul kembali.

## 12. Prompt siap pakai untuk multi-agent

Salin prompt berikut saat ingin melanjutkan dengan beberapa agent:

```text
Kita sedang mengerjakan branch `cleanflow` di project Laravel SIPADUHOK.

Baca seluruh file berikut sebelum mengubah kode:
1. `conversation.md`
2. `docs/cleanflow-admin-progress.md`
3. controller, route, model, view, aset, dan test milik modul yang ditugaskan.

Tujuan:
- Migrasikan seluruh UI setelah login menjadi Tailwind + Alpine.
- Pertahankan semua fungsi bisnis, validasi, route, permission, nama field, method HTTP, dan contract controller.
- Buat UI profesional, minimalis, mobile-first, fleksibel saat browser di-zoom, serta UX yang menjelaskan urutan kerja.
- Hapus Bootstrap/modal lama; gunakan dialog Alpine/HTML native untuk flow berform dan SweetAlert global untuk konfirmasi sederhana.
- Setelah satu modul benar-benar selesai, hapus CSS/JS halaman terpisah yang sudah tidak digunakan.
- Fondasi global Notification, Profile, Account Settings, scroll-up, UI AI Assistant, serta seluruh role Sekretaris, Ketua PKBM, dan Bendahara sudah selesai. Config/Pengaturan Pembayaran tetap eksklusif Admin dan tidak boleh diperkenalkan kembali ke route, controller, sidebar, maupun knowledge base Bendahara. Pertahankan contract dan polanya; jangan membuat ulang aset lokal atau view duplikat untuk fitur-fitur ini.

Aturan wajib:
- Jangan menambah `<style>`, style inline, CSS halaman, atau JS halaman baru.
- Gunakan Alpine langsung di Blade untuk interaksi ringan.
- JS AI Assistant adalah pengecualian global yang disengaja karena memuat API async, model, upload, riwayat, lightbox, dan drag. Jangan pecah atau salin modul ini per role; styling-nya tetap murni utility Tailwind tanpa CSS komponen.
- Jangan memecah halaman menjadi banyak partial satu-pemakai.
- Pada sidebar, item tunggal tidak memakai border/ring agar navigasi tetap ringan; state aktif dibedakan dengan latar dan warna teks. Border dipertahankan pada pemicu grup dan item submenu untuk menegaskan bahwa elemen tersebut satu kesatuan yang dapat dibuka.
- Bila dua role memiliki field dan flow publikasi yang identik, gunakan satu view bersama yang menerima konteks route; jangan mempertahankan salinan Blade per role. Uji URL create/edit/toggle/destruktif pada kedua namespace agar reuse tidak mengarahkan role ke middleware role lain.
- Bila flow identik dipakai tiga role atau lebih, hitung satu `routePrefix` berdasarkan namespace route di awal view dan bangun seluruh endpoint dari sana. Hindari kondisi role tersebar pada setiap kontrol; render-test setiap konteks wajib memastikan endpoint role lain tidak bocor ke HTML.
- Sebelum menyimpulkan dua role dapat memakai flow yang sama, buat matriks capability. Fitur sensitif yang eksklusif Admin harus tidak memiliki route dan link sidebar pada role lain; menyembunyikan elemen secara visual saja tidak cukup. Tambahkan tes `Route::has(...)` dan URL langsung untuk membuktikan batas otoritas.
- Pada view bersama, audit juga HTML hasil render untuk role terbatas. Checkbox, toolbar massal, handler Alpine, form tersembunyi, dan endpoint API khusus Admin tidak boleh ikut terkirim jika capability itu tidak tersedia.
- Filter pilihan pada UI bukan batas keamanan. Setiap ID relasi yang menentukan capability (misalnya `guru_id`) wajib divalidasi ulang di server pada create, update, aksi satuan/massal, import, sinkronisasi/rebuild, dan pengiriman notifikasi melalui satu scope/model rule bersama.
- Satu orang boleh memiliki beberapa akun dengan role berbeda, tetapi relasi pekerjaan dan notifikasi harus menunjuk ke akun yang memiliki capability tersebut. Jangan menganggap kesamaan nama/profil tenaga pendidik sebagai izin lintas-role.
- Pencarian menu global wajib membangun indeks dari konfigurasi navigasi role aktif dan tetap menghormati permission route. Jangan menampilkan hasil yang berakhir 403 hanya karena label tersebut tersedia pada role lain.
- Untuk flow keputusan satuan dan massal, gunakan satu dialog Alpine yang menerima URL aksi, nama record, tipe keputusan, dan array ID melalui state/data attribute. Jangan membuat satu modal per baris.
- Create/edit dengan field sama memakai satu `form.blade.php`.
- Desktop table memakai `table-fixed` + `colgroup`; mobile memakai kartu.
- Lebar kolom aksi harus dihitung dari jumlah tombol 36px beserta gap; jangan memakai persentase yang menciptakan ruang kosong antara status dan aksi.
- Perhitungan kolom aksi wajib menambahkan padding kiri/kanan. Setelah render, ukur jarak badge status ke tombol pertama dan pastikan kelompok tombol tidak meluber ke kolom sebelumnya.
- Semua input dengan ikon di dalamnya wajib memiliki padding kiri aman (`!pl-10` selama CSS compatibility masih dimuat), kemudian audit posisi ikon terhadap placeholder dan teks aktual pada mobile serta desktop.
- Jangan mencampur shorthand spacing dan utility sumbu/sisi yang bertumpang tindih pada elemen yang sama, termasuk lintas breakpoint. Gunakan `px-* py-*`, `pl-* pr-*`, atau `mx-* my-*` secara eksplisit dan jalankan guard spacing sebelum menyatakan selesai.
- Semua dropdown/popover wajib diuji dalam keadaan terbuka pada viewport mobile. Gunakan container aksi `relative` selebar baris dan panel `absolute inset-x-0 w-auto` di mobile; lebar tetap serta anchor kanan hanya boleh mulai breakpoint `sm:`. Verifikasi bounding box panel tidak melewati kedua sisi viewport.
- Stat card mobile tidak boleh dipaksa tiga kolom jika ikon dan label berhimpitan; gunakan kartu ringkasan utama selebar dua kolom dengan dua kartu status di bawahnya.
- Tiga aksi filter mobile harus tetap satu baris memakai grid tiga kolom. Gunakan label adaptif bila lebar kurang, tanpa menghilangkan arti tindakan.
- Editor panjang memakai navigasi sticky dengan tinggi maksimum viewport pada desktop serta select bagian sticky pada mobile. Gunakan `overflow-x-clip` pada ancestor, `scroll-margin` pada target, dan sinkronkan section aktif melalui IntersectionObserver.
- Halaman daftar/empty state tidak boleh dibatasi `max-w-*`; hanya teks penjelasan yang boleh diberi batas baca.
- Validasi input berikon memakai DOM hasil render: catat `padding-left` terhitung dan pastikan bounding box ikon tidak bertabrakan dengan placeholder/teks pada mobile maupun desktop.
- Audit “full Tailwind” harus mencakup layout, slot, partial, dan component bersama yang ikut muncul pada DOM hasil render. Jangan menyatakan bersih hanya berdasarkan pencarian di namespace view modul; bila legacy berasal dari komponen lintas-role, laporkan ownership dan migrasikan sebagai alur tersendiri.
- Audit halaman cetak dimulai dari route -> controller -> view akhir, termasuk controller admin yang mewarisi role lain; jangan menilai hanya dari nama file atau namespace route.
- Semua pratinjau/cetak HTML admin memakai `layouts.print` dan utility `print:` Tailwind tanpa Bootstrap/aset halaman. Template PDF DomPDF dan Excel adalah dokumen server-side; CSS dokumen atau atribut spreadsheet hanya boleh dipertahankan bila renderer membutuhkannya dan tidak boleh disamakan dengan UI Bootstrap.
- Semua aksi ikon ringkas pada tabel desktop wajib memakai `<x-cleanflow.table-action>` berukuran `h-9 w-9`, `rounded-lg`, dan `ring-1`; jangan mencampur tombol manual untuk visibilitas, unggulan, status, salin, atau CRUD dalam kelompok yang sama.
- Jangan menyisipkan `@js(...)` langsung ke atribut komponen Blade untuk payload Alpine. Gunakan atribut `data-*` yang ter-escape dan baca dengan `$el.dataset...`, lalu uji interaksinya secara live agar tidak ada ekspresi Alpine mentah atau dialog yang gagal terbuka.
- Untuk toolbar pilihan massal atau elemen fixed di dalam shell, gunakan `x-teleport="body"` dan audit posisi nyata dengan `getBoundingClientRect()`; transform parent dapat membuat elemen terlihat jauh di luar viewport.
- Saat live test aksi destruktif, buka lalu batalkan SweetAlert. Jangan menekan konfirmasi terhadap database bersama.
- Wrapper utama memakai `min-w-0 w-full`, bukan `max-w-*`.
- Semua state normal/hover/focus/disabled/loading harus terbaca.
- Pilihan kelas menampilkan `nama · jenjang · cabang` dan value ID.
- Jangan menghapus fitur atau mengubah logika untuk mempermudah migrasi.
- Query agregasi laporan tidak boleh mengasumsikan satu driver database. Fungsi tanggal/raw SQL seperti `DAY()`, `DATE_FORMAT()`, dan `strftime()` wajib dipetakan per driver atau diganti API query yang portabel; jalankan route test pada SQLite selain audit live MySQL.
- Jangan menghapus file sampai `rg` membuktikan tidak ada referensi dan validasi runtime lulus.
- Untuk view bercabang berdasarkan jenis data (misalnya preview materi/tugas/ujian), test harus merender setiap cabang memakai fixture nyata; Blade cache saja tidak menjalankan seluruh PHP hasil kompilasi dan tidak cukup menangkap parse/runtime error cabang tertentu.
- Worktree dipakai bersama dan mungkin kotor; jangan reset/revert perubahan agent atau pengguna lain.

Pembagian agent harus berdasarkan kepemilikan folder yang tidak tumpang tindih. Rekomendasi:
- Agent Koordinator: audit route/controller, integrasi akhir, tracker, build, test, live test, dan file shared; jangan mengambil view yang sedang dikerjakan agent lain.
- Agent Role Bendahara: kerjakan satu alur keuangan secara end-to-end, termasuk daftar, detail, form, keputusan, riwayat, dan cetak miliknya.
- Agent Role Waka/Wali Kelas: kerjakan alur akademik role tersebut tanpa menyentuh view shared yang sudah dimiliki koordinator.
- Agent Role Guru/Siswa/Wali Siswa/Bendahara: bagi berdasarkan folder role dan alur lengkap, bukan per halaman index.
- Agent Audit Clean Code: cari Bootstrap/Sneat, aset yatim, view duplikat, dan referensi mati hanya pada role yang sudah selesai; jangan menghapus aset yang masih direferensikan role lain.

Setiap agent sebelum edit harus mengirim ke koordinator:
1. daftar file yang akan dimiliki,
2. route/controller contract yang ditemukan,
3. risiko logika dan rencana validasi.

Setiap agent setelah edit harus mengirim:
1. daftar view yang selesai,
2. daftar aset yang aman dihapus (jangan hapus bila masih dipakai role lain),
3. hasil Blade cache/build/test terkait,
4. route live dan viewport yang diuji,
5. temuan bug atau contract yang belum pasti.

Hanya koordinator yang memperbarui `docs/cleanflow-admin-progress.md`, menghapus folder bersama, menjalankan full build/full test, dan menyatakan modul selesai. Jika dua modul memakai aset/controller yang sama, berhenti dan koordinasikan ownership sebelum edit.

Mulai dengan audit read-only. Jangan langsung melakukan rewrite massal. Kerjakan satu alur lengkap (index/create/edit/show/import/print/dialog) lalu validasi sebelum berpindah ke alur berikutnya.
```

## 13. Urutan kerja yang direkomendasikan

Migrasi admin dan fondasi global sudah selesai. Urutan kerja berikutnya:

1. Sekretaris — selesai; publikasi Admin/Sekretaris kini memakai view bersama.
2. Ketua PKBM — selesai; dashboard, keputusan, monitoring, laporan/cetak, dan catatan bersih dari aset UI lokal.
3. Bendahara — selesai; seluruh dashboard, keuangan, validasi, Tagihan, Pembayaran, dan Laporan/cetak bersih dari aset UI role lokal. Config Pembayaran tetap eksklusif Admin.
4. Waka dan Wali Kelas untuk alur akademik yang saling berkaitan.
5. Guru, Siswa, dan Wali Siswa setelah pola LMS serta kartu mobile dikunci.
6. Setelah setiap role selesai, audit lintas-role dan baru kurangi Bootstrap/Sneat dari bridge global ketika tidak ada consumer tersisa.
