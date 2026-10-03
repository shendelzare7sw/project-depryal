# 07 — Alur Aplikasi SIKASET (per Role)

Panduan cara kerja aplikasi untuk tim penguji dan pengguna. Akun demo: `admin`, `operator`, `pimpinan` (kata sandi `password`).
Alur ini juga diuji otomatis di `tests/Feature/AlurLengkapTest.php`.

## Gambaran besar

```
OPERATOR                         PIMPINAN                          ADMIN
1. Data Aset / Import BMD  ──────────────────────────────────▶ 🔔 "Import data BMD selesai"
2. Kriteria (bobot = 100%)
3. Periode → Input Nilai
4. Hitung MOORA  ─────────────▶ 🔔 "Peringkat MOORA siap ditinjau"   🔔 (salinan)
                                5. Keputusan (tiap aset)
                                6. Finalisasi  ──────▶ 🔔 Operator & Admin "Periode difinalisasi"
7. Laporan PDF/Excel            7. Laporan PDF/Excel               Unduh laporan (lihat saja)
8. Buka kembali (bila koreksi) ▶ 🔔 "Periode dibuka kembali"       Audit Log mencatat semuanya
```

Status periode: **Draft** → (semua nilai lengkap) → **Dinilai** → (Hitung MOORA) → **Dihitung** → (semua aset diputuskan + Finalisasi) → **Final**.
Notifikasi muncul di ikon 🔔 kanan atas; klik untuk langsung membuka halaman terkait.

## 1. Operator (Pengurus Barang)

| Langkah | Menu | Yang dilakukan | Hasil / dampak ke role lain |
|---|---|---|---|
| 1 | **Data Aset** → *Tambah* | Form 2 tahap: Data BMD → Kondisi & Foto (kamera/galeri) | Aset tersimpan; badge ⚠ bila sisa UEB ≤ 3 |
| 1b | **Data Aset** → *Import BMD* | Unduh template → unggah Excel → periksa pratinjau (baris valid/error) → *Simpan* | Admin & operator lain mendapat 🔔 "Import data BMD selesai" |
| 1c | **Data Aset** → *Export* | Unduh seluruh aset (.xlsx) | — |
| 2 | **Kategori Aset** | Tambah/ubah kategori (hapus hanya bila belum dipakai) | — |
| 3 | **Kriteria** | Atur kriteria, tipe (benefit/cost), bobot % (meteran harus **tepat 100%**, hijau), rubrik skala 1–5 | Bobot dipakai saat Hitung MOORA |
| 4 | **Periode Penilaian** → *Buat Periode* | Nama, tanggal, cakupan aset (semua / per kategori / perlu perhatian / pilih manual) | Periode berstatus **Draft** (hanya boleh satu periode aktif) |
| 5 | **Periode** → *Input Nilai* per aset | Pilih skala 1–5 tiap kriteria (rubrik tampil), deskripsi kondisi, foto → *Simpan & Lanjut* | Status otomatis **Dinilai** bila semua lengkap |
| 6 | **Periode** → *Hitung MOORA* (aktif bila bobot 100% & nilai lengkap) | Konfirmasi SweetAlert | Status **Dihitung**; **Pimpinan & Admin mendapat 🔔 "Peringkat MOORA siap ditinjau"**; diarahkan ke halaman Peringkat |
| 7 | **Laporan** | Pilih periode, jenis (Peringkat/Keputusan/Lengkap), format (PDF/Excel) → *Buat & Unduh* | Tercatat di riwayat cetak |
| 8 | **Periode** (final) → *Buka Kembali* + alasan | Untuk koreksi data setelah final | Status kembali **Dihitung**; **Pimpinan & Admin mendapat 🔔 "Periode dibuka kembali"** |

Catatan: mengubah nilai pada periode yang sudah **Dihitung** akan menghapus hasil & keputusan (ada konfirmasi) — Hitung MOORA perlu dijalankan ulang.

## 2. Pimpinan (Camat)

| Langkah | Menu | Yang dilakukan | Hasil / dampak |
|---|---|---|---|
| 1 | 🔔 Notifikasi / **Beranda** | Klik "Peringkat MOORA siap ditinjau" atau tombol *Mulai Putuskan* | Beranda menampilkan jumlah aset menunggu, distribusi rekomendasi, 5 aset prioritas |
| 2 | **Peringkat Aset** | Lihat urutan skor, rekomendasi sistem, ⚠ "biaya tinggi"; *Detail Perhitungan* (Matriks · Normalisasi · Terbobot · Hasil) | Read-only |
| 3 | **Keputusan** | Daftar aset *Menunggu* & *Sudah diputuskan* → klik aset → pilih **Pertahankan / Perbaiki / Hapus** (rekomendasi ditandai) | Catatan **wajib** (min. 10 karakter) bila berbeda dari rekomendasi; otomatis lanjut ke aset berikutnya |
| 4 | **Keputusan** / **Peringkat** → *Finalisasi Periode* (aktif bila semua aset diputuskan) | Konfirmasi SweetAlert | Status **Final**, data terkunci; status aset berubah (Pertahankan→Aktif, Perbaiki→Dalam Perbaikan, Hapus→Diusulkan Hapus); **Operator & Admin mendapat 🔔 "Periode difinalisasi"** |
| 5 | **Laporan** | Cetak PDF/Excel | Kop instansi + tanda tangan penandatangan dari Pengaturan |

Menu **Keputusan** kosong (berisi panduan) selama belum ada periode berstatus *Dihitung*.

## 3. Admin (Administrator Sistem)

| Menu | Yang dilakukan |
|---|---|
| **Beranda** | Statistik sistem, pengguna per peran, ambang rekomendasi, aktivitas terbaru |
| **Pengguna** | Tambah akun, ubah peran, nonaktifkan/aktifkan, *Reset Password* (password acak tampil sekali). Tidak bisa menonaktifkan/menurunkan diri sendiri |
| **Pengaturan** | Ambang rekomendasi (Pertahankan ≥ X, Hapus < Y; berlaku untuk perhitungan berikutnya), nama & alamat instansi, penandatangan laporan |
| **Audit Log** | Jejak siapa mengubah apa & kapan (aset, kategori, kriteria, periode, nilai, keputusan, pengguna, pengaturan) + nilai sebelum/sesudah; filter pengguna/modul/tanggal |
| Data Aset, Kriteria, Periode, Hasil MOORA, Laporan | **Hanya melihat** (dan mengunduh laporan) |

## 4. Matriks akses ringkas

| Fitur | Admin | Operator | Pimpinan |
|---|---|---|---|
| Aset, Kategori, Kriteria, Import/Export | Lihat | **Kelola** | Lihat (aset & kriteria) |
| Periode, Input Nilai, Hitung MOORA, Buka kembali | Lihat | **Kelola** | Lihat |
| Peringkat & Detail Perhitungan | Lihat | Lihat | Lihat |
| Keputusan & Finalisasi | Lihat | Lihat | **Kelola** |
| Laporan | Unduh | Buat & unduh | Buat & unduh |
| Pengguna, Pengaturan, Audit Log | **Kelola** | — | — |
| Profil & ganti kata sandi, Notifikasi | ✔ | ✔ | ✔ |
