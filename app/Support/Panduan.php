<?php

declare(strict_types=1);

namespace App\Support;

use App\Enums\UserRole;

/**
 * Panduan singkat per halaman (bahasa awam) — ditampilkan otomatis oleh layout di atas konten.
 * Struktur: judul, isi (1–2 kalimat), langkah (opsional), istilah (opsional: istilah => arti).
 */
final class Panduan
{
    private const ISTILAH_MOORA = [
        'MOORA' => 'Metode penilaian yang membandingkan semua aset sekaligus berdasarkan beberapa kriteria dan bobotnya, lalu mengurutkannya dari yang paling layak.',
        'Skor relatif (0–100)' => 'Nilai akhir yang mudah dibaca: 100 = aset terbaik pada periode ini, 0 = aset terendah.',
        'Rekomendasi' => 'Saran otomatis dari skor: Pertahankan (skor tinggi), Perbaiki (sedang), Hapus (rendah). Hanya saran — keputusan tetap di tangan pimpinan.',
    ];

    /**
     * @return array{judul: string, isi: string, langkah?: list<string>|null, istilah?: array<string, string>}|null
     */
    public static function untuk(?string $route, UserRole $role): ?array
    {
        return match ($route) {
            'dashboard' => match ($role) {
                UserRole::Operator => ['judul' => 'Beranda Operator', 'isi' => 'Ringkasan pekerjaan Anda. Tombol kuning di atas selalu menunjukkan langkah berikutnya yang perlu dikerjakan.',
                    'langkah' => ['Lengkapi data aset dan kriteria.', 'Buat periode, lalu isi nilai setiap aset.', 'Jalankan Hitung MOORA — pimpinan otomatis diberi tahu.']],
                UserRole::Pimpinan => ['judul' => 'Beranda Pimpinan', 'isi' => 'Di sini terlihat berapa aset yang menunggu keputusan Anda. Tekan tombol "Mulai Putuskan" untuk memberi keputusan satu per satu.',
                    'istilah' => self::ISTILAH_MOORA],
                UserRole::Admin => ['judul' => 'Beranda Administrator', 'isi' => 'Ringkasan kondisi sistem: jumlah pengguna, aktivitas terbaru, dan ambang rekomendasi yang berlaku.'],
            },
            'aset.index' => ['judul' => 'Data Aset BMD', 'isi' => 'Daftar gedung & bangunan milik daerah yang akan dinilai. Tanda ⚠ kuning berarti sisa umur ekonomisnya tinggal 3 tahun atau kurang.',
                'langkah' => $role === UserRole::Operator ? ['Tambah aset satu per satu dengan tombol "Tambah Aset", atau', 'Gunakan "Import BMD" untuk memasukkan banyak aset sekaligus dari Excel.', 'Gunakan kotak pencarian & filter untuk menemukan aset.'] : null,
                'istilah' => ['NUP' => 'Nomor Urut Pendaftaran — pembeda aset yang kode barangnya sama.', 'UEB' => 'Umur Ekonomis Bangunan (tahun). Sisa UEB = sisa tahun pemakaian yang wajar.']],
            'aset.show' => ['judul' => 'Detail Aset', 'isi' => 'Seluruh data BMD dan foto aset. Foto yang bertanda gembok adalah bukti penilaian periode final dan tidak dapat dihapus.'],
            'aset.create', 'aset.edit' => ['judul' => 'Mengisi Data Aset', 'isi' => 'Isian dibagi dua langkah agar tidak membingungkan. Kolom bertanda * wajib diisi.',
                'langkah' => ['Langkah 1: salin data dari Kartu Inventaris Barang (kode, NUP, nilai, umur ekonomis).', 'Tekan "Lanjut", lalu pilih kondisi aset.', 'Ambil foto langsung dari kamera HP atau pilih dari galeri, lalu "Simpan Aset".']],
            'aset.import' => ['judul' => 'Import Data BMD dari Excel', 'isi' => 'Cara cepat memasukkan banyak aset. Data belum tersimpan sebelum Anda menekan tombol Simpan di halaman pratinjau.',
                'langkah' => ['Unduh template Excel, lalu isi sesuai kolomnya.', 'Unggah berkas tersebut.', 'Periksa pratinjau, lalu simpan.']],
            'aset.import.preview' => ['judul' => 'Pratinjau Import', 'isi' => 'Baris hijau akan disimpan. Baris merah memiliki kesalahan (alasannya tertulis) dan akan dilewati — perbaiki di Excel lalu unggah ulang bila perlu.'],
            'kategori-aset.index', 'kategori-aset.create', 'kategori-aset.edit' => ['judul' => 'Kategori Aset', 'isi' => 'Pengelompokan aset (mis. Gedung Kantor, Posyandu). Kategori yang masih dipakai aset tidak dapat dihapus.'],
            'kriteria.index' => ['judul' => 'Kriteria Penilaian', 'isi' => 'Hal-hal yang dinilai dari setiap aset beserta tingkat kepentingannya (bobot). Jumlah bobot kriteria aktif harus tepat 100% — meteran berwarna hijau.',
                'istilah' => ['Bobot' => 'Seberapa penting kriteria tersebut dibanding yang lain.', 'Benefit' => 'Makin tinggi nilainya makin baik (mis. fungsi aset).', 'Cost' => 'Makin tinggi nilainya makin buruk (mis. biaya pemeliharaan).', 'Rubrik' => 'Arti setiap angka 1–5, agar penilai memberi nilai yang seragam.']],
            'kriteria.create', 'kriteria.edit' => ['judul' => 'Mengatur Kriteria', 'isi' => 'Isi bobot dalam persen dan arti setiap skala 1–5 (rubrik). Penjelasan rubrik akan muncul saat operator mengisi nilai.'],
            'periode.index' => ['judul' => 'Periode Penilaian', 'isi' => 'Satu periode = satu kali kegiatan penilaian (mis. tahunan). Hanya boleh ada satu periode yang sedang berjalan.',
                'istilah' => ['Draft' => 'Nilai belum lengkap.', 'Dinilai' => 'Semua nilai sudah terisi, siap dihitung.', 'Dihitung' => 'Peringkat sudah ada, menunggu keputusan pimpinan.', 'Final' => 'Sudah diputuskan dan dikunci.']],
            'periode.create', 'periode.edit' => ['judul' => 'Membuat Periode', 'isi' => 'Beri nama periode dan pilih aset yang akan dinilai. Setelah dibuat, isi nilai setiap aset.'],
            'periode.show' => ['judul' => 'Halaman Periode', 'isi' => 'Pusat kerja penilaian. Isi nilai setiap aset sampai semuanya berstatus "Lengkap", lalu tekan "Hitung MOORA". Bila tombol belum aktif, alasannya tertulis di kotak kuning. Aset bisa ditambah ("Tambah Aset") atau dikeluarkan (ikon minus) selama periode belum final.'],
            'periode.aset.create' => ['judul' => 'Menambah Aset ke Periode', 'isi' => 'Centang aset yang terlewat atau baru didata, lalu tekan "Tambahkan ke Periode". Setelah itu isi nilai aset tersebut. Bila periode sudah dihitung, hitung ulang MOORA diperlukan.'],
            'penilaian.edit' => ['judul' => 'Mengisi Nilai Aset', 'isi' => 'Untuk setiap kriteria, pilih angka 1–5 yang paling sesuai dengan kondisi di lapangan. Arti angka yang dipilih muncul di bawahnya.',
                'langkah' => ['Pilih angka untuk setiap kriteria.', 'Tulis singkat kerusakan/kondisi dan ambil foto.', 'Tekan "Simpan & Lanjut" untuk berpindah ke aset berikutnya.']],
            'peringkat.index' => ['judul' => 'Hasil MOORA (Peringkat Aset)', 'isi' => 'Urutan aset dari yang paling layak dipertahankan hingga yang paling layak dihapus, hasil perhitungan metode MOORA.',
                'langkah' => $role === UserRole::Pimpinan ? ['Tinjau peringkat dan rekomendasinya.', 'Tekan "Mulai Putuskan" untuk memberi keputusan per aset.', 'Setelah semua diputuskan, tekan "Finalisasi Periode".'] : null,
                'istilah' => self::ISTILAH_MOORA + ['⚠ Biaya tinggi' => 'Aset masih berfungsi baik tetapi biaya pemeliharaannya sangat tinggi — layak dipertimbangkan untuk dihapus.']],
            'hasil.index' => ['judul' => 'Hasil MOORA', 'isi' => 'Hasil muncul setelah operator menjalankan Hitung MOORA pada sebuah periode.', 'istilah' => self::ISTILAH_MOORA],
            'peringkat.show' => ['judul' => 'Detail Hasil Aset', 'isi' => 'Nilai setiap kriteria yang membentuk skor aset ini, serta rekomendasi sistem dan keputusan pimpinan.', 'istilah' => self::ISTILAH_MOORA],
            'peringkat.detail' => ['judul' => 'Detail Perhitungan MOORA', 'isi' => 'Langkah hitung lengkap untuk pemeriksaan/laporan. Tidak perlu diubah — cukup dibaca.',
                'langkah' => ['Matriks: nilai asli setiap aset.', 'Normalisasi: nilai disetarakan agar kriteria berbeda bisa dibandingkan.', 'Terbobot: nilai dikalikan bobot kriteria.', 'Hasil: jumlah benefit dikurangi cost (Yi), lalu diurutkan.']],
            'keputusan.index' => ['judul' => 'Keputusan Pimpinan', 'isi' => 'Daftar aset yang menunggu keputusan Anda. Pilih aset, lalu tetapkan: Pertahankan, Perbaiki, atau Hapus.'],
            'keputusan.edit' => ['judul' => 'Menentukan Tindakan', 'isi' => 'Pilih salah satu dari tiga tombol besar. Pilihan bertanda "Rekomendasi" adalah saran sistem; Anda boleh memilih lain dengan menuliskan alasannya di Catatan.'],
            'laporan.index' => ['judul' => 'Laporan', 'isi' => $role === UserRole::Admin ? 'Daftar laporan yang sudah dicetak. Tekan ikon unduh untuk mengambil berkasnya.' : 'Cetak hasil penilaian dalam bentuk PDF (siap ditandatangani) atau Excel. Laporan yang pernah dibuat bisa diunduh lagi.',
                'istilah' => ['Peringkat' => 'Urutan aset dan rekomendasi sistem.', 'Keputusan' => 'Tindakan yang ditetapkan pimpinan.', 'Lengkap' => 'Kriteria, nilai, peringkat, dan keputusan sekaligus.']],
            'notifikasi.index' => ['judul' => 'Notifikasi', 'isi' => 'Pemberitahuan pekerjaan dari pengguna lain. Tekan sebuah notifikasi untuk langsung membuka halaman terkait.'],
            'pengguna.index', 'pengguna.create', 'pengguna.edit' => ['judul' => 'Manajemen Pengguna', 'isi' => 'Kelola akun yang boleh masuk. Bila pengguna lupa kata sandi, tekan ikon kunci (Reset) lalu sampaikan kata sandi sementara yang muncul. Pengguna otomatis diminta menggantinya saat masuk; pengguna yang punya email juga bisa memakai "Lupa kata sandi?" di halaman masuk.',
                'istilah' => ['Admin' => 'Mengelola akun, pengaturan, dan audit.', 'Operator' => 'Pengurus barang: data aset, penilaian, hitung MOORA.', 'Pimpinan' => 'Camat/Sekcam: memberi keputusan dan finalisasi.']],
            'pengaturan.index' => ['judul' => 'Pengaturan', 'isi' => 'Batas skor untuk rekomendasi dan identitas yang tercetak di laporan. Perubahan batas skor hanya berlaku untuk perhitungan berikutnya.'],
            'integrasi.index' => ['judul' => 'Integrasi', 'isi' => 'Hubungkan SIKASET dengan Telegram (gratis) dan email agar pengguna menerima pemberitahuan penting di luar aplikasi. Token & kata sandi disimpan terenkripsi.',
                'langkah' => ['Buat bot di @BotFather, tempel tokennya, lalu Simpan.', 'Isi SMTP bila ingin email (mis. Gmail + App Password).', 'Hubungkan Telegram Anda di menu Profil, lalu tekan "Uji Telegram".']],
            'audit-log.index' => ['judul' => 'Audit Log', 'isi' => 'Catatan otomatis setiap perubahan data: siapa, kapan, dan apa yang berubah. Tekan sebuah baris untuk melihat nilai sebelum dan sesudah.'],
            'profil.edit' => ['judul' => 'Profil Saya', 'isi' => 'Ubah nama atau kata sandi Anda. Untuk mengganti kata sandi, isi kata sandi lama terlebih dahulu. Bila tersedia, tekan "Hubungkan Telegram" agar pemberitahuan masuk ke Telegram Anda.'],
            default => null,
        };
    }
}
