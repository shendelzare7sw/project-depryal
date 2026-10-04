# Dokumentasi SIKASET

**Sistem Pendukung Keputusan Penilaian Kelayakan Aset (Metode MOORA)**
Pemerintah Kecamatan Batuceper, Kota Tangerang

Versi dokumen: 4 Oktober 2026 (branch `production-ready`)

---

## Daftar Isi

1. Gambaran Umum
2. Peran Pengguna
3. Cara Kerja Aplikasi Secara Keseluruhan
4. Siklus Periode Penilaian
5. Cara Kerja Metode MOORA
6. Alur Kerja per Peran
7. Daftar Fitur, Kegunaan, dan Cara Kerjanya
8. Matriks Hak Akses
9. Keamanan dan Perlindungan Data
10. Istilah Penting
11. Informasi Teknis Singkat

---

## 1. Gambaran Umum

SIKASET adalah aplikasi web untuk menilai kelayakan **Barang Milik Daerah (BMD)** berupa gedung dan bangunan di Kecamatan Batuceper. Aplikasi membantu menjawab pertanyaan: *aset mana yang layak dipertahankan, diperbaiki, atau diusulkan untuk dihapus?*

Penilaian dilakukan dengan metode **MOORA** (*Multi-Objective Optimization on the basis of Ratio Analysis*). Setiap aset dinilai pada beberapa kriteria (misalnya fungsi aset, efektivitas pemanfaatan, dan biaya pemeliharaan), lalu sistem menghitung skor dan menyusun peringkat seluruh aset.

**Prinsip utama:**

- Sistem hanya **alat bantu**. Rekomendasi sistem bersifat saran; **keputusan akhir tetap di tangan Pimpinan** (Camat).
- Setiap kegiatan penilaian tercatat dalam **periode** sehingga hasil tahun ini dan tahun lalu dapat dibandingkan dan ditelusuri ulang.
- Setiap perubahan data tercatat di **audit log** (siapa, kapan, apa yang berubah).
- Tampilan dirancang **mobile-first**: nyaman dipakai di HP maupun komputer, dan dilengkapi panduan singkat di setiap halaman untuk pengguna awam.

---

## 2. Peran Pengguna

SIKASET memiliki tiga peran dengan tugas yang dipisahkan (prinsip pemisahan tugas pengelolaan BMD).

| Peran | Siapa | Tugas utama |
|---|---|---|
| **Operator** | Pengurus Barang | Mengelola data aset, kategori, dan kriteria; membuat periode; mengisi nilai; menjalankan perhitungan MOORA; mencetak laporan |
| **Pimpinan** | Camat / Sekretaris Camat | Meninjau peringkat, menetapkan tindakan untuk setiap aset, memfinalisasi periode, mencetak laporan |
| **Admin** | Administrator sistem | Mengelola akun pengguna, pengaturan, integrasi notifikasi, cadangan data, dan memantau audit log. Admin **tidak** mengubah data penilaian (hanya melihat) |

Selain ketiga peran tersebut, **siapa pun** (tanpa login) dapat membuka halaman **Verifikasi Dokumen** untuk memeriksa keaslian laporan PDF melalui kode QR.

---

## 3. Cara Kerja Aplikasi Secara Keseluruhan

Secara garis besar, aplikasi bekerja dalam urutan berikut:

```
 OPERATOR                          PIMPINAN                        ADMIN
 -------------------------------   -----------------------------   -------------------------
 1. Data aset (input / import)                                     Notifikasi: import selesai
 2. Kriteria & bobot (total 100%)
 3. Buat periode & pilih aset
 4. Isi nilai tiap aset
 5. Hitung MOORA  --------------->  Notifikasi: peringkat siap      Notifikasi (salinan)
                                    6. Tinjau peringkat
                                    7. Tetapkan tindakan per aset
                                    8. Finalisasi periode  ------>  Notifikasi: periode final
    Notifikasi: periode final  <---
 9. Cetak laporan PDF/Excel         9. Cetak laporan PDF/Excel      Unduh laporan
10. Buka kembali (bila koreksi) -->  Notifikasi: dibuka kembali      Audit log mencatat semua
```

**Penjelasan singkat:**

