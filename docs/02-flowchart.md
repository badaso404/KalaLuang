# Flowchart — KalaLuang

Diagram ditulis dengan sintaks [Mermaid](https://mermaid.js.org/).

## 1. Alur Utama Pencari Kerja

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka Beranda KalaLuang]
    B --> C{Sudah punya akun?}
    C -- Belum --> D[Daftar, pilih peran Pencari Kerja]
    D --> E[Verifikasi email]
    E --> F[Lengkapi profil: pendidikan, keahlian, portofolio, CV]
    C -- Sudah --> G[Login]
    G --> H[Buka Katalog Lowongan]
    F --> H
    H --> I[Cari & filter: jenis, bidang, lokasi]
    I --> J[Buka detail lowongan]
    J --> K{Aksi?}
    K -- Simpan --> L[Lowongan masuk daftar tersimpan]
    L --> H
    K -- Laporkan --> M[Isi alasan laporan]
    M --> N[Laporan terkirim ke admin]
    N --> H
    K -- Lamar --> O{Profil lengkap?}
    O -- Tidak --> F
    O -- Ya --> P{Sudah pernah melamar?}
    P -- Ya --> Q[Tampilkan pesan: sudah melamar]
    Q --> H
    P -- Tidak --> R[Simpan lamaran status Baru + snapshot CV]
    R --> S[Pantau status lamaran]
    S --> T([Selesai])
```

## 2. Alur Registrasi & Verifikasi Perusahaan

```mermaid
flowchart TD
    A([Mulai]) --> B[Daftar, pilih peran Perusahaan]
    B --> C[Isi profil perusahaan]
    C --> D[Unggah dokumen legalitas NIB]
    D --> E[Status perusahaan: pending]
    E --> F[Admin periksa dokumen]
    F --> G{Dokumen valid?}
    G -- Ya --> H[Status: verified, lencana Terverifikasi aktif]
    H --> I([Perusahaan boleh pasang lowongan])
    G -- Tidak --> J[Status: rejected + catatan admin]
    J --> K[Perusahaan perbaiki dokumen]
    K --> D
```

## 3. Alur Pasang Lowongan, Pembayaran & Kurasi

```mermaid
flowchart TD
    A([Mulai]) --> B{Perusahaan verified?}
    B -- Tidak --> C[Arahkan ke halaman Status Verifikasi]
    C --> Z([Selesai])
    B -- Ya --> D[Isi form lowongan + centang pernyataan entry-level]
    D --> E[Lowongan: pending_payment, tagihan Rp100.000 dibuat]
    E --> F[Transfer & unggah bukti bayar]
    F --> G[Admin cek pembayaran]
    G --> H{Pembayaran valid?}
    H -- Tidak --> I[Pembayaran rejected]
    I --> F
    H -- Ya --> J[Pembayaran paid, lowongan under_review]
    J --> K[Admin kurasi lowongan]
    K --> L{Lolos kurasi entry-level?}
    L -- Tidak --> M[Lowongan rejected + catatan]
    M --> N[Perusahaan revisi lowongan]
    N --> J
    L -- Ya --> O[Lowongan active, tayang 30 hari]
    O --> P{Lewat 30 hari?}
    P -- Ya --> Q[Scheduler: status expired]
    Q --> R{Perpanjang?}
    R -- Ya --> E
    R -- Tidak --> Z
    P -- Belum --> S[Terima & kelola pelamar]
    S --> P
```

## 4. Alur Kelola Pelamar (Perusahaan)

```mermaid
flowchart TD
    A([Mulai]) --> B[Buka Dasbor > Pelamar]
    B --> C[Pilih pelamar status Baru]
    C --> D[Lihat profil & CV]
    D --> E[Ubah status: Diproses]
    E --> F{Keputusan}
    F -- Diterima --> G[Status: Diterima]
    F -- Ditolak --> H[Status: Ditolak]
    G --> I[Notifikasi ke pelamar]
    H --> I
    I --> J([Selesai])
```

## 5. Alur Penanganan Laporan (Admin)

```mermaid
flowchart TD
    A([Mulai]) --> B[Admin buka daftar laporan pending]
    B --> C[Tinjau lowongan & alasan laporan]
    C --> D{Laporan terbukti?}
    D -- Tidak --> E[Laporan dismissed]
    D -- Ya --> F[Laporan valid, lowongan taken_down]
    F --> G{Pelanggaran berat?}
    G -- Ya --> H[Perusahaan suspended, semua lowongan turun]
    G -- Tidak --> I[Kirim peringatan ke perusahaan]
    E --> J([Selesai])
    H --> J
    I --> J
```
