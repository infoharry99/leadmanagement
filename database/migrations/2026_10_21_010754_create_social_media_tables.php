<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Social Accounts
        Schema::create('social_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('platform', 30)->index(); // linkedin, facebook, instagram, tiktok
            $table->string('account_name');
            $table->string('account_id')->index();
            $table->string('avatar_url')->nullable();
            $table->text('access_token'); // Encrypted
            $table->text('refresh_token')->nullable(); // Encrypted
            $table->timestamp('token_expires_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('metadata')->nullable(); // scopes, username, profile_url
            $table->timestamps();
        });

        // 2. Social Posts
        Schema::create('social_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title')->nullable();
            $table->longText('content');
            $table->json('target_platforms'); // ['linkedin', 'instagram', 'facebook', 'tiktok']
            $table->json('media_urls')->nullable();
            $table->string('status', 30)->default('draft')->index(); 
            // draft, pending_approval, scheduled, publishing, published, failed
            $table->dateTime('scheduled_at')->nullable()->index();
            $table->dateTime('published_at')->nullable();
            $table->foreignId('approved_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('created_by_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('error_message')->nullable();
            $table->json('hashtags')->nullable();
            $table->timestamps();
        });

        // 3. Social Post Accounts (Publishing pivot per account)
        Schema::create('social_post_accounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_post_id')->constrained('social_posts')->cascadeOnDelete();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('platform_post_id')->nullable()->index();
            $table->string('status', 30)->default('pending')->index(); // pending, published, failed
            $table->text('error_message')->nullable();
            $table->json('engagement_metrics')->nullable(); // likes, shares, comments, impressions
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });

        // 4. Social Comments
        Schema::create('social_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('platform_post_id')->nullable()->index();
            $table->string('comment_id')->index();
            $table->string('author_name');
            $table->string('author_id')->nullable();
            $table->text('content');
            $table->string('sentiment', 20)->default('neutral'); // positive, negative, neutral, lead_inquiry
            $table->string('status', 30)->default('unread')->index(); // unread, responded, archived
            $table->string('parent_comment_id')->nullable();
            $table->timestamp('commented_at')->useCurrent()->index();
            $table->timestamps();
        });

        // 5. Social Messages
        Schema::create('social_messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('social_account_id')->constrained('social_accounts')->cascadeOnDelete();
            $table->string('sender_name');
            $table->string('sender_id');
            $table->text('message_text');
            $table->boolean('is_inbound')->default(true);
            $table->string('status', 30)->default('unread')->index();
            $table->timestamp('received_at')->useCurrent();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('social_messages');
        Schema::dropIfExists('social_comments');
        Schema::dropIfExists('social_post_accounts');
        Schema::dropIfExists('social_posts');
        Schema::dropIfExists('social_accounts');
    }
};
