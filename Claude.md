# CLAUDE.md - Context & Coding Guidelines

## 📌 Project Overview

- **Nama Sistem:** Aplikasi Pelaporan Produksi Harian dan Rekapitulasi Lifting Minyak Bumi Berbasis Web
- **Instansi:** Dinas Energi dan Sumber Daya Mineral (ESDM) Provinsi Sumatera Selatan
- **Tujuan Utama:** Digitalisasi dan otomatisasi pencatatan data produksi harian sumur minyak masyarakat serta rekapitulasi lifting dari Badan Kerjasama Usaha (BKU)/Koperasi untuk evaluasi kepatuhan kontrak.

---

## 👥 User Roles & Access Matrix

1. **Admin / Staf Dinas ESDM (Verifikator, Evaluator & Editor)**
   - Mengelola master data (`bku`, `kontrak`, `bku_kontrak`).
   - Menerima laporan produksi & lifting harian BKU.
   - **Meninjau Pengajuan Justifikasi:** Menyetujui (_Approve_) atau Menolak (_Reject_) permohonan ubah data dari BKU.
   - **Melakukan Edit Data:** Eksekusi perubahan data laporan harian secara langsung berdasarkan justifikasi BKU yang telah disetujui.
   - Mengakses Dashboard Rekapitulasi, Audit Trail, & Visualisasi data.

2. **Operator BKU / Koperasi / UMKM (Pengirim Data)**
   - Menginput laporan produksi & lifting harian per kontrak (Hanya Akses _Create & Read_).
   - **Aturan Akses Edit:** **TIDAK BISA** mengubah data laporan harian yang sudah disimpan secara langsung.
   - **Mengajukan Justifikasi:** Mengirimkan formulir permohonan revisi data (disertai alasan/justifikasi & data usulan baru) ke Staf Dinas.
   - Disetiap List Laporan Harian di Bagian BKU akan ada tombol untuk mengajukan justifikasi di setiap listnya, jadi memudahkan BKU untuk mengajukan justifikasi pada data yang diinginkan

---

## 🛠️ Tech Stack & Dependencies

- **Backend Framework:** PHP 8.x + Laravel (Latest)
- **Frontend Framework:** Vue.js (Inertia.js SSR)
- **Database:** MySQL
- **Styling:** Tailwind CSS
- **Integrasi Tambahan:**
  - **Location Sharing:** Google Maps Redirect URL (`https://maps.google.com/?q={lat},{long}`) — _tanpa API Key/SDK berbayar_.
  - Mailer/SMTP (Auto-reminder notifikasi email)
  - PDF/Excel Generator (Export Laporan Rekapitulasi)

---

## 🔑 Key Business Logic & Domain Concepts

- **Produksi Harian:** Data harian volume minyak bumi yang dihasilkan per sumur/wilayah BKU. setiap sumur terkait dalam satu kontrak (Pertamina/Medco), setiap BKU memiliki kontrak yang berbeda namun saat ini hanya 2.

- **Lifting Minyak:** Proses pengeluaran/penjualan/penyerahan minyak bumi hasil produksi dari titik kumpul BKU.

- **Evaluasi Kepatuhan:** Sistem membandingkan target kuota/skema kontrak BKU dengan realisasi produksi & lifting harian.

- **Audit Trail:** Setiap aktivitas perubahan data laporan (siapa, kapan, apa yang diubah) wajib tercatat di database log (`audit_logs`).

---

## 📐 Coding Conventions & Guidelines

### 1. Backend (Laravel - Penamaan Bahasa Indonesia)

- **Naming Conventions:**
  - Controllers: PascalCase + Controller (`LaporanProduksiController.php`, `RekapLiftingController.php`, `BkuController.php`)
  - Models: Singular PascalCase (`LaporanProduksi.php`, `BkuKontrak.php`, `RekapLifting.php`)
  - Database Tables: Plural/Singular snake_case Bahasa Indonesia.
  - Column/Field Names: snake_case Bahasa Indonesia (`volume_barel`, `tanggal_produksi`)
  - Migrations: `create_laporan_produksi_table`, `create_bku_table`
