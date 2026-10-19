<?php

namespace Tests\Feature;

use App\Enums\TicketStatus;
use App\Models\SlaPolicy;
use App\Models\Tenant;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class MultiTenantIsolationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_from_tenant_a_cannot_see_tickets_from_tenant_b(): void
    {
        $tenantA = Tenant::create(['name' => 'Tenant A', 'slug' => 'tenant-a']);
        $tenantB = Tenant::create(['name' => 'Tenant B', 'slug' => 'tenant-b']);

        $userA = User::factory()->create(['tenant_id' => $tenantA->id, 'role' => 'admin']);
        $userB = User::factory()->create(['tenant_id' => $tenantB->id, 'role' => 'admin']);

        $policyA = SlaPolicy::create([
            'tenant_id' => $tenantA->id,
            'name' => 'High SLA',
            'priority' => 'high',
            'first_response_time_minutes' => 15,
            'resolution_time_minutes' => 60,
        ]);

        $policyB = SlaPolicy::create([
            'tenant_id' => $tenantB->id,
            'name' => 'High SLA',
            'priority' => 'high',
            'first_response_time_minutes' => 15,
            'resolution_time_minutes' => 60,
        ]);

        Ticket::create([
            'tenant_id' => $tenantA->id,
            'ticket_number' => 'TKT-A01',
            'created_by' => $userA->id,
            'sla_policy_id' => $policyA->id,
            'title' => 'Internal Issue A',
            'description' => 'Secret A',
            'first_response_deadline_at' => now()->addMinutes(15),
            'sla_deadline_at' => now()->addHour(),
        ]);

        Ticket::create([
            'tenant_id' => $tenantB->id,
            'ticket_number' => 'TKT-B01',
            'created_by' => $userB->id,
            'sla_policy_id' => $policyB->id,
            'title' => 'Internal Issue B',
            'description' => 'Secret B',
            'first_response_deadline_at' => now()->addMinutes(15),
            'sla_deadline_at' => now()->addHour(),
        ]);

        $this->actingAs($userA);

        $visibleTickets = Ticket::all();

        $this->assertCount(1, $visibleTickets);
        $this->assertEquals('TKT-A01', $visibleTickets->first()->ticket_number);
    }

    public function test_sla_check_command_marks_overdue_tickets_as_breached(): void
    {
        $tenant = Tenant::create(['name' => 'PT Uji Coba', 'slug' => 'uji-coba']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
        $policy = SlaPolicy::create([
            'tenant_id' => $tenant->id,
            'name' => 'Critical',
            'priority' => 'critical',
            'first_response_time_minutes' => 10,
            'resolution_time_minutes' => 30,
        ]);

        $ticket = Ticket::create([
            'tenant_id' => $tenant->id,
            'ticket_number' => 'TKT-OVERDUE-01',
            'created_by' => $admin->id,
            'assigned_to' => $admin->id,
            'sla_policy_id' => $policy->id,
            'title' => 'Production Database Down',
            'description' => 'Testing automated SLA breach detection',
            'status' => TicketStatus::OPEN,
            'first_response_deadline_at' => now()->subMinutes(20),
            'sla_deadline_at' => now()->subMinutes(10),
            'is_sla_breached' => false,
        ]);

        Artisan::call('sla:check');

        $this->assertTrue($ticket->fresh()->is_sla_breached);
        $this->assertCount(1, $admin->fresh()->unreadNotifications);
    }

    public function test_sla_timer_pauses_and_extends_deadline_when_waiting_on_customer(): void
    {
        $tenant = Tenant::create(['name' => 'PT SLA Pause', 'slug' => 'sla-pause']);
        $admin = User::factory()->create(['tenant_id' => $tenant->id, 'role' => 'admin']);
        $policy = SlaPolicy::create([
            'tenant_id' => $tenant->id,
            'name' => 'High',
            'priority' => 'high',
            'first_response_time_minutes' => 15,
            'resolution_time_minutes' => 60,
        ]);

        $this->actingAs($admin);
        $service = app(TicketService::class);

        $ticket = $service->createTicket($admin, [
            'title' => 'Test Pause SLA',
            'description' => 'Testing pause mechanism',
            'sla_policy_id' => $policy->id,
        ]);

        // 1. Ubah status ke Waiting on Customer (Mulai Pause)
        $service->updateStatus($ticket, $admin, TicketStatus::WAITING_ON_CUSTOMER);
        $this->assertNotNull($ticket->fresh()->sla_paused_at);

        // 2. Simulasikan pelanggan baru membalas setelah 20 menit berlalu
        $this->travel(20)->minutes();
        $service->updateStatus($ticket->fresh(), $admin, TicketStatus::IN_PROGRESS);

        $updatedTicket = $ticket->fresh();
        $this->assertNull($updatedTicket->sla_paused_at);
        $this->assertGreaterThanOrEqual(20, $updatedTicket->total_paused_minutes);
    }
}
