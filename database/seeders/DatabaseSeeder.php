<?php

namespace Database\Seeders;

use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ==========================================
        // PERUSAHAAN 1: PT FinTech Nusantara (Tenant A)
        // ==========================================
        $tenantA = Tenant::create([
            'name' => 'PT FinTech Nusantara',
            'slug' => 'fintech-nusantara',
            'plan' => 'enterprise',
            'is_active' => true,
        ]);

        $adminA = User::create([
            'tenant_id' => $tenantA->id,
            'name' => 'Ridwan (Admin FinTech)',
            'email' => 'admin@fintech.test',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $policiesA = $this->createDefaultPolicies($tenantA->id);

        // Tiket Aman (On Track) untuk Tenant A
        Ticket::create([
            'tenant_id' => $tenantA->id,
            'ticket_number' => 'TKT-FIN-001',
            'created_by' => $adminA->id,
            'assigned_to' => $adminA->id,
            'sla_policy_id' => $policiesA['high']->id,
            'title' => 'Sinkronisasi Mutasi Bank Melambat',
            'description' => 'Proses penarikan data mutasi mengalami jeda sekitar 3 menit.',
            'status' => 'in_progress',
            'sla_deadline_at' => now()->addMinutes(95),
            'is_sla_breached' => false,
        ]);

        // Tiket Terlambat (Breached) untuk Tenant A
        Ticket::create([
            'tenant_id' => $tenantA->id,
            'ticket_number' => 'TKT-FIN-002',
            'created_by' => $adminA->id,
            'assigned_to' => $adminA->id,
            'sla_policy_id' => $policiesA['critical']->id,
            'title' => 'API Payment Gateway Timeout 504',
            'description' => 'Transaksi QRIS gagal diproses sejak pukul 13:00 WIB.',
            'status' => 'open',
            'sla_deadline_at' => now()->subMinutes(25),
            'is_sla_breached' => false,
        ]);

        // ==========================================
        // PERUSAHAAN 2: PT Logistik Cepat (Tenant B)
        // ==========================================
        $tenantB = Tenant::create([
            'name' => 'PT Logistik Cepat',
            'slug' => 'logistik-cepat',
            'plan' => 'pro',
            'is_active' => true,
        ]);

        $adminB = User::create([
            'tenant_id' => $tenantB->id,
            'name' => 'Budi (Admin Logistik)',
            'email' => 'admin@logistik.test',
            'role' => 'admin',
            'password' => Hash::make('password'),
        ]);

        $policiesB = $this->createDefaultPolicies($tenantB->id);

        Ticket::create([
            'tenant_id' => $tenantB->id,
            'ticket_number' => 'TKT-LOG-001',
            'created_by' => $adminB->id,
            'assigned_to' => $adminB->id,
            'sla_policy_id' => $policiesB['medium']->id,
            'title' => 'Pembaruan Koordinat GPS Kurir Tertunda',
            'description' => 'Lokasi kurir di aplikasi pelanggan tidak bergerak selama 15 menit.',
            'status' => 'open',
            'sla_deadline_at' => now()->addMinutes(360),
            'is_sla_breached' => false,
        ]);

        // Jalankan robot SLA agar tiket yang lewat deadline langsung menghasilkan notifikasi
        Artisan::call('sla:check');
    }

    private function createDefaultPolicies(int $tenantId): array
    {
        return [
            'low' => SlaPolicy::create(['tenant_id' => $tenantId, 'name' => 'Low Priority (Pertanyaan Umum)', 'priority' => 'low', 'resolution_time_minutes' => 1440]),
            'medium' => SlaPolicy::create(['tenant_id' => $tenantId, 'name' => 'Medium Priority (Gangguan Minor)', 'priority' => 'medium', 'resolution_time_minutes' => 480]),
            'high' => SlaPolicy::create(['tenant_id' => $tenantId, 'name' => 'High Priority (Fitur Utama Terganggu)', 'priority' => 'high', 'resolution_time_minutes' => 120]),
            'critical' => SlaPolicy::create(['tenant_id' => $tenantId, 'name' => 'Critical Priority (Sistem Total Down)', 'priority' => 'critical', 'resolution_time_minutes' => 30]),
        ];
    }
}
