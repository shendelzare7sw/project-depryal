# 01 — Product Spec (Role, Aturan Bisnis, MOORA, Alur UX)

## 1. Role & Hak Akses
| Role (enum `UserRole`) | Siapa | Tugas utama |
|---|---|---|
| `admin` | Administrator sistem | Kelola pengguna, pengaturan ambang, audit log |
| `operator` | **Pengurus Barang** | Kelola aset (import, foto, kondisi), kriteria & bobot, periode, input nilai, hitung MOORA, cetak laporan |
| `pimpinan` | Camat/Sekcam/Kasubag (Pimpinan di laporan) | Lihat peringkat & detail, tentukan tindakan, finalisasi periode, cetak laporan |

*Role extensible* (backlog): `auditor` read-only untuk verifikasi BPKD.

### Matriks izin (C=create, R=read, U=update, D=delete, —=tidak boleh)
| Modul | admin | operator | pimpinan |
|---|---|---|---|
| Dashboard | R (statistik sistem) | R (progres kerja) | R (ringkasan keputusan) |
| Pengguna | CRUD | — | — |
| Pengaturan (ambang) | RU | R | — |
| Audit log | R | — | — |
| Kategori & Aset | R | CRUD + Import/Export | R |
| Foto & kondisi aset | R | CRUD | R |
| Kriteria, bobot, rubrik | R | CRUD | R |
| Periode penilaian | R | CRU (+ buka/tutup) | R |
| Nilai kriteria per aset | R | CRU | R |
| Hitung MOORA | — | ✔ | — |
| Peringkat & Detail Perhitungan | R | R | R |
| Keputusan (tindakan) | R | R | **CRU** + Finalisasi |
| Laporan (PDF/Excel) | R | ✔ buat | ✔ buat |
| Profil & ganti password | semua | semua | semua |

Implementasi: middleware `role:admin,operator,pimpinan` di route + `Policy` per model untuk aksi sensitif (mis. `PeriodePolicy::calculate`).

## 2. Siklus Hidup Periode (state machine, enum `StatusPeriode`)
```
draft ──(semua nilai lengkap)──▶ dinilai ──(Hitung MOORA)──▶ dihitung ──(semua aset diputuskan + Finalisasi)──▶ final
  ▲                                  ▲                             │
  └──────────── Admin edit ──────────┴──── Hitung ulang (Admin) ───┘
final ──(Admin "Buka kembali" + alasan wajib, tercatat audit)──▶ dihitung
```
- `draft`: periode dibuat, aset dipilih, nilai belum lengkap.
- `dinilai`: seluruh aset di periode memiliki nilai untuk SEMUA kriteria aktif.
- `dihitung`: hasil MOORA tersimpan; Pimpinan boleh memutuskan. Mengubah nilai/hitung ulang menghapus keputusan "draft" lama (dengan konfirmasi SweetAlert).
- `final`: **semua data periode terkunci** (nilai, hasil, keputusan). Status `aset.status` diperbarui.
- Hanya **satu** periode aktif (non-final) pada satu waktu (sederhana & mencegah kebingungan). 

## 3. Aturan Bisnis
1. **Bobot:** jumlah bobot kriteria aktif harus = 1,00 (toleransi 0,0001). Tombol "Hitung MOORA" dinonaktifkan + pesan jika tidak terpenuhi. Input bobot dalam persen (0–100) di UI, disimpan desimal (0–1).
2. **Kriteria:** `tipe` ∈ {benefit, cost}; `skala_min`/`skala_maks` (default 1–5). Min 2 kriteria aktif untuk menghitung. Kriteria yang pernah dipakai periode final → hanya bisa dinonaktifkan.
3. **Nilai:** wajib angka dalam rentang skala kriteria. Satu nilai per (periode, aset, kriteria).
4. **Aset dalam periode:** default semua aset berstatus `aktif`; bisa difilter kategori / "perlu perhatian". Minimal 2 aset untuk MOORA.
5. **Rekomendasi sistem** dari `skor_relatif` (0–100): `≥ ambang_pertahankan` → Pertahankan; `< ambang_perbaiki` → Hapus; sisanya Perbaiki. Default 66,67 dan 33,33 (di tabel `pengaturan`).
6. **Keputusan Pimpinan** bebas berbeda dari rekomendasi; jika berbeda **catatan wajib diisi** (min 10 karakter).
7. **Finalisasi** hanya jika setiap aset di periode sudah punya keputusan.
8. **Peringatan ⚠:** Fungsi ≥4 dan Biaya Perawatan = skala maks → badge "Berfungsi tetapi biaya tinggi — pertimbangkan penghapusan" (aturan wawancara 3.1.1). Hanya informasi.
9. **Audit:** setiap create/update/delete pada Aset, Kriteria, Periode, Nilai, Keputusan, User dicatat (siapa, kapan, sebelum/sesudah).
10. **Hapus aset** = soft delete; ditolak jika aset sudah muncul di periode final.

