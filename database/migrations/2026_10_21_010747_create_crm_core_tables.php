<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Companies
        Schema::create('companies', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('domain')->nullable()->index();
            $table->string('industry')->nullable()->index();
            $table->string('size', 50)->nullable();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();
            $table->text('address')->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->timestamps();
        });

        // 2. Lead Sources
        Schema::create('lead_sources', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('code', 50)->unique();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Lead Tags
        Schema::create('lead_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('color', 20)->default('#4F46E5');
            $table->timestamps();
        });

        // 4. Leads
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('name');
            $table->string('email')->index();
            $table->string('phone', 50)->nullable();
            $table->string('website')->nullable();
            $table->string('industry', 100)->nullable()->index();
            $table->string('job_title', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('city', 100)->nullable();
            $table->foreignId('lead_source_id')->nullable()->constrained('lead_sources')->nullOnDelete();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->string('status', 30)->default('new')->index(); 
            // new, contacted, interested, qualified, converted, not_interested, lost
            
            // Compliance & Consent
            $table->string('consent_basis', 50)->default('legitimate_interest'); 
            // legitimate_interest, explicit_consent, contractual, public_record
            $table->string('consent_source')->nullable();
            $table->timestamp('consent_at')->nullable();

            // Engagement & Follow-up
            $table->integer('email_engagement_score')->default(0);
            $table->timestamp('last_contacted_at')->nullable();
            $table->timestamp('next_followup_at')->nullable()->index();
            $table->text('notes')->nullable();
            $table->json('custom_fields')->nullable();
            $table->boolean('is_archived')->default(false)->index();
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 5. Pivot: lead_tag_lead
        Schema::create('lead_tag_lead', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('lead_tag_id')->constrained('lead_tags')->cascadeOnDelete();
            $table->unique(['lead_id', 'lead_tag_id']);
        });

        // 6. Lead Activities
        Schema::create('lead_activities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('type', 50)->index(); 
            // status_change, email_sent, reply_received, note_added, task_created, manager_assigned, unsubscribed, imported
            $table->text('description');
            $table->json('metadata')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 7. Lead Notes
        Schema::create('lead_notes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->text('note');
            $table->timestamps();
        });

        // 8. Lead Tasks
        Schema::create('lead_tasks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->dateTime('due_date')->index();
            $table->string('priority', 20)->default('medium'); // low, medium, high, urgent
            $table->string('status', 20)->default('pending')->index(); // pending, in_progress, completed, overdue, cancelled
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_tasks');
        Schema::dropIfExists('lead_notes');
        Schema::dropIfExists('lead_activities');
        Schema::dropIfExists('lead_tag_lead');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('lead_tags');
        Schema::dropIfExists('lead_sources');
        Schema::dropIfExists('companies');
    }
};