1. **Data aset** dimasukkan satu per satu atau di-import dari Excel data BMD.
2. **Kriteria** penilaian diatur beserta bobotnya; jumlah bobot kriteria aktif harus tepat **100%**.
3. **Periode penilaian** dibuat (misalnya "Penilaian Kelayakan BMD 2026") dan aset yang akan dinilai dipilih.
4. Operator **mengisi nilai** setiap aset untuk setiap kriteria (skala 1–5, dengan rubrik penjelas), menulis kondisi, dan melampirkan foto.
5. Setelah semua nilai lengkap, operator menjalankan **Hitung MOORA**. Sistem menghasilkan skor, peringkat, dan rekomendasi untuk setiap aset, lalu mengirim notifikasi ke Pimpinan.
6. **Pimpinan** meninjau peringkat dan menetapkan tindakan untuk setiap aset: **Pertahankan**, **Perbaiki**, atau **Hapus**.
7. Setelah semua aset diputuskan, Pimpinan melakukan **Finalisasi**. Data periode terkunci dan status aset diperbarui otomatis.
8. **Laporan** PDF (siap tanda tangan, dengan QR verifikasi) atau Excel dapat dicetak kapan saja setelah perhitungan.
9. Bila ada kekeliruan setelah final, operator dapat **membuka kembali** periode dengan alasan tertulis.

---

## 4. Siklus Periode Penilaian

Setiap periode melewati empat status:

```
 DRAFT  --(semua nilai lengkap)-->  DINILAI  --(Hitung MOORA)-->  DIHITUNG  --(semua aset diputuskan + Finalisasi)-->  FINAL
   ^                                   ^                              |
   |____ nilai diubah / aset ditambah _|_________ diubah lagi ________|

 FINAL  --(Buka kembali + alasan)-->  DIHITUNG
```

| Status | Arti | Yang bisa dilakukan |
|---|---|---|
| **Draft** | Periode dibuat, nilai belum lengkap | Isi nilai, tambah/keluarkan aset, ubah atau hapus periode |
| **Dinilai** | Semua aset sudah dinilai untuk semua kriteria aktif | Hitung MOORA, ubah atau hapus periode |
| **Dihitung** | Peringkat dan rekomendasi sudah ada | Pimpinan menetapkan keputusan; operator masih bisa mengoreksi (hasil lama dihapus, perlu hitung ulang) |
| **Final** | Semua aset sudah diputuskan dan dikunci | Hanya dilihat dan dicetak; bisa dibuka kembali oleh operator dengan alasan |

**Aturan penting:**

- Hanya boleh ada **satu periode aktif** (belum final) pada satu waktu, supaya tidak membingungkan.
- Satu periode minimal berisi **2 aset** (MOORA membandingkan antar-aset).
- Mengubah nilai atau daftar aset pada periode yang sudah **Dihitung** akan menghapus hasil MOORA dan keputusan periode tersebut. Ada konfirmasi sebelum hal ini terjadi, dan perhitungan harus dijalankan ulang.
- Saat Finalisasi, status aset berubah otomatis: Pertahankan menjadi **Aktif**, Perbaiki menjadi **Dalam Perbaikan**, Hapus menjadi **Diusulkan Hapus**.

---

## 5. Cara Kerja Metode MOORA

MOORA membandingkan seluruh aset sekaligus. Langkah perhitungannya:

1. **Matriks keputusan.** Nilai setiap aset (baris) pada setiap kriteria (kolom).
2. **Normalisasi.** Setiap nilai dibagi akar dari jumlah kuadrat nilai pada kriteria tersebut, sehingga kriteria yang berbeda dapat dibandingkan:
   `x*ij = xij / akar(jumlah xij²)`
3. **Pembobotan.** Nilai ternormalisasi dikalikan bobot kriteria: `vij = bobot j × x*ij`
4. **Nilai optimasi (Yi).** Jumlah nilai kriteria *benefit* (makin tinggi makin baik) dikurangi jumlah nilai kriteria *cost* (makin tinggi makin buruk):
   `Yi = jumlah v (benefit) − jumlah v (cost)`
5. **Skor relatif 0–100.** Agar mudah dibaca: `(Yi − Ymin) / (Ymax − Ymin) × 100`. Aset terbaik bernilai 100, terendah 0.
6. **Peringkat.** Aset diurutkan dari Yi terbesar. Nilai yang sama mendapat peringkat yang sama.
7. **Rekomendasi.** Berdasarkan skor relatif dan ambang yang diatur Admin (bawaan):
   - skor **≥ 66,67** → **Pertahankan**
   - skor **< 33,33** → **Hapus**
   - di antaranya → **Perbaiki**

**Contoh perhitungan** (bobot: Fungsi 40% benefit, Efektivitas 35% benefit, Biaya 25% cost):

