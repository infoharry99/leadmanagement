<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Audit Logs
        Schema::create('audit_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('action', 100)->index();
            $table->string('entity_type', 100)->nullable()->index();
            $table->unsignedBigInteger('entity_id')->nullable()->index();
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Integration Settings & Health
        Schema::create('integration_settings', function (Blueprint $table) {
            $table->id();
            $table->string('service_name', 50)->unique(); 
            // smtp_email, gemini_ai, openai, linkedin, facebook, instagram, tiktok
            $table->text('credentials')->nullable(); // Encrypted JSON
            $table->json('settings')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('health_status', 30)->default('unknown'); // healthy, degraded, failed, not_configured
            $table->timestamp('last_health_check_at')->nullable();
            $table->text('last_error_message')->nullable();
            $table->timestamps();
        });

        // 3. App Notifications
        Schema::create('app_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->string('title');
            $table->text('message');
            $table->string('type', 30)->default('info'); // info, success, warning, danger, handover, reply
            $table->string('action_url')->nullable();
            $table->boolean('is_read')->default(false)->index();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('app_notifications');
        Schema::dropIfExists('integration_settings');
        Schema::dropIfExists('audit_logs');
    }
};