## 4. Algoritma MOORA (kontrak `MooraCalculator`)
Input: `alternatives[]` (id), `criteria[]` (id, tipe, bobot), `matrix[alt_id][crit_id]` (float).
1. Penyebut per kriteria: `d_j = sqrt( Σ_i x_ij² )`. Jika `d_j = 0` → semua `x*_ij = 0`.
2. Normalisasi: `x*_ij = x_ij / d_j`.
3. Terbobot: `v_ij = w_j · x*_ij`.
4. Optimasi: `Yi = Σ_{j∈benefit} v_ij − Σ_{j∈cost} v_ij`.
5. `skor_relatif = (Yi − Ymin)/(Ymax − Ymin) × 100`; jika `Ymax = Ymin` → 100 untuk semua.
6. Ranking: urut `Yi` menurun; nilai sama → ranking sama (*competition ranking* 1,2,2,4).
Output per alternatif: `yi`, `skor_relatif`, `ranking`, dan `detail` = `{normalized:{crit:val}, weighted:{crit:val}, benefit_sum, cost_sum}`.
Presisi: simpan 6 desimal; bulatkan hanya saat tampil (4 desimal untuk Yi, 2 untuk skor relatif).

## 5. Alur UX (Mobile-First)
Prinsip: **satu tugas per layar**, tombol aksi utama selalu terjangkau jempol (sticky bottom bar di mobile), status/progres selalu terlihat, bahasa non-teknis.

### 5.1 Navigasi
- Mobile: **top app bar** (judul halaman + avatar) + **drawer** (DaisyUI `drawer`) + **bottom navigation** 4 item sesuai role.
- Desktop (`lg:`): sidebar permanen, tanpa bottom nav.
- Menu per role:
  - **Admin:** Dashboard · Pengguna · Pengaturan · Audit Log · Data Aset (R) · Kriteria (R) · Periode (R) · Hasil MOORA (R) · Laporan (R)
  - **Operator:** Dashboard · Aset · Kriteria · Penilaian (Periode) · Hasil MOORA · Laporan
  - **Pimpinan:** Dashboard · Hasil MOORA (Peringkat) · Keputusan · Laporan

### 5.2 Alur Operator (Pengurus Barang) — "Dari data sampai hasil"
1. **Login** → Dashboard Operator: kartu *Total Aset*, *Perlu Perhatian*, *Periode Aktif + progres nilai (x/96)*, tombol utama kontekstual ("Lanjutkan Penilaian").
2. **Aset** → daftar (pencarian, filter kategori/status, kartu di mobile). Tombol **Import BMD** → unggah Excel → *pratinjau* (baris valid/error) → konfirmasi → selesai. **Tambah/Ubah Aset**: form bertahap (Data BMD → Kondisi & Foto) dengan kamera langsung (`accept="image/*" capture="environment"`).
3. **Kriteria** → daftar kriteria + **meter total bobot** (hijau jika 100%, merah jika tidak). Edit bobot & rubrik skala (1–5 + keterangan).
4. **Penilaian** → *Buat Periode* (nama, tanggal, pilih aset/semua) → halaman periode berisi progres + daftar aset berstatus *Belum/Lengkap*.
5. **Input Nilai** (per aset, satu layar): foto kondisi, deskripsi kerusakan, lalu per kriteria **pilihan skala (radio-button besar 1–5)** dengan rubrik muncul di bawah pilihan. Tombol *Simpan & Lanjut ke aset berikutnya* (sticky). Filter "Belum dinilai".
6. **Hitung MOORA** (aktif bila 100% lengkap & bobot 100%) → SweetAlert konfirmasi → proses → diarahkan ke **Peringkat**.
7. **Detail Perhitungan** (tab: Matriks · Normalisasi · Terbobot · Hasil) untuk verifikasi manual.
8. **Laporan** → pilih periode & jenis (Peringkat / Keputusan / Lengkap) → PDF/Excel + riwayat cetak.

### 5.3 Alur Pimpinan — "Lihat, putuskan, selesai"
1. **Login** → Dashboard: banner *"N aset menunggu keputusan"*, ringkasan distribusi rekomendasi (bar proporsi bertumpuk dari DaisyUI `progress`/`stat`, **tanpa library chart**), 5 aset prioritas.
2. **Peringkat** → daftar urut (kartu di mobile): ranking, nama, skor, badge rekomendasi, badge keputusan. Ketuk → **Detail Aset** (foto, deskripsi, nilai tiap kriteria, skor).
3. **Tentukan Tindakan** (di Detail Aset): 3 tombol besar *Pertahankan / Perbaiki / Hapus* (rekomendasi sistem ditandai), kolom catatan (wajib bila beda dari rekomendasi), Simpan → toast sukses → otomatis ke aset berikutnya yang belum diputuskan.
4. **Finalisasi Periode** (aktif bila semua diputuskan) → SweetAlert (peringatan data akan terkunci) → status `final`.
5. **Laporan** → sama seperti Operator.