| Aset | Fungsi | Efektivitas | Biaya | Yi | Skor relatif | Peringkat | Rekomendasi |
|---|---|---|---|---|---|---|---|
| A1 | 4 | 3 | 2 | 0,2693 | 62,65 | 2 | Perbaiki |
| A2 | 2 | 2 | 4 | 0,0215 | 0,00 | 4 | Hapus |
| A3 | 5 | 4 | 1 | 0,4170 | 100,00 | 1 | Pertahankan |
| A4 | 3 | 5 | 3 | 0,2645 | 61,43 | 3 | Perbaiki |

Hasil ini juga diuji otomatis oleh sistem sehingga perhitungan aplikasi selalu cocok dengan perhitungan manual.

**Peringatan "biaya tinggi".** Bila sebuah aset bernilai Fungsi ≥ 4 tetapi Biaya Pemeliharaan berada pada skala tertinggi, sistem menampilkan tanda peringatan *"Berfungsi tetapi biaya tinggi — pertimbangkan penghapusan"*. Ini hanya informasi tambahan untuk Pimpinan; rekomendasi tidak berubah otomatis.

---

## 6. Alur Kerja per Peran

### 6.1 Operator (Pengurus Barang)

| Langkah | Menu | Yang dilakukan | Hasil |
|---|---|---|---|
| 1 | Data Aset > Tambah Aset | Isi data BMD (tahap 1), lalu kondisi dan foto (tahap 2) | Aset tersimpan; bisa langsung ikut periode berjalan |
| 2 | Data Aset > Import BMD | Unduh template, isi di Excel, unggah, periksa pratinjau, simpan | Banyak aset masuk sekaligus |
| 3 | Kategori Aset | Tambah atau ubah kategori | Aset terkelompok rapi |
| 4 | Kriteria | Atur kriteria, tipe, bobot (total 100%), dan rubrik skala | Siap dipakai menghitung |
| 5 | Periode Penilaian > Buat Periode | Isi nama, tanggal, cakupan aset | Periode berstatus Draft |
| 6 | Periode > Input Nilai | Pilih nilai 1–5 tiap kriteria, tulis kondisi, ambil foto, lalu Simpan & Lanjut | Status menjadi Dinilai bila semua lengkap |
| 7 | Periode > Hitung MOORA | Konfirmasi perhitungan | Peringkat terbentuk; Pimpinan diberi tahu |
| 8 | Laporan | Pilih periode, jenis, format, lalu Buat & Unduh | Berkas laporan tersimpan di riwayat |
| 9 | Periode > Buka Kembali | Isi alasan (bila ada koreksi setelah final) | Periode kembali Dihitung; Pimpinan diberi tahu |

### 6.2 Pimpinan (Camat)

| Langkah | Menu | Yang dilakukan | Hasil |
|---|---|---|---|
| 1 | Notifikasi / Beranda | Buka pemberitahuan "Peringkat siap ditinjau" atau tekan Mulai Putuskan | Langsung menuju aset yang menunggu keputusan |
| 2 | Peringkat Aset | Tinjau urutan, skor, rekomendasi, peringatan, dan detail perhitungan | Dasar pengambilan keputusan |
| 3 | Keputusan | Pilih Pertahankan / Perbaiki / Hapus untuk tiap aset | Otomatis lanjut ke aset berikutnya |
| 4 | Finalisasi Periode | Konfirmasi setelah semua aset diputuskan | Data terkunci; status aset diperbarui; Operator dan Admin diberi tahu |
| 5 | Laporan | Cetak laporan PDF atau Excel | Dokumen siap ditandatangani |

Bila keputusan Pimpinan berbeda dari rekomendasi sistem, **catatan alasan wajib diisi** (minimal 10 karakter) agar keputusan dapat dipertanggungjawabkan.

### 6.3 Admin (Administrator Sistem)

| Menu | Yang dilakukan |
|---|---|
| Beranda | Melihat ringkasan sistem: jumlah pengguna, aktivitas terbaru, ambang rekomendasi |
| Pengguna | Menambah akun, mengubah peran, menonaktifkan/mengaktifkan, mereset kata sandi |
| Pengaturan | Mengatur ambang rekomendasi, identitas instansi dan penandatangan laporan, batas logout otomatis, masa simpan cadangan |
| Integrasi | Mengatur bot Telegram dan email (SMTP) untuk notifikasi; mengirim pesan uji |
| Cadangan | Membuat, mengunduh, dan menghapus cadangan data |
| Audit Log | Menelusuri seluruh perubahan data |
| Data penilaian | Hanya melihat aset, kriteria, periode, hasil MOORA, dan mengunduh laporan |

