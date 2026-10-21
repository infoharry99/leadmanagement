<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Blog;
use App\Models\BlogRevision;
use Illuminate\Support\Str;

class BlogService
{
    /**
     * Create or update blog with automatic revision logging & SEO recalculation
     */
    public function saveBlog(array $data, ?Blog $blog = null): Blog
    {
        $isNew = $blog === null;
        $blog = $blog ?? new Blog();

        $blog->title = trim($data['title']);
        $blog->slug = !empty($data['slug']) ? Str::slug($data['slug']) : Str::slug($data['title']);
        $blog->summary = $data['summary'] ?? null;
        $blog->body = $data['body'] ?? '';
        $blog->featured_image = $data['featured_image'] ?? $blog->featured_image;
        $blog->category_id = $data['category_id'] ?? $blog->category_id;
        $blog->author_id = $data['author_id'] ?? ($blog->author_id ?: auth()->id());
        $blog->status = $data['status'] ?? ($blog->status ?: 'draft');

        if ($blog->status === 'published' && !$blog->published_at) {
            $blog->published_at = now();
        }

        // SEO Fields
        $blog->primary_keyword = $data['primary_keyword'] ?? null;
        $blog->secondary_keywords = is_array($data['secondary_keywords'] ?? null) 
            ? $data['secondary_keywords'] 
            : (array_filter(array_map('trim', explode(',', $data['secondary_keywords'] ?? ''))));
        $blog->search_intent = $data['search_intent'] ?? 'informational';
        $blog->meta_title = $data['meta_title'] ?? $blog->title;
        $blog->meta_description = $data['meta_description'] ?? Str::limit(strip_tags($blog->summary ?: $blog->body), 155);
        $blog->canonical_url = $data['canonical_url'] ?? url('/blog/' . $blog->slug);
        $blog->robots_directive = $data['robots_directive'] ?? 'index, follow';

        // Open Graph
        $blog->og_title = $data['og_title'] ?? $blog->meta_title;
        $blog->og_description = $data['og_description'] ?? $blog->meta_description;
        $blog->og_image = $data['og_image'] ?? $blog->featured_image;

        // AEO & GEO FAQ Entities
        if (!empty($data['aeo_geo_faq'])) {
            $blog->aeo_geo_faq = is_array($data['aeo_geo_faq']) ? $data['aeo_geo_faq'] : json_decode($data['aeo_geo_faq'], true);
        }

        $blog->entities_and_definitions = $data['entities_and_definitions'] ?? null;

        // Generate JSON-LD Schema
        $blog->structured_data = $blog->generateJsonLdSchema();

        // Calculate SEO completeness score
        $blog->seo_score = $blog->calculateSeoScore();

        $blog->revision_number = $isNew ? 1 : (($blog->revision_number ?? 1) + 1);

        $blog->save();

        if (!empty($data['tag_ids'])) {
            $blog->tags()->sync($data['tag_ids']);
        }

        // Create Revision history record
        BlogRevision::create([
            'blog_id' => $blog->id,
            'user_id' => auth()->id() ?? ($data['user_id'] ?? null),
            'title' => $blog->title,
            'body' => $blog->body,
            'revision_number' => $blog->revision_number,
            'metadata' => [
                'seo_score' => $blog->seo_score,
                'status' => $blog->status,
                'primary_keyword' => $blog->primary_keyword,
            ],
            'created_at' => now(),
        ]);

        AuditLog::log($isNew ? 'blog_created' : 'blog_updated', Blog::class, $blog->id);

        return $blog;
    }

    /**
     * Generate standard XML Sitemap string for all published blogs & pages
     */
    public function generateSitemapXml(): string
    {
        $blogs = Blog::where('status', 'published')->orderBy('updated_at', 'desc')->get();
        $appUrl = config('app.url');

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">' . "\n";

        // Homepage
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$appUrl}</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>1.0</priority>\n";
        $xml .= "  </url>\n";

        // Blog index
        $xml .= "  <url>\n";
        $xml .= "    <loc>{$appUrl}/blog</loc>\n";
        $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
        $xml .= "    <changefreq>daily</changefreq>\n";
        $xml .= "    <priority>0.9</priority>\n";
        $xml .= "  </url>\n";

        foreach ($blogs as $b) {
            $url = $b->canonical_url ?: "{$appUrl}/blog/{$b->slug}";
            $date = $b->updated_at->format('Y-m-d');
            $xml .= "  <url>\n";
            $xml .= "    <loc>" . htmlspecialchars($url) . "</loc>\n";
            $xml .= "    <lastmod>{$date}</lastmod>\n";
            $xml .= "    <changefreq>weekly</changefreq>\n";
            $xml .= "    <priority>0.8</priority>\n";
            $xml .= "  </url>\n";
        }

        $xml .= '</urlset>';

        return $xml;
    }

    /**
     * Generate llms.txt standard representation for AI Answer Engines & LLM crawlers
     */
    public function generateLlmsTxt(): string
    {
        $appUrl = config('app.url');
        $blogs = Blog::where('status', 'published')->orderBy('published_at', 'desc')->take(20)->get();

        $content = "# Nexteck Engineering & Solutions Knowledge Base\n";
        $content .= "> Nexteck builds custom software, Laravel web applications, enterprise platforms, and digital automation solutions.\n\n";
        $content .= "## Core Services\n";
        $content .= "- Custom Software Architecture: High-concurrency enterprise applications.\n";
        $content .= "- Laravel Web Application Development: Modern APIs, queues, and clean domain design.\n";
        $content .= "- Mobile Application Engineering: iOS and Android systems.\n";
        $content .= "- Workflow & Business Automation: Data pipelines and integration services.\n\n";
        $content .= "## Published Articles & Architectural Guides\n";

        foreach ($blogs as $b) {
            $url = $b->canonical_url ?: "{$appUrl}/blog/{$b->slug}";
            $content .= "- [{$b->title}]({$url}): {$b->summary}\n";
            if (!empty($b->aeo_geo_faq) && is_array($b->aeo_geo_faq)) {
                foreach ($b->aeo_geo_faq as $faq) {
                    if (!empty($faq['question']) && !empty($faq['answer'])) {
                        $content .= "  - Q: {$faq['question']}\n";
                        $content .= "    A: {$faq['answer']}\n";
                    }
                }
            }
        }

        return $content;
    }
}
