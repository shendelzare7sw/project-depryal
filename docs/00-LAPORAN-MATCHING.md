# 00 — Pencocokan Laporan (Bab I–III) ↔ Sistem

Sumber: Bab I (Pendahuluan), Bab II (Landasan Teori), Bab III (Analisa & Perancangan).
Status: ✅ dipakai apa adanya · 🔧 diperbaiki/diperluas · ➕ ditambahkan karena laporan belum memuat.

## A. Yang dipertahankan dari laporan
| # | Isi laporan | Implementasi |
|---|---|---|
| 1 | Judul: SPK Penilaian Kelayakan Aset MOORA, Laravel, Kec. Batuceper | ✅ Nama aplikasi **SIKASET** (nama ini muncul di Bab II 2.2) |
| 2 | 3 kriteria: Fungsi Barang (benefit, paling menentukan), Efektivitas Penggunaan (benefit), Biaya Perawatan (cost) | ✅ Seeder default; tetap dinamis (CRUD kriteria) |
| 3 | MOORA: matriks keputusan → normalisasi akar kuadrat → bobot → Yi = Σbenefit − Σcost → ranking | ✅ `MooraCalculator` |
| 4 | 7 tabel: User, Aset, Kriteria, NilaiKriteriaAset, HasilMOORA, Keputusan, Laporan | ✅ dipertahankan (+ tabel tambahan di bawah) |
| 5 | 2 aktor: Admin (Pengurus Barang) & Pimpinan, 9 use case | ✅ semua ada |
| 6 | Tindakan: Pertahankan / Perbaiki / Hapus + catatan | ✅ `TindakanAset` enum |
| 7 | Laporan PDF + riwayat cetak | ✅ |
| 8 | Data BMD Gedung & Bangunan 2026: 96 aset, 11 kolom, 6 kategori, sisa UEB | ✅ diimpor dari Excel |
| 9 | Pengujian: fungsional + bandingkan hasil dengan perhitungan manual | ✅ Pest test dengan *golden dataset* (lihat `01-PRODUCT-SPEC.md` §6) |
| 10 | Waterfall, MVC, MySQL | ✅ |

