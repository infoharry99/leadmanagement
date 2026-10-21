<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Suppression List
        Schema::create('suppression_list', function (Blueprint $table) {
            $table->id();
            $table->string('email')->unique();
            $table->string('reason', 50)->default('unsubscribed'); // unsubscribed, hard_bounce, complaint, manual
            $table->string('source', 50)->default('system'); // email_unsubscribe, web_form, import, manual, bounce_webhook
            $table->string('token', 64)->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });

        // 2. Unsubscribes Audit Record
        Schema::create('unsubscribes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')->nullable()->constrained('leads')->nullOnDelete();
            $table->string('email')->index();
            $table->unsignedBigInteger('campaign_id')->nullable()->index();
            $table->string('token', 64)->index();
            $table->string('reason')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('unsubscribes');
        Schema::dropIfExists('suppression_list');
    }
};
