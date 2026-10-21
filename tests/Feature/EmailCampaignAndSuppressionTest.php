<?php

namespace Tests\Feature;

use App\Models\CampaignRecipient;
use App\Models\EmailCampaign;
use App\Models\EmailTemplate;
use App\Models\Lead;
use App\Models\SuppressionList;
use App\Models\User;
use App\Services\EmailService;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class EmailCampaignAndSuppressionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected EmailService $emailService;
    protected LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->emailService = app(EmailService::class);
        $this->leadService = app(LeadService::class);
    }

    public function test_campaign_email_sending_to_eligible_lead(): void
    {
        $lead = $this->leadService->createLead(['name' => 'John Doe', 'email' => 'john@testdomain.com']);

        $template = EmailTemplate::create([
            'name' => 'Intro Test',
            'subject' => 'Hello {{first_name}} at {{company_name}}',
            'body_html' => '<p>Hi {{first_name}}, check <a href="{{unsubscribe_url}}">opt out</a></p>',
            'category' => 'cold_outreach',
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'Q4 Test Outreach',
            'email_template_id' => $template->id,
            'subject' => $template->subject,
            'from_name' => 'Nexteck',
            'from_email' => 'outreach@nexteck.com',
            'status' => 'running',
        ]);

        $recipient = CampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'status' => 'pending',
            'idempotency_key' => 'unique_key_123',
        ]);

        $success = $this->emailService->sendCampaignEmail($recipient);

        $this->assertTrue($success);
        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $recipient->id,
            'status' => 'sent',
        ]);
        $this->assertDatabaseHas('email_messages', [
            'lead_id' => $lead->id,
            'to_email' => $lead->email,
        ]);
    }

    public function test_suppressed_contact_is_not_sent_campaign_email(): void
    {
        SuppressionList::suppress('optout@victim.com', 'unsubscribed');

        $lead = Lead::create([
            'name' => 'Optout User',
            'email' => 'optout@victim.com',
            'status' => 'new',
        ]);

        $campaign = EmailCampaign::create([
            'name' => 'Test Campaign',
            'subject' => 'Hello',
            'from_name' => 'Nexteck',
            'from_email' => 'outreach@nexteck.com',
        ]);

        $recipient = CampaignRecipient::create([
            'email_campaign_id' => $campaign->id,
            'lead_id' => $lead->id,
            'email' => $lead->email,
            'status' => 'pending',
            'idempotency_key' => 'key_suppress_1',
        ]);

        $success = $this->emailService->sendCampaignEmail($recipient);

        $this->assertFalse($success);
        $this->assertDatabaseHas('campaign_recipients', [
            'id' => $recipient->id,
            'status' => 'suppressed',
        ]);
    }

    public function test_unsubscribe_endpoint_suppresses_contact_permanently(): void
    {
        $lead = $this->leadService->createLead(['name' => 'Unsub Tester', 'email' => 'tester@unsubdomain.com']);

        $token = 'test_token_abc';
        $response = $this->post(route('unsubscribe.process', $token), [
            'email' => 'tester@unsubdomain.com',
            'reason' => 'No longer relevant',
        ]);

        $response->assertStatus(200);
        $response->assertSee('Preferences Updated');

        $this->assertTrue(SuppressionList::isSuppressed('tester@unsubdomain.com'));
        $this->assertDatabaseHas('unsubscribes', [
            'email' => 'tester@unsubdomain.com',
            'lead_id' => $lead->id,
        ]);
    }
}
