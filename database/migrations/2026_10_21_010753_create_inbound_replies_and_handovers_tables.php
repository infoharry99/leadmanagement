<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Inbound Replies
        Schema::create('inbound_replies', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('email_message_id')->nullable()->constrained('email_messages')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
            $table->string('from_email')->index();
            $table->string('subject');
            $table->longText('body');
            
            // AI Classification (Section 12)
            $table->string('ai_classification', 50)->default('uncertain')->index(); 
            // interested, not_interested, information_request, meeting_request, out_of_office, uncertain
            $table->decimal('ai_confidence', 5, 2)->default(0.00);
            $table->text('ai_summary')->nullable();
            
            $table->boolean('is_reviewed')->default(false);
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            
            $table->string('status', 30)->default('unassigned')->index(); // unassigned, assigned, responded, resolved
            $table->dateTime('received_at')->useCurrent()->index();
            $table->timestamps();
        });

        // 2. Manager Handovers Queue
        Schema::create('manager_handovers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('inbound_reply_id')->nullable()->constrained('inbound_replies')->nullOnDelete();
            $table->foreignId('assigned_manager_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('handover_reason'); // positive_reply, meeting_requested, manual_handover, qualification
            $table->string('urgency', 20)->default('normal'); // normal, urgent, immediate
            $table->string('status', 30)->default('pending')->index(); // pending, acknowledged, in_progress, converted, closed
            $table->text('handoff_notes')->nullable();
            $table->timestamp('acknowledged_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('manager_handovers');
        Schema::dropIfExists('inbound_replies');
    }
};
