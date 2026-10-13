# Proses Bisnis — KalaLuang

> Platform lowongan kerja dua sisi khusus *fresh graduate* (lulusan D3/D4/S1, usia ±21–25 th, lulus ≤ 2 tahun, belum punya pengalaman kerja formal). Masalah yang diselesaikan: asimetri informasi dan lowongan fiktif.

## 1. Aktor

| Aktor | Peran |
|---|---|
| **Pencari Kerja** (fresh graduate) | Daftar, isi profil (pendidikan, keahlian, portofolio, CV), cari & filter lowongan, simpan lowongan, melamar, melapor lowongan mencurigakan. **Gratis.** |
| **Perusahaan** (UKM/startup) | Daftar, unggah dokumen verifikasi, pasang lowongan (**Rp100.000 / lowongan / 30 hari**), kelola lowongan, lihat & ubah status pelamar. |
| **Admin** (pengelola KalaLuang) | Verifikasi perusahaan, kurasi lowongan (pastikan benar-benar entry-level), konfirmasi pembayaran, tinjau laporan & menurunkan lowongan bermasalah. |

## 2. Cakupan MVP

Fitur yang dibangun:

1. Registrasi & login dengan pilihan peran (pencari kerja / perusahaan)
2. Profil pencari kerja: pendidikan, keahlian, portofolio sederhana, unggah CV
3. Katalog lowongan entry-level (hanya lowongan yang menerima lulusan baru) dengan label **"Tanpa pengalaman"**
4. Pencarian + filter: **jenis pekerjaan** (penuh waktu, magang, paruh waktu), **bidang**, **lokasi**
5. Verifikasi perusahaan → lencana **"Terverifikasi"** di setiap lowongannya
6. Dasbor perusahaan: ringkasan, pasang lowongan, daftar pelamar, status verifikasi
7. Lapor lowongan → ditinjau admin → lowongan bisa diturunkan
8. Simpan lowongan

Di luar cakupan MVP (tidak dibangun): skill-matching/rekomendasi otomatis, program magang terstruktur, integrasi career center kampus, paket langganan, *featured listing*, payment gateway otomatis.

Halaman (dari prototipe): Beranda · Daftar/Masuk (pilih peran) · Katalog Lowongan · Detail Lowongan · Profil Pencari Kerja · Dasbor Perusahaan · Panel Admin. Tampilan **mobile-first**.

## 3. Proses Bisnis

### 3.1 Registrasi & Profil Pencari Kerja

1. Pengguna membuka beranda → klik Daftar → pilih peran **Pencari Kerja**.
2. Mengisi nama, email, password → akun dibuat (verifikasi email Laravel).
3. Melengkapi profil: no. HP, kota domisili, jenjang & institusi pendidikan, jurusan, tahun lulus, keahlian (multi-pilih), link portofolio, unggah CV (PDF, maks 2 MB).
4. Profil dianggap **lengkap** jika data pendidikan + CV sudah terisi. Profil lengkap menjadi **syarat untuk melamar**.

### 3.2 Registrasi & Verifikasi Perusahaan

1. Perusahaan mendaftar dengan peran **Perusahaan** → mengisi data akun.
2. Mengisi profil perusahaan: nama, bidang industri, kota, alamat, situs/medsos, deskripsi, logo.
3. Mengunggah dokumen legalitas (NIB wajib; akta/NPWP opsional) → status perusahaan **`pending`**.
4. Admin memeriksa dokumen secara manual:
   - Valid → status **`verified`**, lencana Terverifikasi aktif.
   - Tidak valid → status **`rejected`** + catatan alasan → perusahaan dapat memperbaiki dan mengajukan ulang (kembali `pending`).
5. **Hanya perusahaan `verified` yang boleh memasang lowongan.**

### 3.3 Pemasangan & Kurasi Lowongan

