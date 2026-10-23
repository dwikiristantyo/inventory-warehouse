# Inventory Warehouse

Aplikasi web untuk mengelola perusahaan, gudang, barang, persediaan, hak akses pengguna, laporan, dan permintaan barang. Panel operasional memakai Filament di `/admin`; form permintaan barang tersedia untuk pemohon tanpa login.

## Daftar Isi

- [Fitur](#fitur)
- [Teknologi](#teknologi)
- [Persyaratan](#persyaratan)
- [Instalasi Lokal](#instalasi-lokal)
- [Konfigurasi](#konfigurasi)
- [Alur Penggunaan](#alur-penggunaan)
- [Struktur Proyek](#struktur-proyek)
- [Pengujian](#pengujian)
- [Operasional dan Keamanan](#operasional-dan-keamanan)

## Fitur

### Master Data

- **Company**: informasi perusahaan dan alias yang digunakan pada identitas gudang.
- **Warehouse**: data gudang dan relasinya ke perusahaan.
- **Category**: kategori barang dan pengaturan akses kategori per User Group.
- **Item**: kode, deskripsi, kategori, harga, status aktif, UOM 1, UOM 2, dan faktor konversi.
- **User**: akun login, status aktif, grup pengguna, akses perusahaan, dan akses gudang.
- **User Group**: izin per menu berupa `can_view`, `can_add`, `can_edit`, dan `can_delete`.

### Transaksi Persediaan

- **Goods In (`IN`)** untuk barang masuk.
- **Goods Out (`OUT`)** untuk barang keluar.
- **Adjustment (`ADJ`)** untuk koreksi persediaan.
- **Lock Period** untuk mengunci transaksi berdasarkan gudang, bulan, dan tahun.
- Setiap transaksi memiliki header (nomor, tipe, tanggal, gudang, remark, status, dan pembuat) serta satu atau beberapa detail item dan kuantitas.
- Status transaksi: `A` aktif, `P` posted, dan `X` tidak aktif/dihapus. Baris berstatus `X` dikecualikan dari perhitungan laporan.
- List Goods In, Goods Out, dan Adjustment secara default diurutkan berdasarkan tanggal transaksi terbaru.

### Laporan

- **Egg Stock Report by Item** menampilkan saldo awal dan mutasi item per tanggal.
- **Egg Stock Report By Warehouse** merangkum saldo per item pada gudang dan periode yang dipilih.
- **Stock Report by Item** menampilkan mutasi item per gudang, termasuk remark transaksi.
- **Stock Report by Warehouse** menyajikan ringkasan stok per gudang.
- **Daily Egg Matrix Report** menyajikan matriks harian sesuai filter laporan.
- Beberapa laporan menyediakan ekspor Excel.

Perhitungan stok menggunakan kuantitas UOM 1: barang masuk menambah saldo, barang keluar mengurangi saldo, dan adjustment menambah atau mengurangi sesuai kuantitas yang dicatat. Report by Item dan Report by Warehouse hanya menampilkan item yang memiliki transaksi valid di gudang terpilih. Saldo awal tetap mencakup transaksi sebelum rentang tanggal laporan.

### Permintaan Barang dan Acceptance

- Form publik: `GET /request-items`; pemohon tidak perlu login.
- Data pemohon: email, nama, department, gudang, dan detail satu atau beberapa item.
- Kode/nama barang, UOM utama, serta saldo saat ini diambil dari data aplikasi. Saldo dihitung per gudang sampai hari ini dari transaksi `IN`, `OUT`, dan `ADJ` yang tidak berstatus `X`.
- Form lookup saldo dibatasi 60 request per menit per IP; pengiriman permintaan dibatasi 5 request per menit per IP.
- Permintaan baru berstatus `pending` dan muncul pada menu **Acceptance** di panel admin.
- Approver adalah Super Admin (grup dengan `usergroupid = 1`) atau pengguna yang memiliki izin **Add** pada menu Goods Out.
- **Accept** membuat Goods Out aktif pada tanggal persetujuan. Remark otomatis berisi `Nama - Department`.
- Pembuatan transaksi dan perubahan status permintaan dilakukan dalam satu transaksi database. Request yang sudah diproses tidak dapat diproses lagi.
- **Reject** menyimpan alasan penolakan dan tidak membuat transaksi stok.
- Email permintaan baru ditujukan kepada approver; email hasil accept/reject ditujukan kepada pemohon.

## Teknologi

| Area | Teknologi |
| --- | --- |
| Bahasa server | PHP 8.2+ |
| Framework aplikasi | Laravel 12 |
| Panel admin | Filament 5 |
| Komponen interaktif | Livewire |
| Database dan akses data | PostgreSQL pada environment operasional; Eloquent ORM |
| Tampilan server | Blade |
| Aset frontend | Vite 7, Tailwind CSS 4, JavaScript ES modules, Axios |
| Ekspor Excel | Maatwebsite Laravel Excel 3.1 |
| Pustaka PDF | `barryvdh/laravel-dompdf` 3.1 |
| Pengujian | PHPUnit 11 dan Laravel test runner |

Batas versi dependensi dapat dilihat pada `composer.json` dan `package.json`. Konfigurasi Laravel menyertakan driver PostgreSQL, MySQL/MariaDB, SQLite, dan SQL Server; PostgreSQL direkomendasikan untuk instalasi yang mengikuti environment aplikasi. `phpunit.xml` menjalankan feature test menggunakan SQLite in-memory.

## Persyaratan

- PHP 8.2 atau lebih baru dan Composer.
- Ekstensi PDO untuk database yang digunakan, misalnya `pdo_pgsql` atau `pdo_sqlite`.
- Node.js dan npm untuk memasang serta membangun aset frontend.
- Server database dan database kosong yang dapat diakses akun aplikasi.
- Untuk email sungguhan: server SMTP dan kredensial yang valid.

## Instalasi Lokal

### 1. Pasang dependensi

```bash
composer install
npm install
```

### 2. Siapkan environment

Linux/macOS:

```bash
cp .env.example .env
```

Windows PowerShell:

```powershell
Copy-Item .env.example .env
```

Buat application key:

```bash
php artisan key:generate
```

### 3. Siapkan database

Buat database terlebih dahulu, lalu atur koneksi di `.env` sesuai bagian [Konfigurasi](#konfigurasi). Jalankan migrasi:

```bash
php artisan migrate
```

### 4. Isi data menu

Seeder berikut membuat data menu yang dipakai pada pengaturan hak akses. Jalankan satu kali:

```bash
php artisan db:seed --class=MenuSeeder
```

### 5. Build aset dan jalankan server

```bash
npm run build
php artisan serve
```

Panel admin tersedia di `http://127.0.0.1:8000/admin`; form permintaan publik di `http://127.0.0.1:8000/request-items`.

Untuk mode pengembangan dengan hot reload, jalankan di terminal terpisah:

```bash
php artisan serve
npm run dev
```

Atau gunakan script Composer yang menjalankan server, queue listener, log viewer, dan Vite secara bersamaan:

```bash
composer dev
```

## Konfigurasi

### Aplikasi

```dotenv
APP_NAME="Inventory Warehouse"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
```

`APP_KEY` dihasilkan oleh `php artisan key:generate`. Untuk production, gunakan `APP_ENV=production`, `APP_DEBUG=false`, dan `APP_URL` dengan HTTPS. Setelah mengubah `.env` pada instalasi yang memakai config cache, jalankan `php artisan config:clear`.

### Database PostgreSQL

Buat database dan user di PostgreSQL, kemudian sesuaikan variabel berikut:

```dotenv
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=inventory_warehouse
DB_USERNAME=nama_user_database
DB_PASSWORD=password_database
DB_SSLMODE=prefer
```

Nama database, user, dan password di atas hanya contoh. Jangan menyimpan kredensial nyata di Git. Koneksi PostgreSQL memakai schema `public` sebagaimana konfigurasi `config/database.php`.

### Database SQLite

`.env.example` menggunakan SQLite sebagai default kerangka Laravel. Untuk development lokal, atur `DB_CONNECTION=sqlite` dan `DB_DATABASE` ke path file database yang tersedia. Feature test menggunakan database SQLite in-memory yang ditentukan di `phpunit.xml`, bukan database development.

### Email

Mailer default pada `.env.example` adalah `log`. Dengan konfigurasi ini email dicatat ke log Laravel dan tidak dikirim ke penerima sungguhan. Contoh konfigurasi SMTP:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_SCHEME=tls
MAIL_USERNAME=akun_smtp
MAIL_PASSWORD=password_smtp
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Inventory Warehouse"
```

Sesuaikan host, port, scheme, dan kredensial dengan penyedia email. Jangan commit password SMTP ke repository. Pengiriman email permintaan dilakukan langsung oleh aplikasi; saat SMTP belum dikonfigurasi, periksa `storage/logs/laravel.log`.

### Session, cache, dan queue

Nilai pada `.env.example` menggunakan database untuk session, cache, dan queue. Migrasi Laravel menyiapkan tabel yang diperlukan. Queue worker dapat dijalankan jika pekerjaan aplikasi kelak dikirim ke queue; email permintaan saat ini dikirim secara langsung.

## Alur Penggunaan

### Panel Admin

1. Login melalui `/admin`.
2. Siapkan company, warehouse, category, dan item.
3. Buat User Group dan tentukan hak akses menu serta kategori.
4. Buat pengguna dan kaitkan ke User Group, perusahaan, serta gudang yang sesuai.
5. Catat Goods In, Goods Out, atau Adjustment; gunakan Lock Period untuk menutup periode transaksi.
6. Tinjau laporan stok dan gunakan fitur ekspor bila tersedia.
7. Untuk permintaan publik, buka menu **Acceptance**, periksa pemohon, gudang, item, quantity, dan saldo snapshot, lalu pilih **Accept** atau **Reject**.

Pengecekan akses menggunakan User Group dan detail izin per menu. Implementasi tertentu memperlakukan `usergroupid = 1` sebagai grup dengan bypass akses; pastikan grup tersebut hanya diberikan kepada administrator tepercaya.

### Permintaan Publik

1. Pemohon membuka `/request-items` tanpa login.
2. Pemohon memasukkan email, nama, department, memilih gudang, lalu menambahkan item dan quantity.
3. UOM dan saldo ditampilkan berdasarkan gudang/item yang dipilih. Saldo ini adalah informasi saat formulir dimuat; server tetap menghitung ulang dan menyimpan snapshot saat submit.
4. Permintaan masuk ke Acceptance dengan status `pending`.
5. Approver menerima email pemberitahuan dan memproses permintaan dari panel admin.
6. Jika diterima, sistem membuat Goods Out; jika ditolak, sistem menyimpan alasan penolakan. Hasil dikirim ke email pemohon.

Persetujuan tidak menolak quantity yang melebihi saldo secara otomatis. Approver perlu meninjau saldo dan jumlah yang diminta sebelum menerima.

### Seeder Demo

`DatabaseSeeder` membuat akun demo `admin` dengan password `admin123`, beberapa grup, dan data contoh perusahaan, gudang, kategori, item, serta transaksi. Seeder ini ditujukan untuk demo/development dan bukan provisioning production. Jangan jalankan pada database berisi data operasional. Ganti atau hapus kredensial demo sebelum aplikasi digunakan secara nyata.

## Struktur Proyek

```text
app/
  Exports/                 Kelas ekspor laporan/mutasi
  Filament/Pages/          Dashboard dan halaman laporan
  Filament/Resources/      Resource master, transaksi, user, dan Acceptance
  Http/Controllers/        Controller HTTP dan form permintaan publik
  Mail/                    Template email status permintaan
  Models/                  Model Eloquent
  Providers/Filament/      Konfigurasi panel admin
  Services/                Perhitungan saldo dan alur persetujuan
bootstrap/                 Bootstrap aplikasi Laravel
config/                    Konfigurasi aplikasi, database, email, dan layanan
database/migrations/       Skema database dan perubahan skema
database/seeders/          Seeder menu dan data demo
resources/views/           Blade view, form publik, dan email
routes/web.php             Route aplikasi publik
storage/                   Log, cache, file, dan hasil runtime
tests/                     Unit test dan feature test
```

## Pengujian

Jalankan semua test:

```bash
php artisan test
```

Feature test permintaan barang mencakup tampilan publik, saldo snapshot, email, pembuatan Goods Out pada accept, dan tidak adanya transaksi pada reject. Untuk memeriksa migrasi dan route:

```bash
php artisan migrate:status
php artisan route:list
```

## Operasional dan Keamanan

- Sebelum deploy, buat backup database dan jalankan migrasi secara eksplisit: `php artisan migrate --force`.
- Migrasi sinkronisasi sequence menangani PostgreSQL. Alur approval juga menyelaraskan sequence header/detail sebelum pembuatan Goods Out untuk mengurangi bentrok setelah input ID manual.
- Jangan mengubah ID menu tanpa meninjau kode pemeriksaan izin yang menggunakan ID tersebut.
- Jangan publikasikan `.env`, kredensial database/SMTP, atau dump database operasional.
- Gunakan HTTPS, matikan debug di production, batasi akses database, pantau log, dan uji konfigurasi SMTP sebelum membuka form publik.