---

## 7. Daftar Fitur, Kegunaan, dan Cara Kerjanya

### A. Akun dan Keamanan

**A1. Masuk (Login)**
- *Kegunaan:* memastikan hanya pegawai yang berwenang yang dapat memakai aplikasi.
- *Cara kerja:* pengguna memasukkan username atau email dan kata sandi. Setelah 5 kali gagal dalam satu menit, percobaan ditahan sementara. Akun yang dinonaktifkan Admin tidak bisa masuk. Bila kunci Cloudflare Turnstile dipasang, ada pemeriksaan anti-bot otomatis.

**A2. Wajib Ganti Kata Sandi Sementara**
- *Kegunaan:* mencegah akun terus memakai kata sandi awal yang diketahui orang lain.
- *Cara kerja:* akun yang baru dibuat atau direset oleh Admin otomatis diarahkan ke halaman "Ganti kata sandi" saat masuk. Pengguna tidak dapat membuka menu lain sebelum kata sandi baru (minimal 8 karakter, berbeda dari yang lama) disimpan.

**A3. Lupa Kata Sandi**
- *Kegunaan:* pengguna dapat memulihkan akunnya sendiri tanpa menunggu Admin.
- *Cara kerja:* pengguna menekan "Lupa kata sandi?", memasukkan email, lalu menerima tautan yang berlaku 60 menit untuk membuat kata sandi baru. Pesan yang tampil selalu sama (tidak membocorkan apakah email terdaftar). Pengguna tanpa email meminta Admin untuk mereset.

**A4. Logout Otomatis saat Tidak Aktif**
- *Kegunaan:* komputer kantor sering dipakai bergantian; sesi tidak boleh terbuka terus.
- *Cara kerja:* bila tidak ada aktivitas (klik, ketik, sentuh, gulir) selama batas waktu yang diatur Admin (bawaan 30 menit), pengguna dikeluarkan otomatis dan diberi pesan penjelasan. Aktivitas di tab lain ikut dihitung sehingga tab yang masih dipakai tidak ikut keluar.

**A5. Profil Saya**
- *Kegunaan:* pengguna mengelola data akunnya sendiri.
- *Cara kerja:* mengubah nama, email, dan kata sandi (wajib memasukkan kata sandi lama), serta menghubungkan Telegram untuk notifikasi.

### B. Dashboard dan Bantuan

**B1. Beranda per Peran**
- *Kegunaan:* pengguna langsung tahu apa yang perlu dikerjakan.
- *Cara kerja:* isi beranda berbeda per peran. Operator melihat progres alur kerja dan langkah berikutnya; Pimpinan melihat jumlah aset yang menunggu keputusan dan aset prioritas; Admin melihat ringkasan sistem dan aktivitas terbaru.

**B2. Panduan Singkat di Setiap Halaman**
- *Kegunaan:* membantu pengguna yang belum terbiasa dengan aplikasi.
- *Cara kerja:* di bagian atas setiap halaman tampil penjelasan singkat, langkah-langkah, dan arti istilah (misalnya MOORA, skor relatif, UEB, NUP). Panduan dapat disembunyikan dan pilihannya diingat di perangkat tersebut.

**B3. Perbesar Teks**
- *Kegunaan:* memudahkan pengguna senior membaca layar.
- *Cara kerja:* tombol "AA" di pojok kanan atas memperbesar seluruh tampilan sekitar 12,5%. Pilihan diingat di perangkat tersebut, termasuk di halaman masuk.

**B4. Notifikasi**
- *Kegunaan:* pengguna tahu ada pekerjaan baru tanpa harus memeriksa aplikasi terus-menerus.
- *Cara kerja:* kejadian penting (import selesai, peringkat siap ditinjau, periode difinalisasi, periode dibuka kembali, cadangan gagal) dikirim ke ikon lonceng. Menekan notifikasi langsung membuka halaman terkait. Bila diaktifkan Admin, salinannya juga dikirim ke **Telegram** dan/atau **email**.

### C. Master Data