1. Perusahaan terverifikasi klik **Pasang Lowongan** → isi: judul, bidang, jenis pekerjaan, lokasi (atau remote), pendidikan minimal, deskripsi, persyaratan, cara melamar, rentang gaji (opsional), centang pernyataan *"Lowongan ini menerima lulusan baru tanpa pengalaman kerja"* (wajib).
2. Lowongan tersimpan berstatus **`pending_payment`**, sistem membuat tagihan **Rp100.000**.
3. Perusahaan transfer manual & unggah bukti bayar → pembayaran **`waiting_confirmation`**.
4. Admin mengonfirmasi pembayaran (**`paid`**) atau menolak (**`rejected`**, perusahaan unggah ulang).
5. Setelah dibayar, lowongan masuk **`under_review`** → admin melakukan **kurasi**: cek tidak mensyaratkan pengalaman, tidak meminta biaya dari pelamar, deskripsi jelas.
   - Lolos → **`active`**, `published_at = now()`, `expires_at = now() + 30 hari`.
   - Tidak lolos → **`rejected`** + catatan; perusahaan dapat merevisi lalu kembali ke `under_review` (tanpa bayar ulang).
6. Scheduler harian mengubah lowongan yang lewat `expires_at` menjadi **`expired`**. Perusahaan bisa memperpanjang (bayar lagi → +30 hari).
7. Perusahaan dapat menutup lowongan lebih awal → **`closed`**.

### 3.4 Pencarian & Lamaran

1. Pencari kerja (atau pengunjung tamu) membuka katalog → hanya lowongan **`active`** milik perusahaan **`verified`** yang tampil.
2. Mencari dengan kata kunci dan filter jenis pekerjaan / bidang / lokasi.
3. Membuka detail lowongan → bisa **Simpan lowongan** atau **Lamar Sekarang** (tamu diarahkan ke login).
4. Saat melamar: sistem cek profil lengkap & belum pernah melamar lowongan yang sama → lamaran dibuat dengan status **`new`** (Baru), CV disalin (snapshot) ke lamaran.
5. Perusahaan melihat pelamar di dasbor dan mengubah status: **`new` → `reviewed` (Diproses) → `accepted` (Diterima) / `rejected` (Ditolak)**.
6. Pencari kerja memantau status lamaran di halaman profil/riwayat lamaran. (Notifikasi: database notification Laravel.)

### 3.5 Pelaporan Lowongan

1. Pengguna login klik **Laporkan lowongan** di detail lowongan → pilih alasan (meminta biaya, perusahaan fiktif, mensyaratkan pengalaman, info menyesatkan, lainnya) + keterangan.
2. Laporan berstatus **`pending`**. Satu pengguna hanya bisa melapor satu kali per lowongan.
3. Admin meninjau:
   - Terbukti → laporan **`valid`**, lowongan diubah menjadi **`taken_down`** (hilang dari katalog). Jika pelanggaran berat, admin dapat mengubah perusahaan menjadi **`suspended`** (semua lowongannya turun).
   - Tidak terbukti → laporan **`dismissed`**.

## 4. Aturan Bisnis (Business Rules)

| Kode | Aturan |
|---|---|
| BR-01 | Pencari kerja gratis; pendapatan hanya dari biaya pasang lowongan Rp100.000 / 30 hari. |
| BR-02 | Perusahaan wajib `verified` sebelum memasang lowongan. |
| BR-03 | Lowongan hanya tampil jika status `active`, belum lewat `expires_at`, dan perusahaan `verified`. |
| BR-04 | Semua lowongan wajib entry-level (`requires_experience = false`) — ditegakkan di form dan kurasi admin. |
| BR-05 | Pelamar hanya bisa melamar 1x per lowongan (unique constraint). |
| BR-06 | Melamar wajib profil lengkap (pendidikan + CV). |
| BR-07 | Perusahaan resmi tidak boleh meminta biaya dari pelamar; pelanggaran = lowongan diturunkan. |
| BR-08 | Satu pengguna maksimal satu laporan per lowongan. |

## 5. Hak Akses

| Aksi | Tamu | Pencari Kerja | Perusahaan | Admin |
|---|:-:|:-:|:-:|:-:|
| Lihat katalog & detail | ✓ | ✓ | ✓ | ✓ |
| Simpan / lamar lowongan | | ✓ | | |
| Laporkan lowongan | | ✓ | ✓ | |
| Kelola profil perusahaan & dokumen | | | ✓ (milik sendiri) | |
| Pasang / edit / tutup lowongan | | | ✓ (verified, milik sendiri) | |
| Lihat & ubah status pelamar | | | ✓ (lowongan sendiri) | |
| Verifikasi perusahaan, konfirmasi bayar, kurasi, tinjau laporan | | | | ✓ |
