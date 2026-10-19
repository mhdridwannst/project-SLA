# OneSLA — Multi-Tenant B2B Incident & SLA Telemetry Platform

**OneSLA** adalah platform _Software-as-a-Service_ (SaaS) berbasis **Laravel** untuk manajemen insiden operasional, pelacakan _Service Level Agreement_ (SLA), dan audit kepatuhan tim teknis dengan arsitektur **Single-Database Multi-Tenancy**.

---

## Arsitektur & Keputusan Teknis Utama

### 1. Automated Multi-Tenant Isolation (`Global Scope`)

Menggunakan trait `BelongsToTenant` yang menyuntikkan filter `tenant_id` secara otomatis pada siklus hidup Eloquent (`addGlobalScope` saat _query_ dan `creating` hook saat _insert_). Menjamin isolasi data antar-perusahaan termasuk pemisahan direktori penyimpanan lampiran (`storage/app/public/tenants/{tenant_id}`).

### 2. Dual-Metric SLA Engine dengan Mekanisme _Clock Pause/Resume_

Setiap tiket melacak dua indikator SLA secara simultan melalui `TicketService`:

- **First Response SLA (MTTA):** Mengukur batas waktu hingga teknisi (_Support Agent_) memberikan balasan publik pertama.
- **Resolution SLA:** Mengukur batas waktu hingga insiden berstatus _Resolved_.
- **Dynamic SLA Pause:** Saat status tiket diubah menjadi `Waiting on Customer`, arloji SLA otomatis dijeda (`sla_paused_at`). Ketika pelanggan membalas atau status kembali ke `In Progress`, sistem menghitung durasi jeda dan mengundurkan `sla_deadline_at` secara presisi.

### 3. Type-Safe Domain & Role-Based Access Control (RBAC)

- Menggunakan **PHP 8.2+ Backed Enums** (`TicketStatus`, `TicketPriority`, `UserRole`) untuk menjaga konsistensi status di seluruh lapisan aplikasi.
- Otorisasi dikontrol melalui **Laravel Policies (`TicketPolicy`)**:
    - **Workspace Admin:** Mengelola matriks SLA, anggota tim, dan seluruh tiket operasional.
    - **Support Agent:** Menangani tiket, menulis _Internal Note_ (catatan rahasia tim), dan memperbarui status insiden.
    - **Customer / Reporter:** Melaporkan insiden beserta lampiran bukti dan berdiskusi pada tiket miliknya sendiri.

### 4. Immutable Audit Trail & Background Escalation Worker

- Setiap perubahan status, pergantian PIC (_idempotent check_), jeda SLA, dan komunikasi dicatat secara permanen ke tabel `ticket_activities`.
- Dilengkapi _scheduled command_ (`php artisan sla:check`) yang berjalan setiap menit di latar belakang (`withoutGlobalScope`) untuk mendeteksi pelanggaran SLA lintas organisasi dan mengirimkan notifikasi eskalasi.

---

## Tech Stack

- **Backend:** PHP 8.3, Laravel, Eloquent ORM
- **Frontend:** Blade, Tailwind CSS, Alpine.js (Linear/Vercel Zinc UI System)
- **Database:** SQLite / MySQL / PostgreSQL
- **Testing:** PHPUnit (Feature Testing dengan _Time-Travel Assertions_)

---

## Instalasi & Penggunaan Lokal

```bash
# 1. Clone repository
git clone https://github.com/mhdridwannst/project-SLA.git
cd slaguard

# 2. Install dependencies & build assets
composer install
npm install && npm run build

# 3. Konfigurasi environment & database
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link

# 4. Jalankan server lokal
php artisan serve
```