- **Architecture Pattern:**
  - Gunakan **Form Request Validation** untuk setiap _incoming request_ (`SimpanLaporanProduksiRequest.php`).
  - Tempatkan _business logic_ kompleks pada **Service Class** (`Services/HitungLiftingService.php`), hindari logika panjang di Controller.
  - Manfaatkan **API Resources** untuk _response formatting_ frontend (`LaporanProduksiResource.php`).
  - Enforce **Database Transactions** (`DB::transaction`) saat menyimpan laporan yang melibatkan audit log atau upload file.
  - Buat Accessor/Helper khusus di Model untuk generasi URL Google Maps berdasarkan atribut `latitude` dan `longitude`.

### 2. Database Schema & Relationships Detail

Sistem menggunakan arsitektur _Future-Proof Many-to-Many (N:M)_ untuk mengantisipasi penambahan kontrak BKU di masa mendatang tanpa mengubah struktur tabel SQL.
berikut adalah nama-nama tabel beserta fieldnya

- **bku**
  - id (integer)
  - nama (text)
  - penetapan (integer)

- **kontrak**
  - id (integer)
  - nama (text)

- **sumur**
  - id (integer)
  - bku_kontrak_id (foreign_key 'id' di table 'bku_kontrak')
  - nama_sumur (varchar)
  - desa (varchar)
  - kecamatan (varchar)
  - kabupaten (varchar)
  - latitude (double 12,9)
  - longitude (double 11,8)

- **bku_kontrak**
  - id (integer)
  - bku_id (foreign_key 'id' di table 'bku')
  - kontrak_id (foreign key 'id' di table 'kontrak')
  - jumlah_sumur (integer)

- **laporan_harian**
  - id (integer)
  - bku_kontrak_id (foreign_key 'id' di table 'bku_kontrak')
  - tanggal (date)
  - total_produksi (decimal)
  - total_lifting (decimal)
  - keterangan (text)

- **audit_logs**

  - id: Identitas unik (Primary Key) untuk setiap baris log.

  - user_id: ID pengguna (users.id) yang melakukan tindakan. Menggunakan ON DELETE SET NULL agar riwayat log tidak hilang jika akun pengguna dihapus.

  - action: Jenis tindakan yang dilakukan (contoh: APPROVED_JUSTIFIKASI, REJECTED_JUSTIFIKASI, UPDATE_LAPORAN).

  - auditable_type: Nama Model/Tabel target yang diubah (contoh: App\Models\LaporanHarian).

  - auditable_id: ID dari baris data yang mengalami perubahan (laporan_harian_id).

  - old_values: Data sebelum diubah dalam format JSON (contoh: {"total_produksi": 100, "total_lifting": 90}).

  - new_values: Data setelah diubah dalam format JSON (contoh: {"total_produksi": 120, "total_lifting": 95}).

  - created_at: Waktu pasti (timestamp) saat aktivitas terjadi. Kolom updated_at tidak diperlukan karena data log tidak boleh diubah setelah dicatat.


### 3. Frontend (Vue.js)

- **Component Design:**
  - Gunakan **Composition API (`<script setup>`)**.
  - Pisahkan komponen re-usable (Tabel, Form Input, Modal, Stats Card) di direktori `Components/`.
  - Halaman utama berada di direktori `Pages/`.
- **Naming Conventions:**
  - Components: PascalCase (`StatusBadge.vue`, `ProductionTable.vue`).
  - Props & Events: camelCase untuk props (`reportData`), kebab-case untuk event emission (`@report-submitted`).

### 4. Database & Security Guidelines

- Selalu manfaatkan Foreign Key Constraints dengan aturan cascade yang jelas.
- Setiap tabel utama wajib memiliki timestamp (`created_at`, `updated_at`).
- Terapkan **Laravel Policy / Gate** untuk Authorization (Pastikan BKU A tidak bisa melihat/mengedit laporan BKU B).
- Password dan credential BKU disimpan dengan enkripsi standar Laravel (`Bcrypt/Argon2`).

---

## 🚀 Development Quick Commands

```bash
# Backend Setup & Run
php artisan migrate:fresh --seed
php artisan serve

# Frontend Setup & Watch
npm install
npm run dev

# Testing & Quality
php artisan test
```