**C1. Data Aset**
- *Kegunaan:* menyimpan data BMD gedung dan bangunan sebagai bahan penilaian.
- *Cara kerja:* form dua tahap: (1) data BMD (kode barang, NUP, nama, kategori, jumlah, luas, tanggal perolehan, harga, nilai perolehan, umur ekonomis, akumulasi penyusutan, sisa UEB, nilai buku, lokasi); (2) status dan foto (kamera atau galeri, maksimal 5 foto). Aset dengan sisa umur ekonomis ≤ 3 tahun ditandai "perlu perhatian". Aset yang sudah tercatat di periode final tidak dapat dihapus.

**C2. Kompres Foto Otomatis**
- *Kegunaan:* foto HP berukuran besar (3–8 MB) tidak memperlambat aplikasi dan tidak cepat memenuhi server.
- *Cara kerja:* setiap foto yang diunggah diperkecil otomatis (sisi terpanjang maksimal 1600 piksel, format JPEG) dan arah putarnya diluruskan sesuai posisi kamera. Ukuran akhir umumnya hanya ratusan KB.

**C3. Import Data BMD dari Excel**
- *Kegunaan:* memasukkan puluhan hingga ratusan aset sekaligus dari data BMD yang sudah ada.
- *Cara kerja:* operator mengunduh template, mengisinya, lalu mengunggah berkas. Sistem menampilkan **pratinjau**: baris valid (akan disimpan) dan baris bermasalah beserta alasannya (misalnya tanggal tidak valid atau NUP ganda). Data baru tersimpan setelah operator menekan Simpan.

**C4. Export Data Aset**
- *Kegunaan:* mengambil seluruh data aset dalam format Excel untuk arsip atau laporan lain.
- *Cara kerja:* sekali tekan, sistem membuat berkas `.xlsx`. Isi sel diamankan agar tidak dapat dijalankan sebagai rumus berbahaya di Excel.

**C5. Kategori Aset**
- *Kegunaan:* mengelompokkan aset (misalnya Gedung Kantor, Posyandu, PAUD).
- *Cara kerja:* tambah dan ubah kategori; kategori yang masih dipakai aset tidak dapat dihapus.

