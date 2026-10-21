<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Content Calendar
        Schema::create('content_calendars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->date('scheduled_date')->index();
            $table->time('scheduled_time')->default('10:00:00');
            $table->string('target_audience')->nullable();
            $table->string('industry')->nullable();
            $table->string('campaign_goal')->nullable();
            $table->string('objective')->nullable(); // lead_generation, brand_awareness, education, product_demo
            $table->string('platform', 30)->default('linkedin'); // linkedin, instagram, facebook, tiktok, blog
            $table->string('content_type', 30)->default('post'); // post, carousel, video_script, reel, blog
            $table->longText('content_draft')->nullable();
            $table->json('media_assets')->nullable();
            $table->string('status', 30)->default('planned')->index(); 
            // planned, draft, approval_requested, approved, scheduled, published, rejected
            
            $table->foreignId('assigned_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->unsignedBigInteger('social_post_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });

        // 2. Content Approvals
        Schema::create('content_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('content_calendar_id')->nullable()->constrained('content_calendars')->cascadeOnDelete();
            $table->unsignedBigInteger('social_post_id')->nullable()->index();
            $table->foreignId('requested_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('reviewed_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('pending')->index(); // pending, approved, rejected, revision_requested
            $table->text('review_notes')->nullable();
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });

        // 3. AI Content Studio Generations
        Schema::create('ai_content_generations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('topic');
            $table->string('platform', 30)->default('linkedin');
            $table->string('audience')->nullable();
            $table->string('tone', 50)->default('professional');
            $table->string('length', 30)->default('medium');
            $table->string('service_focus')->default('software_development'); 
            // software_development, web_applications, mobile_apps, laravel_development, ecommerce, automation
            $table->text('call_to_action')->nullable();
            $table->text('keywords')->nullable();
            $table->text('prohibited_claims')->nullable();
            $table->json('output_variations'); // array of variations
            $table->longText('selected_output')->nullable();
            $table->string('provider', 30)->default('gemini');
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_content_generations');
        Schema::dropIfExists('content_approvals');
        Schema::dropIfExists('content_calendars');
    }
};
