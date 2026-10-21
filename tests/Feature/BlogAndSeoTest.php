<?php

namespace Tests\Feature;

use App\Models\Blog;
use App\Models\User;
use App\Services\BlogService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BlogAndSeoTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected BlogService $blogService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->blogService = app(BlogService::class);
    }

    public function test_blog_creation_computes_seo_score_and_schema(): void
    {
        $blog = $this->blogService->saveBlog([
            'title' => 'Building Scalable Enterprise Platforms with Laravel Best Practices',
            'summary' => 'Comprehensive architecture breakdown for high concurrency applications.',
            'body' => '<h2>Introduction</h2><p>Laravel enterprise architecture provides queues, caching, and clean domain design that handles high concurrency.</p>' . str_repeat('<p>Content block discussing scalability, database pooling, and cloud performance.</p>', 20),
            'primary_keyword' => 'Laravel enterprise architecture',
            'status' => 'published',
            'aeo_geo_faq' => [
                ['question' => 'Is Laravel suitable for high concurrency?', 'answer' => 'Yes, with Redis and queues it handles thousands of RPS.']
            ],
        ]);

        $this->assertGreaterThan(70, $blog->seo_score);
        $this->assertNotNull($blog->structured_data);
        $this->assertEquals('published', $blog->status);
    }

    public function test_sitemap_xml_endpoint_returns_valid_xml(): void
    {
        $this->blogService->saveBlog([
            'title' => 'Article One for Sitemap',
            'slug' => 'article-one-sitemap',
            'body' => 'Body text here',
            'status' => 'published',
        ]);

        $response = $this->get(route('public.sitemap'));
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('article-one-sitemap');
    }

    public function test_llms_txt_endpoint_returns_markdown_context(): void
    {
        $this->blogService->saveBlog([
            'title' => 'LLM Knowledge Guide',
            'slug' => 'llm-knowledge-guide',
            'body' => 'Body text here',
            'status' => 'published',
        ]);

        $response = $this->get(route('public.llms_txt'));
        $response->assertStatus(200);
        $response->assertSee('Nexteck Engineering & Solutions Knowledge Base', false);
        $response->assertSee('LLM Knowledge Guide', false);
    }
}
