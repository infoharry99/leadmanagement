<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Followup Sequences
        Schema::create('followup_sequences', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('status', 30)->default('active')->index(); // active, paused, archived
            $table->time('send_window_start')->default('09:00:00');
            $table->time('send_window_end')->default('18:00:00');
            $table->string('timezone', 50)->default('UTC');
            $table->boolean('exclude_weekends')->default(true);
            $table->boolean('exclude_holidays')->default(true);
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        // 2. Sequence Steps
        Schema::create('sequence_steps', function (Blueprint $table) {
            $table->id();
            $table->foreignId('followup_sequence_id')->constrained('followup_sequences')->cascadeOnDelete();
            $table->integer('step_order')->default(1);
            $table->string('phase', 30)->default('phase_a'); // phase_a (weekly), phase_b (twice_monthly), phase_c (monthly)
            $table->integer('delay_days')->default(7); // days to wait after previous step
            $table->string('subject');
            $table->longText('body_template');
            $table->foreignId('email_template_id')->nullable()->constrained('email_templates')->nullOnDelete();
            $table->timestamps();
            $table->unique(['followup_sequence_id', 'step_order']);
        });

        // 3. Sequence Enrollments
        Schema::create('sequence_enrollments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('followup_sequence_id')->constrained('followup_sequences')->cascadeOnDelete();
            $table->foreignId('lead_id')->constrained('leads')->cascadeOnDelete();
            $table->foreignId('current_step_id')->nullable()->constrained('sequence_steps')->nullOnDelete();
            $table->string('status', 30)->default('active')->index(); 
            // active, paused, completed, cancelled, replied_stopped, unsubscribed_stopped, bounced_stopped
            $table->dateTime('next_run_at')->nullable()->index();
            $table->dateTime('last_sent_at')->nullable();
            $table->string('stop_reason')->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->unique(['followup_sequence_id', 'lead_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sequence_enrollments');
        Schema::dropIfExists('sequence_steps');
        Schema::dropIfExists('followup_sequences');
    }
};
