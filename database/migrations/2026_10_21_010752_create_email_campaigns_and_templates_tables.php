<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Email Templates
        Schema::create('email_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('subject');
            $table->longText('body_html');
            $table->longText('body_plain')->nullable();
            $table->string('category', 50)->default('general'); // cold_outreach, follow_up, newsletter, re_engagement
            $table->json('variables_schema')->nullable(); // ['first_name', 'company_name', 'service_name']
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Email Campaigns
        Schema::create('email_campaigns', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, scheduled, running, paused, completed, cancelled
            $table->dateTime('scheduled_at')->nullable()->index();
            $table->dateTime('completed_at')->nullable();
            $table->json('target_segment_filter')->nullable(); // industry, status, tags, etc.
            $table->string('subject');
            $table->string('from_name')->default('Nexteck Outreach');
            $table->string('from_email')->default('outreach@nexteck.com');
            $table->string('reply_to')->nullable();
            $table->integer('rate_limit_per_minute')->default(30);
            
            // Performance Counters
            $table->integer('total_recipients')->default(0);
            $table->integer('sent_count')->default(0);
            $table->integer('delivered_count')->default(0);
            $table->integer('bounced_count')->default(0);
            $table->integer('complained_count')->default(0);
            $table->integer('reply_count')->default(0);
            $table->integer('unsubscribe_count')->default(0);

            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 3. Campaign Recipients
        Schema::create('campaign_recipients', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_campaign_id')->constrained('email_campaigns')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->string('email')->index();
            $table->string('status', 30)->default('pending')->index(); 
            // pending, queued, sent, delivered, bounced, complained, replied, failed, suppressed
            $table->string('idempotency_key', 64)->unique();
            $table->timestamp('sent_at')->nullable();
            $table->timestamp('delivered_at')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });

        // 4. Email Messages (Sent & Received record)
        Schema::create('email_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->foreignId('campaign_id')->nullable()->constrained('email_campaigns')->nullOnDelete();
            $table->foreignId('campaign_recipient_id')->nullable()->constrained('campaign_recipients')->nullOnDelete();
            $table->string('message_id')->nullable()->index();
            $table->string('direction', 20)->default('outbound'); // outbound, inbound
            $table->string('subject');
            $table->longText('body_html')->nullable();
            $table->longText('body_text')->nullable();
            $table->string('from_email');
            $table->string('to_email');
            $table->json('headers')->nullable();
            $table->timestamps();
        });

        // 5. Email Events (Delivery, Bounces, etc.)
        Schema::create('email_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('email_message_id')->constrained('email_messages')->cascadeOnDelete();
            $table->string('event_type', 30)->index(); // sent, delivered, bounce, complaint, reply, unsubscribe
            $table->json('payload')->nullable();
            $table->timestamp('occurred_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_events');
        Schema::dropIfExists('email_messages');
        Schema::dropIfExists('campaign_recipients');
        Schema::dropIfExists('email_campaigns');
        Schema::dropIfExists('email_templates');
    }
};
