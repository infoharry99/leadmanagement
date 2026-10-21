<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Blog Categories
        Schema::create('blog_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->timestamps();
        });

        // 2. Blog Tags
        Schema::create('blog_tags', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->timestamps();
        });

        // 3. Blogs
        Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('summary')->nullable();
            $table->longText('body');
            $table->string('featured_image')->nullable();
            $table->foreignId('category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('status', 30)->default('draft')->index(); // draft, review, scheduled, published, archived
            $table->dateTime('published_at')->nullable()->index();
            
            // SEO Fields
            $table->string('primary_keyword')->nullable()->index();
            $table->json('secondary_keywords')->nullable();
            $table->string('search_intent', 50)->default('informational'); // informational, commercial, transactional, navigational
            $table->string('meta_title')->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url')->nullable();
            $table->string('robots_directive', 50)->default('index, follow');
            
            // Open Graph
            $table->string('og_title')->nullable();
            $table->text('og_description')->nullable();
            $table->string('og_image')->nullable();

            // AEO & GEO (Answer & Generative Engine Optimization)
            $table->json('aeo_geo_faq')->nullable(); // Questions & Answers designed for Answer Engines
            $table->json('structured_data')->nullable(); // JSON-LD schema (Article, FAQPage, BreadcrumbList, Organization)
            $table->text('entities_and_definitions')->nullable(); // Factual entities for GEO citations
            
            // Score & Revisions
            $table->integer('seo_score')->default(0); // 0 - 100 completeness score
            $table->integer('revision_number')->default(1);
            
            $table->timestamps();
        });

        // 4. Pivot: blog_tag_post
        Schema::create('blog_tag_post', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();
            $table->unique(['blog_id', 'blog_tag_id']);
        });

        // 5. Blog Revisions History
        Schema::create('blog_revisions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('blog_id')->constrained('blogs')->cascadeOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->longText('body');
            $table->json('metadata')->nullable();
            $table->integer('revision_number');
            $table->timestamp('created_at')->useCurrent();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_revisions');
        Schema::dropIfExists('blog_tag_post');
        Schema::dropIfExists('blogs');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
