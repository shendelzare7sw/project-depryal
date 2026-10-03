# 06 — Dokumen Pengujian (Bahan Bab IV)

Pengujian dilakukan dengan **black-box testing** yang diotomasi menggunakan Pest (feature test HTTP) dan **unit test** perhitungan MOORA.
Jalankan: `php artisan test` → **134 test (693 asersi), seluruhnya lulus** (lihat ringkasan di bawah). Kolom *Bukti* menunjuk file test.

## A. Perbandingan perhitungan manual vs sistem (golden dataset)

Bobot: Fungsi 0,40 (benefit) · Efektivitas 0,35 (benefit) · Biaya Pemeliharaan 0,25 (cost).

| Aset | Nilai (F, E, B) | Yi manual | Yi sistem | Skor relatif manual | Skor sistem | Rank manual | Rank sistem | Rekomendasi sistem |
|---|---|---|---|---|---|---|---|---|
| A1 | 4, 3, 2 | 0,2693 | 0,2693 | 62,65 | 62,65 | 2 | 2 | Perbaiki |
| A2 | 2, 2, 4 | 0,0215 | 0,0215 | 0,00 | 0,00 | 4 | 4 | Hapus |
| A3 | 5, 4, 1 | 0,4170 | 0,4170 | 100,00 | 100,00 | 1 | 1 | Pertahankan |
| A4 | 3, 5, 3 | 0,2645 | 0,2645 | 61,43 | 61,43 | 3 | 3 | Perbaiki |

Penyebut √Σx²: Fungsi 7,348469 · Efektivitas 7,348469 · Biaya 5,477226 — identik (toleransi 1e-4).
Bukti: `tests/Unit/MooraCalculatorTest.php`, `tests/Feature/PeringkatKeputusanTest.php` (halaman Detail Perhitungan menampilkan nilai yang sama).

## B. Tabel uji black-box

