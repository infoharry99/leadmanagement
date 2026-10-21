<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AiContentService
{
    /**
     * Generate multi-variation content tailored for platforms and Nexteck services
     */
    public function generateContent(array $params): array
    {
        $platform = $params['platform'] ?? 'linkedin';
        $topic = $params['topic'] ?? 'Modern Laravel Architecture';
        $audience = $params['audience'] ?? 'CTOs and Engineering Managers';
        $tone = $params['tone'] ?? 'professional';
        $serviceFocus = $params['service_focus'] ?? 'software_development';
        $cta = $params['call_to_action'] ?? 'Schedule a discovery session with Nexteck';
        $keywords = $params['keywords'] ?? 'scalability, performance, security';

        $geminiKey = config('services.gemini.key') ?: env('GEMINI_API_KEY');

        // If Gemini API Key is configured, attempt real Gemini Flash generation
        if (!empty($geminiKey)) {
            try {
                $prompt = $this->buildPrompt($platform, $topic, $audience, $tone, $serviceFocus, $cta, $keywords);
                $response = Http::withHeaders(['Content-Type' => 'application/json'])
                    ->timeout(15)
                    ->post("https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash:generateContent?key={$geminiKey}", [
                        'contents' => [
                            ['parts' => [['text' => $prompt]]]
                        ],
                        'generationConfig' => [
                            'temperature' => 0.7,
                            'maxOutputTokens' => 1500,
                        ]
                    ]);

                if ($response->successful()) {
                    $json = $response->json();
                    $text = $json['candidates'][0]['content']['parts'][0]['text'] ?? '';
                    if (!empty($text)) {
                        return [
                            'provider' => 'gemini-1.5-flash',
                            'variations' => $this->parseVariations($text, $platform, $topic, $serviceFocus),
                        ];
                    }
                }
            } catch (\Exception $e) {
                Log::warning("Gemini AI API call failed, falling back to built-in generator: " . $e->getMessage());
            }
        }

        // High-fidelity domain-aware generator (always reliable and instant)
        return [
            'provider' => 'nexteck_domain_generator',
            'variations' => $this->generateDomainVariations($platform, $topic, $audience, $tone, $serviceFocus, $cta, $keywords),
        ];
    }

    /**
     * Classify inbound email intent using AI or heuristic semantic classification
     */
    public function classifyReplyIntent(string $body, string $subject): array
    {
        $lowerBody = strtolower($body);
        $lowerSubject = strtolower($subject);
        $fullText = $lowerSubject . ' ' . $lowerBody;

        // 1. Out of Office check
        if (str_contains($fullText, 'out of the office') || 
            str_contains($fullText, 'auto-reply') || 
            str_contains($fullText, 'automatic reply') ||
            str_contains($fullText, 'on annual leave') ||
            str_contains($fullText, 'limited access to email')) {
            return [
                'intent' => 'out_of_office',
                'confidence' => 0.98,
                'summary' => 'Sender is currently out of office or an automated vacation notice was returned.',
            ];
        }

        // 2. Unsubscribe / Not Interested
        if (str_contains($fullText, 'unsubscribe') || 
            str_contains($fullText, 'remove me') || 
            str_contains($fullText, 'not interested') || 
            str_contains($fullText, 'stop emailing') ||
            str_contains($fullText, 'take me off your list')) {
            return [
                'intent' => 'not_interested',
                'confidence' => 0.95,
                'summary' => 'Sender expressed no interest and/or requested removal from marketing outreach.',
            ];
        }

        // 3. Meeting Request
        if (str_contains($fullText, 'calendar') || 
            str_contains($fullText, 'schedule a call') || 
            str_contains($fullText, 'let\'s talk') || 
            str_contains($fullText, 'meeting') || 
            str_contains($fullText, 'zoom') || 
            str_contains($fullText, 'available on tuesday') || 
            str_contains($fullText, 'available next week') || 
            str_contains($fullText, 'send invite')) {
            return [
                'intent' => 'meeting_request',
                'confidence' => 0.92,
                'summary' => 'Sender actively requested or offered a time to meet and discuss capabilities.',
            ];
        }

        // 4. Request for Information / Pricing
        if (str_contains($fullText, 'pricing') || 
            str_contains($fullText, 'how much') || 
            str_contains($fullText, 'case study') || 
            str_contains($fullText, 'send information') || 
            str_contains($fullText, 'portfolio') || 
            str_contains($fullText, 'tell me more') ||
            str_contains($fullText, 'brochure')) {
            return [
                'intent' => 'information_request',
                'confidence' => 0.88,
                'summary' => 'Sender asked for additional information, pricing guidelines, or project case studies.',
            ];
        }

        // 5. General Interest / Project Discussion
        if (str_contains($fullText, 'interesting') || 
            str_contains($fullText, 'exploring') || 
            str_contains($fullText, 'looking for a partner') || 
            str_contains($fullText, 'we need') || 
            str_contains($fullText, 'laravel') || 
            str_contains($fullText, 'migration') || 
            str_contains($fullText, 'mobile app') || 
            str_contains($fullText, 'rewrite')) {
            return [
                'intent' => 'interested',
                'confidence' => 0.85,
                'summary' => 'Sender indicated genuine interest in Nexteck software development or automation services.',
            ];
        }

        return [
            'intent' => 'uncertain',
            'confidence' => 0.50,
            'summary' => 'Inbound message intent requires human sales manager review.',
        ];
    }

    protected function buildPrompt(string $platform, string $topic, string $audience, string $tone, string $service, string $cta, string $keywords): string
    {
        return <<<PROMPT
You are a senior B2B technology copywriter and growth marketer at Nexteck, an elite software engineering agency specializing in custom software development, Laravel web applications, mobile apps, ecommerce systems, and business automation.

Generate 3 high-converting content variations for:
Platform: {$platform}
Topic: {$topic}
Audience: {$audience}
Tone: {$tone}
Service Focus: {$service}
Call To Action: {$cta}
Keywords: {$keywords}

Return output separated clearly with:
--- VARIATION 1 ---
[Content]
--- VARIATION 2 ---
[Content]
--- VARIATION 3 ---
[Content]
PROMPT;
    }

    protected function parseVariations(string $text, string $platform, string $topic, string $service): array
    {
        $parts = preg_split('/--- VARIATION \d+ ---/i', $text);
        $variations = [];
        foreach ($parts as $part) {
            $trimmed = trim($part);
            if (!empty($trimmed)) {
                $variations[] = [
                    'title' => ucfirst($platform) . ' - ' . Str_limit($topic, 40),
                    'content' => $trimmed,
                ];
            }
        }

        if (empty($variations)) {
            $variations[] = ['title' => 'Generated Post', 'content' => $text];
        }

        return $variations;
    }

    protected function generateDomainVariations(string $platform, string $topic, string $audience, string $tone, string $service, string $cta, string $keywords): array
    {
        $serviceTitles = [
            'software_development' => 'Custom Software Architecture',
            'web_applications' => 'High-Performance Web Applications',
            'mobile_apps' => 'Cross-Platform Mobile Engineering',
            'laravel_development' => 'Laravel Enterprise Systems',
            'ecommerce' => 'Scalable Headless E-Commerce',
            'automation' => 'B2B Workflow Automation',
        ];

        $focusName = $serviceTitles[$service] ?? 'Custom Engineering';

        if ($platform === 'linkedin') {
            return [
                [
                    'title' => 'Thought Leadership Hook & Value Matrix',
                    'content' => "Most companies wait until their systems crash during peak traffic before taking architecture seriously.\n\nHere is how we address {$topic} for high-growth teams:\n\n1. Decoupled Service Architecture: Preventing monolithic bottlenecks.\n2. Asynchronous Queue Pipelines: Handling background processing without blocking users.\n3. Robust API Ecosystem: Seamless data flow between internal systems and third parties.\n\nAt Nexteck, we help {$audience} turn technical debt into scalable competitive advantages through {$focusName}.\n\nWhat is your biggest engineering priority this quarter?\n\n👉 {$cta}\n\n#SoftwareEngineering #Laravel #TechLeadership #Nexteck",
                ],
                [
                    'title' => 'Data-Driven Case Insight',
                    'content' => "Why did reducing API response times from 850ms to 92ms increase checkout conversions by 31%?\n\nBecause in enterprise software, performance IS the product.\n\nWhen optimizing for {$topic}, our team focuses on:\n• Query optimization & intelligent caching\n• Resilient error recovery\n• Clean maintainable codebase\n\nIf you're evaluating engineering partners for {$focusName}, let's talk.\n\n{$cta}\n\n#Architecture #Scalability #Nexteck #B2BTech",
                ],
                [
                    'title' => 'Actionable Framework Breakdown',
                    'content' => "A 3-step checklist for {$audience} planning {$topic}:\n\n✅ Audit your current database bottlenecks\n✅ Implement idempotency on all critical webhooks\n✅ Establish automated test suites before deploying new features\n\nNexteck specializes in {$focusName} that lets your team move faster with confidence.\n\n👉 {$cta}\n\n#SoftwareDevelopment #CTO #Nexteck",
                ],
            ];
        }

        if ($platform === 'tiktok') {
            return [
                [
                    'title' => 'Hook & Script (Problem -> Reveal -> CTA)',
                    'content' => "HOOK (0-3s): Stop building your web apps like it's 2015. Here's why.\n\nBODY (3-20s): If your backend takes more than 1 second to load a user dashboard, you're bleeding customers. Here's the 3-step stack we use at Nexteck to build lightning-fast web applications: Laravel 12 for the API, Redis for sub-millisecond caching, and queue workers for all heavy lifting.\n\nCTA (20-30s): Need a software partner that builds it right the first time? Link in bio to book a consultation with Nexteck!\n\nHashtags: #coding #webdev #softwareengineering #tech #startup #nexteck",
                ],
                [
                    'title' => 'Behind The Scenes Developer Reality',
                    'content' => "HOOK: The #1 mistake non-technical founders make when hiring software agencies...\n\nBODY: They buy pretty UI without checking the database architecture! If your database isn't indexed properly, your app will grind to a halt the moment you get 1,000 concurrent users.\n\nNexteck builds enterprise-grade software from day 1.\n\nCTA: Check our profile for software consultations!\n\nHashtags: #programming #laravel #softwaredevelopment #nexteck",
                ],
            ];
        }

        if ($platform === 'instagram') {
            return [
                [
                    'title' => 'Carousel Slide Copy (5-Slide Visual Post)',
                    'content' => "SLIDE 1: 5 Signs Your App Needs Architecture Modernization 🚀\n\nSLIDE 2: Sign #1 - Slow Database Queries holding back response times.\n\nSLIDE 3: Sign #2 - Every new feature breaks two existing features.\n\nSLIDE 4: Sign #3 - No automated test coverage or deployment pipeline.\n\nSLIDE 5: How Nexteck transforms legacy code into high-speed digital products. Tap the link in bio to learn more!\n\nCaption: Is your codebase holding back your business growth? Swipe through our checklist for engineering leaders.\n\n#TechCommunity #WebDevelopment #Laravel #SoftwareAgency #Nexteck",
                ],
                [
                    'title' => 'Direct Value Post & Caption',
                    'content' => "Building scalable web & mobile apps is an art and a science. 💻✨\n\nSwipe to see how Nexteck architects custom software that handles millions in transaction volume with 99.99% uptime.\n\nReady to elevate your technology stack? DM us 'INNOVATE' or visit the link in bio.\n\n#SoftwareDevelopment #MobileApps #Laravel #NexteckEngineering",
                ],
            ];
        }

        // Facebook & Other platforms
        return [
            [
                'title' => 'Educational Case Breakdown',
                'content' => "Is your software ready to scale with your growth this year?\n\nAt Nexteck, we partner with growing enterprises to build custom web applications, mobile platforms, and business automation tools that streamline operations and drive real revenue.\n\nWhether you need to modernize legacy systems or architect a new digital product from scratch, our team provides full-cycle development with transparent communication.\n\n👉 Learn how we can support your roadmap: {$cta}\n\n#BusinessGrowth #CustomSoftware #Technology #Nexteck",
            ],
            [
                'title' => 'Executive ROI Perspective',
                'content' => "Off-the-shelf software solves 70% of your business problems—and creates 100 new ones. Custom software engineered for your exact business workflows eliminates bottlenecks and unlocks massive operational efficiency.\n\nDiscover how Nexteck delivers bespoke development for enterprise leaders.\n\nContact our team: {$cta}",
            ],
        ];
    }
}
