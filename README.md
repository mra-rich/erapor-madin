# E-Rapor — Sistem Rapor Digital Madrasah Diniyah

Platform digitalisasi rapor dan pemantauan akademik santri untuk Madrasah Diniyah, dengan kurikulum kitab klasik. Dikembangkan oleh **MRA Digital Solution**.

## Stack Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8+ (native, mysqli) |
| Database | MySQL / MariaDB (mendukung SSL/Aiven) |
| Frontend | TailwindCSS v4, HTMX, Alpine.js, Flowbite |
| Icons | RemixIcon |
| Fonts | Outfit, Plus Jakarta Sans |
| PHP Library | SimpleXLSX / SimpleXLSXGen (import-export Excel) |
| Deployment | Vercel (vercel-php, region Singapore `sin1`) |

## Requirement

- PHP 8.0+ dengan ekstensi `mysqli`, `mbstring`
- MySQL 5.7+ / MariaDB 10.3+
- Composer (untuk install dependencies PHP)
- Node.js + npm (untuk build CSS)

## Setup Lokal

1. **Clone repo**
   ```bash
   git clone <repo-url> erapor
   cd erapor
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Konfigurasi environment** — salin template dan sesuaikan:
   ```bash
   cp .env.example .env
   ```
   Edit `.env` sesuai konfigurasi database lokal:
   ```
   DB_HOST=localhost
   DB_USER=root
   DB_PASS=
   DB_NAME=e_raport
   DB_PORT=3306
   DB_SSL=false
   APP_ENV=local
   ```

   `APP_ENV=local` memakai file session sementara jika tabel `sessions` belum dibuat. Set `APP_ENV=production` di Vercel; production wajib memakai tabel session database.


4. **Build CSS** (TailwindCSS)
   ```bash
   npm run build:css
   # atau watch mode:
   npm run watch:css
   ```

5. **Jalankan via PHP built-in server** (development):
   ```bash
   php -S localhost:8000 -t public router.php
   ```
   Buka `http://localhost:8000` di browser. `router.php` meneruskan clean URL aplikasi ke front controller dan tetap melayani CSS/aset statis dari `public/`.

### Tabel session lokal

Aplikasi menyimpan session di database. Jalankan sekali pada database lokal bila tabel `sessions` belum ada:

```sql
CREATE TABLE sessions (
  id VARCHAR(128) NOT NULL PRIMARY KEY,
  data MEDIUMTEXT NOT NULL,
  expires INT UNSIGNED NOT NULL,
  INDEX idx_sessions_expires (expires)
);
```

User database harus memiliki izin `SELECT`, `INSERT`, `UPDATE`, dan `DELETE` untuk tabel tersebut.

## Struktur Direktori

```
erapor/
├── app/
│   ├── Core/           # Framework mini (App, Autoloader, BaseController, BaseModel)
│   ├── Controllers/    # Controller OOP + legacy proses_*.php
│   ├── Models/         # Model OOP (ActiveRecord) + QueryBuilder
│   ├── Services/       # AuthService, CsrfService, LoggerService
│   └── Views/          # Template PHP (view + partial halaman)
├── config/
│   └── koneksi.php     # Konfigurasi database + session handler
├── public/
│   ├── index.php       # Front Controller / Router
│   ├── css/            # Build artifact TailwindCSS
│   ├── assets/         # Gambar, logo, dll
│   └── uploads/        # File upload pengguna
├── src/
│   └── input.css       # Source CSS Tailwind
├── dummy_data_gen/     # Generator data dummy untuk testing
├── vercel.json         # Konfigurasi deployment Vercel
├── .agents/AGENTS.md   # Aturan main & konvensi pengembangan
├── .env.example        # Template environment variables
└── composer.json       # Dependencies PHP
```

## Deploy ke Vercel

1. Push ke GitHub, hubungkan repo ke Vercel.
2. Set **Environment Variables** di Vercel dashboard:
   - `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, `DB_PORT`, `DB_SSL`
3. Region sudah dikonfigurasi ke `sin1` (Singapore) di `vercel.json`.
4. Vercel akan otomatis build menggunakan `vercel-php@0.9.0`.

## Arsitektur

Aplikasi menggunakan **front controller pattern** (`public/index.php`) dengan **dual dispatch**:

- **OOP Controller** — Modul yang sudah di-refaktor ke pola MVC (Auth, Dashboard, Guru CRUD) di-dispatch via route map ke Controller class.
- **Legacy Bridge** — Modul belum di-refaktor di-dispatch ke file procedural legacy, tapi hanya file yang terdaftar di `$legacyWhitelist` yang boleh dieksekusi. File dev/test/debug otomatis ditolak (404).

## Role Pengguna

| Role | Hak Akses |
|---|---|
| **Admin** | Full akses (CRUD semua data, pengaturan sistem) |
| **Kepala Madrasah** | Read-only (view laporan, dashboard) |
| **Wali Kelas** | Kelola santri, nilai, evaluasi wali, penilaian mapel |
| **Guru** | Penilaian mapel (mata pelajaran yang diampu) |

## Lisensi

Private — MRA Digital Solution
