<?php

namespace Tests\Feature;

use App\Models\FollowupSequence;
use App\Models\Lead;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use App\Models\User;
use App\Services\LeadService;
use App\Services\SequenceEngineService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class FollowupSequenceEngineTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected SequenceEngineService $sequenceEngine;
    protected LeadService $leadService;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->sequenceEngine = app(SequenceEngineService::class);
        $this->leadService = app(LeadService::class);
    }

    public function test_can_enroll_lead_in_sequence(): void
    {
        $lead = $this->leadService->createLead(['name' => 'Pam Beesly', 'email' => 'pam@dunder.com']);

        $seq = FollowupSequence::create([
            'name' => 'Standard Nurturing',
            'status' => 'active',
            'send_window_start' => '00:00:00',
            'send_window_end' => '23:59:59',
            'timezone' => 'UTC',
            'exclude_weekends' => false,
        ]);

        $step = SequenceStep::create([
            'followup_sequence_id' => $seq->id,
            'step_order' => 1,
            'phase' => 'phase_a',
            'delay_days' => 0,
            'subject' => 'Intro for {{first_name}}',
            'body_template' => 'Hi {{first_name}}, following up.',
        ]);

        $enrollment = $this->sequenceEngine->enrollLead($lead, $seq);

        $this->assertDatabaseHas('sequence_enrollments', [
            'id' => $enrollment->id,
            'lead_id' => $lead->id,
            'status' => 'active',
            'current_step_id' => $step->id,
        ]);
    }

    public function test_sequence_is_stopped_when_lead_replies(): void
    {
        $lead = $this->leadService->createLead(['name' => 'Andy Bernard', 'email' => 'andy@cornell.edu']);

        $seq = FollowupSequence::create([
            'name' => 'Outreach Seq',
            'status' => 'active',
        ]);

        $step = SequenceStep::create([
            'followup_sequence_id' => $seq->id,
            'step_order' => 1,
            'phase' => 'phase_a',
            'delay_days' => 0,
            'subject' => 'Hello',
            'body_template' => 'Body',
        ]);

        $enrollment = $this->sequenceEngine->enrollLead($lead, $seq);

        // Stop sequences for lead upon reply
        $this->sequenceEngine->stopSequencesForLead($lead->id, 'reply');

        $this->assertDatabaseHas('sequence_enrollments', [
            'id' => $enrollment->id,
            'status' => 'replied_stopped',
            'stop_reason' => 'reply',
        ]);
    }
}
