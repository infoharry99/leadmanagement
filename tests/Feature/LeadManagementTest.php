<?php

namespace Tests\Feature;

use App\Models\Lead;
use App\Models\SuppressionList;
use App\Models\User;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class LeadManagementTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->leadService = app(LeadService::class);
    }

    public function test_can_create_lead_successfully(): void
    {
        $lead = $this->leadService->createLead([
            'name' => 'Michael Scott',
            'email' => 'michael@dundermifflin.com',
            'phone' => '+15551234567',
            'company_name' => 'Dunder Mifflin',
            'industry' => 'Paper & Distribution',
            'job_title' => 'Regional Manager',
            'status' => 'new',
            'consent_basis' => 'legitimate_interest',
            'consent_source' => 'Direct Entry',
        ]);

        $this->assertDatabaseHas('leads', [
            'id' => $lead->id,
            'email' => 'michael@dundermifflin.com',
            'status' => 'new',
        ]);
        $this->assertDatabaseHas('companies', [
            'name' => 'Dunder Mifflin',
        ]);
    }

    public function test_duplicate_lead_email_is_prevented(): void
    {
        $this->leadService->createLead([
            'name' => 'Jim Halpert',
            'email' => 'jim@dundermifflin.com',
        ]);

        $this->expectException(ValidationException::class);

        $this->leadService->createLead([
            'name' => 'Jim Duplicate',
            'email' => 'jim@dundermifflin.com',
        ]);
    }

    public function test_suppressed_contact_cannot_be_added_as_lead(): void
    {
        SuppressionList::suppress('optout@acme.com', 'unsubscribed');

        $this->expectException(ValidationException::class);

        $this->leadService->createLead([
            'name' => 'Opted Out User',
            'email' => 'optout@acme.com',
        ]);
    }

    public function test_bulk_lead_status_update(): void
    {
        $lead1 = $this->leadService->createLead(['name' => 'Lead One', 'email' => 'one@test.com']);
        $lead2 = $this->leadService->createLead(['name' => 'Lead Two', 'email' => 'two@test.com']);

        $updated = $this->leadService->bulkUpdateStatus([$lead1->id, $lead2->id], Lead::STATUS_CONTACTED);

        $this->assertEquals(2, $updated);
        $this->assertDatabaseHas('leads', ['id' => $lead1->id, 'status' => Lead::STATUS_CONTACTED]);
        $this->assertDatabaseHas('leads', ['id' => $lead2->id, 'status' => Lead::STATUS_CONTACTED]);
    }

    public function test_lead_timeline_activity_is_recorded(): void
    {
        $lead = $this->leadService->createLead(['name' => 'Dwight', 'email' => 'dwight@schrute.com']);

        $lead->recordActivity('note_added', 'Discussed paper beets inventory');

        $this->assertDatabaseHas('lead_activities', [
            'lead_id' => $lead->id,
            'type' => 'note_added',
            'description' => 'Discussed paper beets inventory',
        ]);
    }
}