**C6. Kriteria Penilaian dan Rubrik**
- *Kegunaan:* menentukan hal yang dinilai dan seberapa penting masing-masing.
- *Cara kerja:* setiap kriteria memiliki kode, nama, tipe (*benefit*/*cost*), bobot dalam persen, rentang skala, dan **rubrik** (arti setiap nilai 1–5) yang tampil saat operator mengisi nilai agar penilaian seragam. Meteran bobot menunjukkan apakah total sudah tepat 100%. Kriteria yang pernah dipakai pada periode final dikunci tipe dan skalanya.

### D. Penilaian

**D1. Periode Penilaian**
- *Kegunaan:* memisahkan setiap kegiatan penilaian (misalnya per tahun) agar hasilnya bisa ditelusuri dan dibandingkan.
- *Cara kerja:* operator membuat periode dengan memilih cakupan aset: semua aset aktif, per kategori, hanya yang perlu perhatian, atau pilih manual. Halaman periode menampilkan progres pengisian nilai dan daftar aset yang belum lengkap.

**D2. Kelola Aset dalam Periode**
- *Kegunaan:* memperbaiki daftar aset tanpa harus membuat ulang periode.
- *Cara kerja:* selama periode belum final, operator dapat **menambah** aset (termasuk aset yang baru didata) atau **mengeluarkan** aset dari periode. Saat menambah aset baru di menu Data Aset, ada pilihan "Ikut periode berjalan".

**D3. Hapus Periode**
- *Kegunaan:* membatalkan periode yang keliru dibuat.
- *Cara kerja:* periode berstatus Draft atau Dinilai dapat dihapus setelah konfirmasi. Data aset dan fotonya tetap aman. Periode yang sudah dihitung atau final tidak dapat dihapus.

**D4. Input Nilai Aset**
- *Kegunaan:* merekam hasil pemeriksaan lapangan.
- *Cara kerja:* untuk setiap aset, operator memilih nilai 1–5 pada setiap kriteria (arti nilai tampil di bawahnya), menulis deskripsi kondisi, dan mengambil foto. Tombol "Simpan & Lanjut" langsung membuka aset berikutnya yang belum lengkap. Status periode berubah otomatis sesuai kelengkapan.

**D5. Hitung MOORA**
- *Kegunaan:* menghasilkan peringkat dan rekomendasi secara objektif.
- *Cara kerja:* tombol aktif bila bobot tepat 100% dan semua nilai lengkap (bila belum, alasannya ditampilkan). Sistem menghitung, menyimpan bobot dan ambang yang dipakai saat itu (*snapshot*) agar hasil lama tidak berubah walau pengaturan diganti kemudian, lalu memberi tahu Pimpinan.

**D6. Peringkat dan Detail Perhitungan**
- *Kegunaan:* menampilkan hasil dan membuktikan perhitungannya.
- *Cara kerja:* halaman peringkat menampilkan urutan aset, skor relatif, Yi, rekomendasi, dan peringatan biaya tinggi, dengan filter dan pencarian. Halaman **Detail Perhitungan** menampilkan langkah lengkap: matriks, normalisasi, terbobot, dan hasil akhir (bahan pemeriksaan dan laporan).

**D7. Keputusan Pimpinan**
- *Kegunaan:* menetapkan tindakan resmi untuk setiap aset.
- *Cara kerja:* Pimpinan memilih salah satu dari tiga tombol besar (Pertahankan, Perbaiki, Hapus); pilihan yang sesuai rekomendasi ditandai. Nilai kriteria, kondisi, dan foto aset tampil di halaman yang sama sebagai dasar keputusan.

**D8. Finalisasi dan Buka Kembali**
- *Kegunaan:* mengunci hasil yang sudah sah, tetapi tetap memberi jalan koreksi yang tercatat.
- *Cara kerja:* finalisasi hanya bisa dilakukan bila semua aset sudah diputuskan. Setelah final, data terkunci dan status aset diperbarui. Bila perlu koreksi, operator membuka kembali periode dengan alasan tertulis; alasan ini tersimpan, tercatat di audit log, dan diberitahukan ke Pimpinan.

**D9. Riwayat Penilaian per Aset**
- *Kegunaan:* melihat perjalanan sebuah aset dari tahun ke tahun.
- *Cara kerja:* di halaman detail aset tampil daftar setiap periode yang memuat aset tersebut: peringkat, skor, rekomendasi sistem, dan keputusan Pimpinan.

**D10. Perbandingan Antar Periode**
- *Kegunaan:* melihat tren, misalnya aset mana yang kondisinya membaik atau memburuk dibanding tahun lalu.
- *Cara kerja:* pilih dua periode yang sudah dihitung. Sistem menampilkan untuk setiap aset: peringkat lama dan baru, perubahan peringkat (naik/turun/tetap), selisih skor, dan perubahan rekomendasi (ditandai), serta ringkasan jumlah aset yang naik, turun, baru, atau tidak dinilai.

### E. Laporan

**E1. Laporan PDF dan Excel**
- *Kegunaan:* dokumen resmi hasil penilaian untuk arsip dan pengambilan keputusan.
- *Cara kerja:* pilih periode, jenis laporan (**Peringkat**, **Keputusan**, atau **Lengkap**), dan format (PDF/Excel). PDF berukuran A4 dengan kop instansi dan kolom tanda tangan penandatangan yang diatur Admin. Setiap laporan tercatat di riwayat cetak dan dapat diunduh ulang.

**E2. QR Verifikasi Keaslian Laporan**
- *Kegunaan:* memastikan dokumen yang beredar benar-benar dicetak dari SIKASET dan tidak diubah.
- *Cara kerja:* setiap laporan PDF memuat kode QR dan kode verifikasi di samping tanda tangan. Siapa pun yang memindai QR akan membuka halaman verifikasi publik yang menampilkan jenis laporan, periode, pencetak, dan tanggal cetak (tanpa tautan unduh). Pemeriksa juga dapat mengunggah berkas yang diterimanya; sistem mencocokkan "sidik jari" berkas (SHA-256) dan memberi tahu apakah isinya sama persis dengan arsip.

### F. Administrasi Sistem

**F1. Manajemen Pengguna**
- *Kegunaan:* mengatur siapa saja yang boleh memakai aplikasi dan perannya.
- *Cara kerja:* Admin menambah akun, mengubah data dan peran, menonaktifkan atau mengaktifkan akun, serta mereset kata sandi. Kata sandi hasil reset tampil satu kali dan pengguna wajib menggantinya saat masuk. Admin tidak dapat menonaktifkan akunnya sendiri.

**F2. Pengaturan**
- *Kegunaan:* menyesuaikan aturan tanpa mengubah program.
- *Cara kerja:* Admin mengatur ambang rekomendasi (berlaku untuk perhitungan berikutnya, dengan pratinjau rentang warna), identitas instansi dan penandatangan laporan, batas logout otomatis (15/30/60/120 menit atau nonaktif), dan masa simpan cadangan (7/14/30/90 hari). Setiap perubahan tercatat di audit log.

**F3. Integrasi (Telegram dan Email)**
- *Kegunaan:* mengirim notifikasi ke luar aplikasi karena Pimpinan jarang membuka aplikasi.
- *Cara kerja:* Admin membuat bot di Telegram (melalui @BotFather, gratis), menempelkan tokennya, dan mengisi pengaturan SMTP bila ingin email. Token dan kata sandi disimpan **terenkripsi** di database, tidak pernah ditampilkan ulang, dan token bot diperiksa keabsahannya sebelum disimpan. Tersedia tombol uji kirim. Setiap pengguna menghubungkan Telegramnya sendiri dari menu Profil dengan menekan "Hubungkan Telegram" lalu "Start" di Telegram, tanpa perlu mengetik nomor atau ID.

**F4. Cadangan Data (Backup)**
- *Kegunaan:* data aset BMD tidak boleh hilang bila server rusak.
- *Cara kerja:* setiap hari pukul 01.00 sistem membuat satu berkas ZIP berisi salinan database, seluruh foto aset, dan berkas laporan. Admin juga dapat membuat cadangan kapan saja, mengunduhnya untuk disimpan di luar server, atau menghapusnya. Cadangan yang lebih tua dari masa simpan dihapus otomatis (3 cadangan terbaru selalu disimpan). Bila pencadangan gagal, Admin mendapat notifikasi. Petunjuk pemulihan disertakan di dalam berkas ZIP.

**F5. Audit Log**
- *Kegunaan:* transparansi dan akuntabilitas: siapa mengubah apa dan kapan.
- *Cara kerja:* setiap tambah, ubah, dan hapus data (aset, kategori, kriteria, periode, nilai, keputusan, pengguna, pengaturan) tercatat otomatis lengkap dengan nilai sebelum dan sesudah. Admin dapat memfilter berdasarkan pengguna, modul, dan tanggal. Nilai rahasia (kata sandi, token) tidak pernah dicatat.

### G. Kenyamanan Penggunaan

**G1. Tampilan Responsif (HP dan Komputer)**
- *Kegunaan:* aplikasi dapat dipakai langsung di lapangan memakai HP.
- *Cara kerja:* di HP, daftar ditampilkan sebagai kartu dengan menu bawah (bottom navigation); di komputer, sebagai tabel dengan menu samping. Tombol berukuran cukup besar untuk disentuh.

**G2. "Tampilkan Lebih Banyak" (Pengganti Halaman Bernomor)**
- *Kegunaan:* menelusuri daftar panjang dengan nyaman, terutama di HP.
- *Cara kerja:* daftar menampilkan sejumlah data terlebih dahulu beserta keterangan "Menampilkan X dari Y". Tombol "Tampilkan N lagi" menambah data dan layar langsung menuju data yang baru dimuat. Pencarian dan filter tetap berlaku.

**G3. Konfirmasi dan Pesan Hasil**
- *Kegunaan:* mencegah salah tekan pada tindakan penting dan memberi kepastian hasil.
- *Cara kerja:* tindakan seperti hapus, hitung MOORA, finalisasi, buka kembali, dan logout selalu meminta konfirmasi. Setelah selesai, muncul pesan singkat yang menjelaskan hasilnya (misalnya "12 aset diimpor").

**G4. Halaman Galat Berbahasa Indonesia**
- *Kegunaan:* pengguna tidak bingung saat terjadi kesalahan.
- *Cara kerja:* halaman tidak ditemukan, akses ditolak, sesi habis, terlalu banyak permintaan, dan galat server ditampilkan dengan penjelasan dan tombol kembali.

---

## 8. Matriks Hak Akses

| Menu / Fitur | Admin | Operator | Pimpinan |
|---|---|---|---|
| Beranda, Profil, Notifikasi | Ya | Ya | Ya |
| Data Aset | Lihat | Kelola + import/export | Lihat |
| Kategori Aset | – | Kelola | – |
| Kriteria | Lihat | Kelola | Lihat |
| Periode, input nilai, hitung MOORA, buka kembali | Lihat | Kelola | Lihat |
| Peringkat, detail perhitungan, perbandingan periode | Lihat | Lihat | Lihat |
| Keputusan dan finalisasi | – | – | Kelola |
| Laporan | Unduh | Buat dan unduh | Buat dan unduh |
| Pengguna, Pengaturan, Integrasi, Cadangan, Audit Log | Kelola | – | – |
| Verifikasi dokumen (QR) | Publik, tanpa login | | |

---

## 9. Keamanan dan Perlindungan Data

- **Kata sandi** disimpan dalam bentuk hash (tidak dapat dibaca), wajib diganti bila bersifat sementara.
- **Pembatasan percobaan**: login dibatasi 5 kali per menit; proses berat (import, export, hitung, laporan, cadangan) dibatasi agar server tidak terbebani.
- **Anti-bot** Cloudflare Turnstile di halaman masuk (aktif setelah kunci dipasang di server).
- **Hak akses per peran** diperiksa di setiap halaman dan aksi.
- **Header keamanan dan Content-Security-Policy**: browser hanya menjalankan skrip dari aplikasi sendiri, sehingga lebih tahan terhadap serangan sisipan skrip.
- **Tidak bergantung CDN**: seluruh tampilan (CSS, JavaScript, huruf) disimpan di server sendiri; aplikasi tetap tampil normal walau koneksi ke layanan luar terputus.
- **Rahasia terenkripsi**: token Telegram dan kata sandi SMTP dienkripsi di database.
- **Integritas data**: data periode final terkunci, foto bukti periode final tidak dapat dihapus, keputusan yang berbeda dari rekomendasi wajib beralasan, dan semua perubahan tercatat di audit log.
- **Cadangan harian** otomatis beserta petunjuk pemulihan.
- **Keamanan berkas Excel**: isi sel yang menyerupai rumus diamankan saat export.

---

## 10. Istilah Penting

| Istilah | Arti |
|---|---|
| BMD | Barang Milik Daerah |
| NUP | Nomor Urut Pendaftaran; pembeda aset dengan kode barang yang sama |
| UEB | Umur Ekonomis Bangunan (tahun); sisa UEB = sisa tahun pemakaian yang wajar |
| Kriteria benefit | Kriteria yang makin tinggi nilainya makin baik (misalnya fungsi aset) |
| Kriteria cost | Kriteria yang makin tinggi nilainya makin buruk (misalnya biaya pemeliharaan) |
| Bobot | Tingkat kepentingan kriteria; total seluruh kriteria aktif = 100% |
| Rubrik | Penjelasan arti setiap nilai 1–5 pada sebuah kriteria |
| MOORA | Metode yang membandingkan semua aset sekaligus berdasarkan kriteria dan bobotnya |
| Yi | Nilai optimasi MOORA (benefit dikurangi cost); bisa bernilai negatif |
| Skor relatif | Yi yang diubah ke skala 0–100; 100 = aset terbaik pada periode itu |
| Rekomendasi | Saran sistem dari skor relatif: Pertahankan, Perbaiki, atau Hapus |
| Periode | Satu kegiatan penilaian (misalnya tahunan) |
| Finalisasi | Penguncian hasil periode setelah semua aset diputuskan |
| Snapshot | Salinan bobot dan ambang saat perhitungan, agar hasil lama tidak berubah |

---

## 11. Informasi Teknis Singkat

| Komponen | Keterangan |
|---|---|
| Kerangka aplikasi | Laravel 13 (PHP 8.3), pola MVC |
| Basis data | MySQL 8 |
| Web server | Nginx + PHP-FPM |
| Tampilan | Blade, Tailwind CSS, DaisyUI, Alpine.js, SweetAlert2 (seluruhnya disimpan di server sendiri) |
| Laporan | PDF (dompdf) dan Excel (Laravel Excel), QR Code (chillerlan/php-qrcode) |
| Audit | spatie/laravel-activitylog |
| Notifikasi | Dalam aplikasi, Telegram Bot API, email SMTP |
| Pengujian | Pest (181 test otomatis, termasuk pencocokan hasil MOORA dengan perhitungan manual), Laravel Pint, Larastan |
| Integrasi berkelanjutan | GitHub Actions memeriksa kode, test, dan berkas tampilan setiap kali ada perubahan |

**Kebutuhan operasional server:**

- Penjadwal tugas aktif (cron di Linux atau Task Scheduler di Windows) agar cadangan harian berjalan.
- Alamat aplikasi (`APP_URL`) diisi dengan domain resmi sebelum mencetak laporan, karena QR verifikasi memakai alamat tersebut.
- Kunci Cloudflare Turnstile diisi di berkas `.env` server bila ingin mengaktifkan anti-bot di halaman masuk.
- Pengaturan Telegram dan email diisi Admin melalui menu Integrasi.