### 5.4 Alur Admin (Administrator Sistem)
Pengguna (tambah, ubah role, reset password, nonaktifkan) · Pengaturan (ambang rekomendasi, nama instansi, nama penandatangan laporan) · Audit Log (filter user/modul/tanggal).

### 5.5 Autentikasi
Halaman login (username + password, ingat saya) · ganti password di Profil · pembatasan percobaan (throttle 5/menit) · logout via `<x-confirm-form>` · akun nonaktif tidak bisa login. Tidak ada registrasi publik, tidak ada "lupa password via email" (reset oleh Admin).

### 5.6 Pola UX standar
- Tabel `≥md`, kartu `<md` (komponen `<x-ui.responsive-list>` — lihat arsitektur).
- Status = badge DaisyUI berwarna konsisten: Pertahankan `success`, Perbaiki `warning`, Hapus `error`, Belum `ghost`.
- *Empty state* ramah (ikon + teks + tombol aksi) di setiap daftar kosong.
- Loading: tombol submit otomatis `loading` (Alpine `x-data="{busy:false}" @submit="busy=true"`).
- Error validasi tampil di bawah field (`<x-form.field>`), plus toast ringkas.
- Konfirmasi SweetAlert **hanya** untuk aksi destruktif/penting: logout, hapus, hitung/hitung ulang, finalisasi, buka kembali periode, import.

## 6. Golden Dataset (untuk Pest test & perbandingan manual Bab IV)
Bobot: Fungsi 0,40 (benefit) · Efektivitas 0,35 (benefit) · Biaya 0,25 (cost).

| Aset | Fungsi | Efektivitas | Biaya |
|---|---|---|---|
| A1 | 4 | 3 | 2 |
| A2 | 2 | 2 | 4 |
| A3 | 5 | 4 | 1 |
| A4 | 3 | 5 | 3 |

Penyebut: Fungsi √54 = 7,348469 · Efektivitas √54 = 7,348469 · Biaya √30 = 5,477226.

| Aset | x* F | x* E | x* B | v F | v E | v B | **Yi** | Skor relatif | Rank |
|---|---|---|---|---|---|---|---|---|---|
| A1 | 0,5443 | 0,4082 | 0,3651 | 0,2177 | 0,1429 | 0,0913 | **0,2693** | 62,65 | 2 |
| A2 | 0,2722 | 0,2722 | 0,7303 | 0,1089 | 0,0953 | 0,1826 | **0,0215** | 0,00 | 4 |
| A3 | 0,6804 | 0,5443 | 0,1826 | 0,2722 | 0,1905 | 0,0456 | **0,4170** | 100,00 | 1 |
| A4 | 0,4082 | 0,6804 | 0,5477 | 0,1633 | 0,2381 | 0,1369 | **0,2645** | 61,43 | 3 |

Test wajib: hasil `MooraCalculator` cocok dengan tabel ini (toleransi 1e-4). Tambahan test: penyebut nol, satu alternatif, nilai sama (ranking kembar), bobot tidak 100% (ditolak di Service, bukan di Calculator).

## 7. Inventaris Halaman
| Route name | URL | Role | Catatan |
|---|---|---|---|
| `login` | `/login` | guest | username+password |
| `dashboard` | `/dashboard` | semua | konten beda per role |
| `aset.index/create/show/edit` | `/aset…` | operator (CRUD), admin & pimpinan (R) | + `aset.import`, `aset.export` |
| `kategori-aset.*` | `/kategori-aset` | operator | CRUD sederhana (modal-less, halaman biasa) |
| `kriteria.*` | `/kriteria` | operator (CRUD), lainnya R | meter bobot, rubrik |
| `periode.index/create/show` | `/periode…` | operator, R lainnya | `periode.hitung`, `periode.finalisasi`, `periode.buka-kembali` |
| `penilaian.edit/update` | `/periode/{periode}/penilaian/{aset}` | operator | form nilai + foto |
| `peringkat.index/show` | `/periode/{periode}/peringkat` | semua | `peringkat.detail-perhitungan` |
| `keputusan.edit/update` | `/periode/{periode}/keputusan/{aset}` | pimpinan | |
| `laporan.index/create/download` | `/laporan…` | operator, pimpinan, admin | riwayat cetak |
| `pengguna.*` | `/pengguna` | admin | |
| `pengaturan.edit/update` | `/pengaturan` | admin | |
| `audit-log.index` | `/audit-log` | admin | |
| `profil.edit/update` | `/profil` | semua | ganti password |
