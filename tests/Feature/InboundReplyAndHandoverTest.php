<?php

namespace Tests\Feature;

use App\Models\InboundReply;
use App\Models\Lead;
use App\Models\User;
use App\Services\InboundReplyService;
use App\Services\LeadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InboundReplyAndHandoverTest extends TestCase
{
    use RefreshDatabase;

    protected User $manager;
    protected InboundReplyService $inboundService;
    protected LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->manager = User::factory()->create(['role' => 'sales_manager']);
        $this->inboundService = app(InboundReplyService::class);
        $this->leadService = app(LeadService::class);
    }

    public function test_inbound_reply_classifies_meeting_intent_and_creates_handover(): void
    {
        $lead = $this->leadService->createLead([
            'name' => 'Stanley Hudson',
            'email' => 'stanley@florida.com',
            'status' => 'contacted',
            'assigned_user_id' => $this->manager->id,
        ]);

        $reply = $this->inboundService->processReply(
            fromEmail: 'stanley@florida.com',
            subject: 'Re: Proposal',
            body: 'I would like to schedule a call next Tuesday to discuss the contract and pricing.'
        );

        $this->assertDatabaseHas('inbound_replies', [
            'id' => $reply->id,
            'lead_id' => $lead->id,
            'ai_classification' => 'meeting_request',
        ]);

        $this->assertDatabaseHas('manager_handovers', [
            'lead_id' => $lead->id,
            'inbound_reply_id' => $reply->id,
            'status' => 'pending',
            'urgency' => 'urgent',
        ]);

        $this->assertDatabaseHas('lead_tasks', [
            'lead_id' => $lead->id,
            'priority' => 'urgent',
        ]);

        $this->assertDatabaseHas('app_notifications', [
            'user_id' => $this->manager->id,
            'type' => 'handover',
        ]);
    }

    public function test_inbound_reply_out_of_office_detection(): void
    {
        $lead = $this->leadService->createLead([
            'name' => 'Toby Flenderson',
            'email' => 'toby@costarica.com',
        ]);

        $reply = $this->inboundService->processReply(
            fromEmail: 'toby@costarica.com',
            subject: 'Automatic reply: Out of Office',
            body: 'I am currently out of the office on annual leave with limited access to email.'
        );

        $this->assertEquals('out_of_office', $reply->ai_classification);
    }
}