| ID | Modul | Skenario | Input | Hasil diharapkan | Hasil aktual | Bukti |
|---|---|---|---|---|---|---|
| TC-01 | Login | Login username benar | `operator` / `password` | Masuk ke Beranda | Sesuai | AuthTest |
| TC-02 | Login | Login dengan email | email + password benar | Masuk ke Beranda | Sesuai | AuthTest |
| TC-03 | Login | Kata sandi salah | password salah | Pesan "Username/email atau kata sandi salah" | Sesuai | AuthTest |
| TC-04 | Login | Akun nonaktif | akun is_active = false | Ditolak, pesan akun dinonaktifkan | Sesuai | AuthTest, AdminTest |
| TC-05 | Login | Brute force | 6× gagal | Percobaan ke-6 diblokir sementara | Sesuai | HardeningTest |
| TC-06 | Hak akses | Operator/Pimpinan buka menu Pengguna | GET /pengguna | 403 Akses ditolak | Sesuai | RoleAccessTest, AdminTest |
| TC-07 | Aset | Tambah aset + foto | Data BMD lengkap + 2 foto | Aset & foto tersimpan | Sesuai | AsetTest |
| TC-08 | Aset | Kode barang + NUP ganda | kombinasi sudah ada | Pesan "sudah terdaftar" | Sesuai | AsetTest |
| TC-09 | Aset | Sisa UEB > UEB / foto > 4 MB | sisa 60, UEB 50; foto 5 MB | Ditolak dengan pesan | Sesuai | AsetTest |
| TC-10 | Aset | Cari & filter | kata kunci, kategori, status | Daftar tersaring | Sesuai | AsetTest |
| TC-11 | Aset | Hapus aset di periode final | aset sudah dinilai final | Ditolak | Sesuai | AsetTest |
| TC-12 | Aset | Admin/Pimpinan ubah aset | POST/PUT/DELETE | 403 | Sesuai | AsetTest |
| TC-13 | Import BMD | Pratinjau baris valid & error | 5 baris (1 valid, 4 error) | 1 valid, 4 error berikut pesan per baris; belum tersimpan | Sesuai | AsetImportTest |
| TC-14 | Import BMD | Format Indonesia | `1.250.000.000,00`, `15/01/2005` | Terbaca 1250000000 & 2005-01-15 | Sesuai | AsetImportTest |
| TC-15 | Import BMD | Upsert & kategori otomatis | kode+NUP sama, kategori baru | Data diperbarui, kategori dibuat | Sesuai | AsetImportTest |
| TC-16 | Import BMD | 96 baris | berkas 96 baris | Tersimpan < 10 detik | Sesuai | AsetImportTest |
| TC-17 | Import BMD | Berkas bukan Excel / > 12 MB | PDF; 12,1 MB | Ditolak | Sesuai | AsetImportTest |
| TC-18 | Export | Formula injection | nama `=HYPERLINK(...)` | Ditulis sebagai teks | Sesuai | HardeningTest |
| TC-19 | Kriteria | Meteran bobot | total 90% / 100% | Merah / hijau | Sesuai | KriteriaTest |
| TC-20 | Kriteria | Bobot persen | 25,5% | Tersimpan 0,255 + rubrik 1–5 | Sesuai | KriteriaTest |
| TC-21 | Kriteria | Kriteria dipakai periode final | ubah tipe/skala, hapus | Tipe & skala terkunci, hapus ditolak | Sesuai | KriteriaTest |
| TC-22 | Periode | Buat periode (semua/kategori/perhatian/pilih) | cakupan berbeda | Aset sesuai cakupan, status Draft | Sesuai | PeriodePenilaianTest |
| TC-23 | Periode | Dua periode aktif | buat saat ada periode aktif | Ditolak | Sesuai | PeriodePenilaianTest |
| TC-24 | Penilaian | Nilai sebagian / lengkap / dikosongkan | input nilai | Draft → Dinilai → Draft | Sesuai | PeriodePenilaianTest |
| TC-25 | Penilaian | Nilai di luar skala | nilai 7 (skala 1–5) | Ditolak | Sesuai | PeriodePenilaianTest |
| TC-26 | Hitung MOORA | Bobot ≠ 100% / nilai belum lengkap | — | Tombol nonaktif + alasan | Sesuai | PeriodePenilaianTest, CalculatePeriodeTest |
| TC-27 | Hitung MOORA | Hitung berhasil | nilai lengkap | Status Dihitung, peringkat tersimpan, Pimpinan dinotifikasi | Sesuai | PeriodePenilaianTest |
| TC-28 | Hitung MOORA | Ubah nilai setelah dihitung | ubah nilai | Hasil & keputusan dihapus, perlu hitung ulang | Sesuai | PeriodePenilaianTest |
| TC-29 | Peringkat | Peringatan biaya tinggi (aturan #8) | Fungsi ≥ 4, Biaya = 5 | Badge "Berfungsi tetapi biaya tinggi" | Sesuai | PeringkatKeputusanTest |
| TC-30 | Keputusan | Sama dengan rekomendasi | tanpa catatan | Tersimpan, lanjut ke aset berikutnya | Sesuai | PeringkatKeputusanTest |
| TC-31 | Keputusan | Berbeda dari rekomendasi | catatan < 10 karakter | Ditolak; ≥ 10 karakter diterima | Sesuai | PeringkatKeputusanTest |
| TC-32 | Finalisasi | Masih ada aset belum diputuskan | — | Ditolak | Sesuai | PeringkatKeputusanTest |
| TC-33 | Finalisasi | Semua diputuskan | — | Status Final, status aset diperbarui, Operator dinotifikasi | Sesuai | PeringkatKeputusanTest |
| TC-34 | Buka kembali | Tanpa alasan / alasan valid | alasan < 10 / ≥ 10 karakter | Ditolak / status Dihitung + notifikasi | Sesuai | PeriodePenilaianTest |
| TC-35 | Laporan | PDF & Excel tiap jenis | periode dihitung | Berkas terbentuk, riwayat tercatat, dapat diunduh ulang | Sesuai | LaporanDashboardTest |
| TC-36 | Laporan | PDF 96 aset | 96 baris | Multi-halaman, header tabel berulang | Sesuai | LaporanDashboardTest |
| TC-37 | Laporan | Admin membuat laporan | POST | 403 (hanya unduh) | Sesuai | LaporanDashboardTest |
| TC-38 | Dashboard | Tanpa N+1 | 3 vs 30 aset | Jumlah query sama | Sesuai | LaporanDashboardTest |
| TC-39 | Pengguna | Tambah, ubah peran, nonaktifkan, reset password | data akun | Berhasil; password baru tampil sekali | Sesuai | AdminTest |
| TC-40 | Pengguna | Admin menonaktifkan diri sendiri | — | Ditolak | Sesuai | AdminTest |
| TC-41 | Pengaturan | Ambang pertahankan ≤ perbaiki | 30 & 40 | Ditolak | Sesuai | AdminTest |
| TC-42 | Pengaturan | Ambang baru dipakai perhitungan berikutnya | 60 & 20 | A1 (62,65) → Pertahankan | Sesuai | AdminTest |
| TC-43 | Audit | Perubahan data tercatat | ubah nama aset | Log berisi pelaku, sebelum & sesudah | Sesuai | AdminTest |
| TC-44 | Profil | Ganti kata sandi | password lama salah / benar | Ditolak / berhasil | Sesuai | AdminTest |
| TC-45 | Notifikasi | Buka, tandai dibaca, milik orang lain | — | Diarahkan; terbaca; 404 | Sesuai | NotifikasiTest |
| TC-46 | Alur lengkap | Import → … → buka kembali (3 role) | skenario utuh | Semua tahap berhasil | Sesuai | AlurLengkapTest |

## C. Ringkasan
- Seluruh 46 kasus uji black-box **sesuai** dengan hasil yang diharapkan.
- Perhitungan MOORA sistem **identik** dengan perhitungan manual pada golden dataset.
- Kualitas kode: Laravel Pint (PSR-12) lulus, Larastan level 5 tanpa galat.
