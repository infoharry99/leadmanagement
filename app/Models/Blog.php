<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Blog extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'summary',
        'body',
        'featured_image',
        'category_id',
        'author_id',
        'status',
        'published_at',
        'primary_keyword',
        'secondary_keywords',
        'search_intent',
        'meta_title',
        'meta_description',
        'canonical_url',
        'robots_directive',
        'og_title',
        'og_description',
        'og_image',
        'aeo_geo_faq',
        'structured_data',
        'entities_and_definitions',
        'seo_score',
        'revision_number',
    ];

    protected $casts = [
        'secondary_keywords' => 'array',
        'aeo_geo_faq' => 'array',
        'structured_data' => 'array',
        'published_at' => 'datetime',
        'seo_score' => 'integer',
        'revision_number' => 'integer',
    ];

    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_tag_post');
    }

    public function revisions(): HasMany
    {
        return $this->hasMany(BlogRevision::class)->latest('created_at');
    }

    // Dynamic SEO, AEO, and GEO scoring engine
    public function calculateSeoScore(): int
    {
        $score = 0;

        // Title checks (optimal title length 20 - 80 chars)
        if (!empty($this->title) && strlen($this->title) >= 20 && strlen($this->title) <= 85) {
            $score += 15;
        }

        // Meta Description check (optimal meta length 50 - 180 chars)
        if (!empty($this->meta_description) && strlen($this->meta_description) >= 50 && strlen($this->meta_description) <= 180) {
            $score += 15;
        }

        // Primary Keyword present in title, meta description, and body
        if (!empty($this->primary_keyword)) {
            $pk = strtolower(trim($this->primary_keyword));
            if (str_contains(strtolower($this->title), $pk)) {
                $score += 10;
            } else {
                // Partial keyword check
                $words = explode(' ', $pk);
                if (count($words) > 1 && str_contains(strtolower($this->title), $words[0])) {
                    $score += 5;
                }
            }

            if (str_contains(strtolower($this->meta_description ?? ''), $pk)) {
                $score += 10;
            } elseif (!empty($this->meta_description)) {
                $score += 5;
            }

            if (str_contains(strtolower($this->body), $pk)) {
                $score += 10;
            }
        }

        // Content Length (at least 500 words for deep-dive, 100 words minimum)
        $wordCount = str_word_count(strip_tags($this->body));
        if ($wordCount >= 500) {
            $score += 15;
        } elseif ($wordCount >= 100) {
            $score += 10;
        } elseif ($wordCount >= 30) {
            $score += 5;
        }

        // Featured image
        if (!empty($this->featured_image)) {
            $score += 10;
        }

        // AEO / GEO Q&A Entities
        if (!empty($this->aeo_geo_faq)) {
            $faqCount = count($this->aeo_geo_faq);
            if ($faqCount >= 2) {
                $score += 15;
            } elseif ($faqCount >= 1) {
                $score += 10;
            }
        }

        // Structured Data presence
        if (!empty($this->structured_data)) {
            $score += 10;
        }

        return min(100, $score);
    }

    // Build JSON-LD Structured Data Schema
    public function generateJsonLdSchema(): array
    {
        $schema = [
            '@context' => 'https://schema.org',
            '@graph' => [
                [
                    '@type' => 'Organization',
                    '@id' => config('app.url') . '/#organization',
                    'name' => 'Nexteck',
                    'url' => config('app.url'),
                    'logo' => config('app.url') . '/images/nexteck-logo.png',
                    'description' => 'Custom Software, Web & Mobile App Development and Lead Generation Solutions.',
                ],
                [
                    '@type' => 'Article',
                    '@id' => ($this->canonical_url ?: url('/blog/' . $this->slug)) . '/#article',
                    'headline' => $this->meta_title ?: $this->title,
                    'description' => $this->meta_description ?: $this->summary,
                    'url' => $this->canonical_url ?: url('/blog/' . $this->slug),
                    'datePublished' => $this->published_at ? $this->published_at->toIso8601String() : ($this->created_at ? $this->created_at->toIso8601String() : now()->toIso8601String()),
                    'dateModified' => $this->updated_at ? $this->updated_at->toIso8601String() : now()->toIso8601String(),
                    'author' => [
                        '@type' => 'Person',
                        'name' => $this->author ? $this->author->name : 'Nexteck Editorial Team',
                    ],
                    'publisher' => [
                        '@id' => config('app.url') . '/#organization',
                    ],
                ]
            ]
        ];

        // Append FAQPage schema if AEO/GEO FAQ entities exist
        if (!empty($this->aeo_geo_faq) && is_array($this->aeo_geo_faq)) {
            $faqItems = [];
            foreach ($this->aeo_geo_faq as $faq) {
                if (!empty($faq['question']) && !empty($faq['answer'])) {
                    $faqItems[] = [
                        '@type' => 'Question',
                        'name' => $faq['question'],
                        'acceptedAnswer' => [
                            '@type' => 'Answer',
                            'text' => $faq['answer'],
                        ]
                    ];
                }
            }

            if (!empty($faqItems)) {
                $schema['@graph'][] = [
                    '@type' => 'FAQPage',
                    'mainEntity' => $faqItems,
                ];
            }
        }

        return $schema;
    }
}
