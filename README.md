# PANTAU ATS - Pemantauan dan Pemutakhiran Data Anak Tidak Sekolah (Kabupaten Polewali Mandar)

Sistem Informasi Pemantauan, Pendataan, Verifikasi, dan Pemutakhiran **Anak Tidak Sekolah (PANTAU ATS)** khusus **Pemerintah Kabupaten Polewali Mandar** (Dinas Pendidikan Kabupaten Polewali Mandar), dibangun dengan framework **CodeIgniter 4**, antarmuka **Admin Panel Modern** (Compact / High-Density UI), serta terintegrasi penuh dengan **Pusat Export Excel (.xlsx)** dan **Dashboard Analitik**.

---

## 🌟 Fitur Utama

1. **Khusus Cakupan 16 Kecamatan Kabupaten Polewali Mandar**:
   - Seluruh 16 Kecamatan & Desa/Kelurahan se-Kabupaten Polewali Mandar terpetakan secara otomatis (*Allu, Anreapi, Balanipa, Binuang, Bulo, Campalagian, Limboro, Luyo, Mapilli, Matakali, Matangnga, Polewali, Tapango, Tinambung, Tutar, Wonomulyo*).
   - Penginputan langsung memilih Kecamatan $\rightarrow$ Desa/Kelurahan (tanpa perlu input kabupaten manual).
2. **Formulir Pendataan & Validasi Cerdas**:
   - Validasi ketat NIK 16 digit angka dengan deteksi otomatis data ganda.
   - Perhitungan usia otomatis saat tanggal lahir dipilih.
   - Filter kondisi otomatis: jika Masih Bersekolah vs Anak Tidak Sekolah (ATS).
   - Detail intervensi ATS: Ijazah terakhir, status putus sekolah / lulus tidak melanjutkan, alasan utama, penerima KIP, minat kembali bersekolah (Formal / PKBM Non Formal), rencana satuan pendidikan, ukuran baju/sepatu, dan pelatihan keterampilan.
   - Ambil foto langsung dari kamera HP atau galeri dengan kompresi otomatis di sisi klien.
3. **Dashboard Analitik Real-Time (Chart.js)**:
   - 8 Indikator Utama (KPI).
   - 8 Visualisasi visual interaktif: Persebaran ATS per Kecamatan, Kelompok Usia, Minat Kembali Bersekolah, Bentuk Satuan Pendidikan, Alasan Tidak Sekolah, Kebutuhan Keterampilan, dan Ukuran Bantuan Baju/Sepatu.
   - Filter per Kecamatan secara dinamis.
4. **Pusat Export & Cetak**:
   - Ekspor Excel (.xlsx) rapi dengan header resmi Pemkab Polman dan styling tabel.
   - Cetak Kartu Verifikasi ATS individual per anak (siap cetak / simpan PDF dengan tanda tangan petugas & korwil).
   - Cetak Rekapitulasi Laporan Resmi PDF/Print landscape.

---

## 🚀 Panduan Deployment ke `r-ats.polmankab.go.id`

### 1. Clone Repository
```bash
git clone https://github.com/90fathur/ats.git
cd ats
```

### 2. Install Dependensi (Composer)
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Konfigurasi Environment (`.env`)
Salin file `.env.example` menjadi `.env`:
```bash
cp .env.example .env
```
Sesuaikan konfigurasi di dalam file `.env`:
```ini
CI_ENVIRONMENT = production

# Domain Resmi
app.baseURL = 'https://r-ats.polmankab.go.id/'
app.indexPage = ''
app.defaultLocale = 'id'
app.appTimezone = 'Asia/Makassar'

# Database Server
database.default.hostname = localhost
database.default.database = db_ats_polman
database.default.username = user_ats
database.default.password = password_rahasia
database.default.DBDriver = MySQLi
```

### 4. Setup Database
Pilih salah satu cara:
- **Cara A (Import SQL)**: Import file `database/ats_polmankab.sql` ke database MySQL Anda via phpMyAdmin atau CLI:
  ```bash
  mysql -u user_ats -p db_ats_polman < database/ats_polmankab.sql
  ```
- **Cara B (Spark Migration & Seeder)**:
  ```bash
  php spark migrate
  php spark db:seed UserSeeder
  php spark db:seed AtsSeeder
  ```

### 5. Konfigurasi Web Server (Apache / Nginx / cPanel)
- **Document Root**: Arahkan `DocumentRoot` web server ke folder `public/` (misalnya `/home/user/public_html/` atau `/var/www/ats/public`).
- **Izin Folder Writable**: Pastikan folder `writable/` memiliki permission write (chmod 775 atau 777 jika diperlukan).

---

## 🔐 Akun Default

| Username | Password | Role | Nama Lengkap |
| :--- | :--- | :--- | :--- |
| `admin` | `admin` | **Admin** | Administrator ATS |
| `petugas` | `admin` | **Petugas** | Petugas Lapangan ATS |

---

## 💻 Menjalankan di Lokal (Development)

Jika menggunakan Laragon:
- Taruh project di `C:\laragon\www\ats`
- Akses via browser: `http://localhost/ats/` atau `http://localhost/ats/public/`

Atau jalankan bawaan Spark:
```bash
php spark serve --port 8085
```
Akses di: `http://localhost:8085/`