## B. Ketidakcocokan & perbaikan (penting untuk konsistensi laporan Bab III–IV)
| # | Temuan di laporan | Masalah | Keputusan untuk sistem |
|---|---|---|---|
| B1 | UI "Bootstrap 5" (3.2.2.5) | Bertentangan dengan stack (Tailwind + Alpine) | 🔧 **Tailwind + DaisyUI + Alpine**. *Revisi teks Bab III 3.2.2.5 & Bab II.* |
| B2 | Bab II 2.5.4 "Laragon/Apache" | Server sebenarnya Nginx | 🔧 Nginx + PHP-FPM. *Revisi Bab II 2.5.4.* |
| B3 | Login pakai username; logout "menghapus token" | Aplikasi web = **session**, bukan token | 🔧 Login `username` + session guard. *Revisi sequence/activity logout.* |
| B4 | Tambah kriteria ditolak jika total bobot ≠ 100% | Secara logika mustahil: menambah kriteria satu per satu tidak pernah langsung 100% | 🔧 Bobot boleh disimpan sebagai draft; **indikator total bobot** realtime; validasi 100% dilakukan saat **mengaktifkan set kriteria / menjalankan MOORA** (tombol Hitung dikunci jika ≠ 100%). *Revisi sequence & activity "Kelola Kriteria".* |
| B5 | Tabel `Aset` hanya 9 atribut | Analisa data menyebut **11 kolom BMD** (jumlah, luas, tgl perolehan, harga satuan, UEB, akumulasi penyusutan, dst.) + 6 kategori | 🔧 Perluas `aset`; ➕ tabel `kategori_aset` |
| B6 | Tidak ada fitur **Import** BMD | Bab 3.1.2.d menyebut data BMD "dapat diimpor langsung" | ➕ Import Excel/CSV + template unduhan + pratinjau & laporan error |
| B7 | Laporan memfilter "periode" tetapi tidak ada entitas periode | Nilai kriteria, hasil, keputusan tidak bisa dibedakan antar waktu; tidak bisa ditelusuri ulang (padahal ini masalah utama 3.1.1) | ➕ Tabel **`periode_penilaian`** (siklus: draft → dinilai → dihitung → final) |
| B8 | `NilaiKriteriaAset` tanpa periode | Nilai tahun ini menimpa tahun lalu | 🔧 tambah `periode_id`, unik (`periode_id`,`aset_id`,`kriteria_id`) |
| B9 | `HasilMOORA` hanya skor+ranking | Tidak bisa membuktikan perhitungan / bobot saat itu | 🔧 simpan `skor_yi`, `skor_relatif`, `ranking`, `rekomendasi`, `detail` (JSON matriks normalisasi & terbobot) + **snapshot bobot** di periode |
| B10 | Rekomendasi Pertahankan/Perbaiki/Hapus disebut, tetapi **aturan ambang tidak ada** | Agent tidak tahu cara memetakan skor → rekomendasi | ➕ Ambang konfigurable di tabel `pengaturan` (default 66,67 / 33,33 dari skor relatif) + keputusan akhir tetap manual |
| B11 | Foto kondisi: 1 kolom `foto` | Kondisi fisik lazimnya butuh beberapa foto | 🔧 tabel `aset_foto` (multi-foto, caption) |
| B12 | Role hanya Admin & Pimpinan; tidak ada manajemen user | Siapa yang membuat akun? | ➕ Role **Super Admin** (kelola user, pengaturan, audit log). Admin = Pengurus Barang. Pimpinan. |
| B13 | Tidak ada dashboard/ringkasan "aset perlu perhatian" untuk Admin | Pimpinan saja yang punya ringkasan (Gbr 3.26) | 🔧 Dashboard per role; indikator awal "perlu perhatian" dari `sisa_ueb ≤ 3` (7+8 = 15 aset ≈ 15,6% di data BMD) |
| B14 | Hapus/ubah kriteria yang sudah dipakai | Merusak hasil lama | ➕ Kriteria yang sudah dipakai periode final → tidak bisa dihapus, hanya dinonaktifkan; bobot lama aman di snapshot |
| B15 | Hasil MOORA bisa negatif (cost dikurangi) tetapi contoh laporan 0,72 | Bingung membaca skor | 🔧 Tampilkan **Yi (mentah)** dan **skor relatif 0–100** (min-max) berdampingan |
| B16 | Pengajuan penghapusan ke BPKD (sampai 1 tahun, SK Walikota) di luar sistem | Di luar batasan masalah | ➕ (ringan) `aset.status` ikut berubah saat keputusan final: `aktif`/`dalam_perbaikan`/`diusulkan_hapus`. Pelacakan SK = backlog fase 2 |
| B17 | Kuesioner/User Response (Bab II 2.6.3) | Bukan fitur aplikasi | ✅ tidak diimplementasikan di app (pakai Google Form di luar sistem) |
| B18 | Kriteria "skala nilai" tidak didefinisikan | Penilaian tetap subjektif tanpa rubrik | ➕ Tabel `kriteria_skala` (rubrik 1–5 per kriteria) tampil sebagai petunjuk saat input nilai |

## C. Fitur tambahan (di atas laporan, tetap sejalan dengan tujuan)
Dashboard per role · Import/Export Excel · Multi-foto · Rubrik skala · Periode & finalisasi (kunci data) · Halaman **Detail Perhitungan MOORA** (tabel langkah demi langkah, untuk Bab IV) · Audit log · Profil & ganti password · Pengaturan ambang · Ekspor PDF & Excel · Dark mode (bawaan DaisyUI, opsional).

## D. Pertanyaan terbuka (konfirmasi ke Pengurus Barang; default sudah ditetapkan)
1. Bobot final tiga kriteria? **Default seeder:** Fungsi 0,40 · Efektivitas 0,35 · Biaya 0,25 (contoh dari laporan).
2. Skala nilai? **Default:** 1–5 untuk semua kriteria (Biaya: 5 = sangat mahal).
3. Apakah aset "masih berfungsi tapi biaya sangat tinggi" harus otomatis direkomendasikan Hapus (3.1.1)? **Default:** tidak otomatis, hanya badge peringatan ⚠ bila Fungsi ≥4 dan Biaya = 5.
4. Siapa Pimpinan (Camat / Sekcam / Kasubag Umum)? **Default:** satu akun `pimpinan`.
