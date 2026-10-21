<?php

namespace Database\Seeders;

use App\Models\Blog;
use App\Models\BlogCategory;
use App\Models\BlogTag;
use App\Models\CampaignRecipient;
use App\Models\Company;
use App\Models\ContentCalendar;
use App\Models\EmailCampaign;
use App\Models\EmailMessage;
use App\Models\EmailTemplate;
use App\Models\FollowupSequence;
use App\Models\InboundReply;
use App\Models\IntegrationSetting;
use App\Models\Lead;
use App\Models\LeadActivity;
use App\Models\LeadNote;
use App\Models\LeadSource;
use App\Models\LeadTag;
use App\Models\LeadTask;
use App\Models\ManagerHandover;
use App\Models\SequenceEnrollment;
use App\Models\SequenceStep;
use App\Models\SocialAccount;
use App\Models\SocialComment;
use App\Models\SocialPost;
use App\Models\SocialPostAccount;
use App\Models\SuppressionList;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Core Users with Roles
        $admin = User::firstOrCreate(
            ['email' => 'admin@nexteck.com'],
            [
                'name' => 'Alexander Nexteck (Admin)',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '+1 (555) 019-2831',
                'status' => 'active',
            ]
        );

        $manager = User::firstOrCreate(
            ['email' => 'manager@nexteck.com'],
            [
                'name' => 'Sarah Jenkins (Sales Manager)',
                'password' => Hash::make('password'),
                'role' => 'sales_manager',
                'phone' => '+1 (555) 019-4720',
                'status' => 'active',
            ]
        );

        $salesRep = User::firstOrCreate(
            ['email' => 'sales@nexteck.com'],
            [
                'name' => 'David Miller (Outreach Rep)',
                'password' => Hash::make('password'),
                'role' => 'sales_rep',
                'phone' => '+1 (555) 019-8812',
                'status' => 'active',
            ]
        );

        // 2. Lead Sources
        $sources = [
            ['name' => 'LinkedIn Outreach', 'code' => 'linkedin'],
            ['name' => 'Website Inbound Form', 'code' => 'website'],
            ['name' => 'Cold Email Campaign', 'code' => 'cold_email'],
            ['name' => 'Referral / Partner', 'code' => 'referral'],
            ['name' => 'Google Search Organic', 'code' => 'google_seo'],
            ['name' => 'Meta / Instagram Ads', 'code' => 'meta_ads'],
        ];

        $sourceMap = [];
        foreach ($sources as $s) {
            $sourceMap[$s['code']] = LeadSource::firstOrCreate(['code' => $s['code']], ['name' => $s['name'], 'is_active' => true]);
        }

        // 3. Lead Tags
        $tags = [
            ['name' => 'High Intent', 'color' => '#10B981'],
            ['name' => 'Enterprise', 'color' => '#6366F1'],
            ['name' => 'Laravel Project', 'color' => '#EF4444'],
            ['name' => 'Mobile App', 'color' => '#3B82F6'],
            ['name' => 'E-Commerce', 'color' => '#F59E0B'],
            ['name' => 'Automation', 'color' => '#8B5CF6'],
        ];
        $tagMap = [];
        foreach ($tags as $t) {
            $tagMap[$t['name']] = LeadTag::firstOrCreate(['name' => $t['name']], ['color' => $t['color']]);
        }

        // 4. Companies
        $companies = [
            [
                'name' => 'AcroTech Solutions',
                'domain' => 'acrotech.io',
                'industry' => 'Software & SaaS',
                'size' => '50-200',
                'website' => 'https://acrotech.io',
                'country' => 'United States',
                'city' => 'Austin',
            ],
            [
                'name' => 'Nordic Logistics Group',
                'domain' => 'nordiclogistics.se',
                'industry' => 'Supply Chain',
                'size' => '200-500',
                'website' => 'https://nordiclogistics.se',
                'country' => 'Sweden',
                'city' => 'Stockholm',
            ],
            [
                'name' => 'Vanguard Health Systems',
                'domain' => 'vanguardhealth.org',
                'industry' => 'Healthcare Tech',
                'size' => '500+',
                'website' => 'https://vanguardhealth.org',
                'country' => 'United Kingdom',
                'city' => 'London',
            ],
            [
                'name' => 'OmniRetail Commerce',
                'domain' => 'omniretail.com',
                'industry' => 'E-Commerce & Retail',
                'size' => '20-50',
                'website' => 'https://omniretail.com',
                'country' => 'Canada',
                'city' => 'Toronto',
            ],
            [
                'name' => 'FinScale Capital Partners',
                'domain' => 'finscalecap.com',
                'industry' => 'Fintech',
                'size' => '50-100',
                'website' => 'https://finscalecap.com',
                'country' => 'Germany',
                'city' => 'Frankfurt',
            ],
        ];

        $companyModels = [];
        foreach ($companies as $c) {
            $companyModels[] = Company::firstOrCreate(['domain' => $c['domain']], $c);
        }

        // 5. Suppression List Seed
        SuppressionList::firstOrCreate(
            ['email' => 'optout@unresponsive-corp.com'],
            [
                'reason' => 'unsubscribed',
                'source' => 'email_unsubscribe',
                'notes' => 'Unsubscribed from Q3 outreach',
                'created_at' => now()->subDays(10),
            ]
        );
        SuppressionList::firstOrCreate(
            ['email' => 'bounced@invalid-domain-xyz.com'],
            [
                'reason' => 'hard_bounce',
                'source' => 'bounce_webhook',
                'notes' => 'Mailbox does not exist (550)',
                'created_at' => now()->subDays(15),
            ]
        );

        // 6. Leads (Realistic, multi-status dataset)
        $sampleLeads = [
            [
                'name' => 'Marcus Vance',
                'email' => 'marcus.vance@acrotech.io',
                'phone' => '+1 (512) 849-2100',
                'job_title' => 'Chief Technology Officer',
                'industry' => 'Software & SaaS',
                'country' => 'United States',
                'city' => 'Austin',
                'company_id' => $companyModels[0]->id,
                'lead_source_id' => $sourceMap['linkedin']->id,
                'assigned_user_id' => $manager->id,
                'status' => Lead::STATUS_INTERESTED,
                'consent_basis' => 'legitimate_interest',
                'consent_source' => 'B2B LinkedIn public profile',
                'consent_at' => now()->subDays(12),
                'email_engagement_score' => 85,
                'last_contacted_at' => now()->subDays(2),
                'next_followup_at' => now()->addDays(1),
                'notes' => 'Interested in migrating legacy monolithic backends to Laravel 12 microservices.',
                'tags' => ['High Intent', 'Laravel Project', 'Enterprise'],
            ],
            [
                'name' => 'Elena Lindqvist',
                'email' => 'elena.l@nordiclogistics.se',
                'phone' => '+46 8 123 4567',
                'job_title' => 'VP of Digital Operations',
                'industry' => 'Supply Chain',
                'country' => 'Sweden',
                'city' => 'Stockholm',
                'company_id' => $companyModels[1]->id,
                'lead_source_id' => $sourceMap['cold_email']->id,
                'assigned_user_id' => $salesRep->id,
                'status' => Lead::STATUS_QUALIFIED,
                'consent_basis' => 'legitimate_interest',
                'consent_source' => 'Outreach prospecting list',
                'consent_at' => now()->subDays(20),
                'email_engagement_score' => 70,
                'last_contacted_at' => now()->subDays(3),
                'next_followup_at' => now()->addDays(2),
                'notes' => 'Budget approved for dispatch automation platform.',
                'tags' => ['Automation', 'Enterprise'],
            ],
            [
                'name' => 'Dr. Julian Thorne',
                'email' => 'j.thorne@vanguardhealth.org',
                'phone' => '+44 20 7946 0991',
                'job_title' => 'Head of Medical Informatics',
                'industry' => 'Healthcare Tech',
                'country' => 'United Kingdom',
                'city' => 'London',
                'company_id' => $companyModels[2]->id,
                'lead_source_id' => $sourceMap['website']->id,
                'assigned_user_id' => $manager->id,
                'status' => Lead::STATUS_CONVERTED,
                'consent_basis' => 'explicit_consent',
                'consent_source' => 'Website consultation booking form',
                'consent_at' => now()->subDays(30),
                'email_engagement_score' => 95,
                'last_contacted_at' => now()->subDays(1),
                'next_followup_at' => null,
                'notes' => 'Contract signed for patient portal mobile app development.',
                'tags' => ['Mobile App', 'High Intent'],
            ],
            [
                'name' => 'Chloe Bennett',
                'email' => 'chloe@omniretail.com',
                'phone' => '+1 (416) 555-0143',
                'job_title' => 'E-Commerce Director',
                'industry' => 'E-Commerce & Retail',
                'country' => 'Canada',
                'city' => 'Toronto',
                'company_id' => $companyModels[3]->id,
                'lead_source_id' => $sourceMap['google_seo']->id,
                'assigned_user_id' => $salesRep->id,
                'status' => Lead::STATUS_CONTACTED,
                'consent_basis' => 'explicit_consent',
                'consent_source' => 'Whitepaper download form',
                'consent_at' => now()->subDays(5),
                'email_engagement_score' => 45,
                'last_contacted_at' => now()->subDays(2),
                'next_followup_at' => now()->addDays(3),
                'notes' => 'Sent initial case study on headless commerce store.',
                'tags' => ['E-Commerce'],
            ],
            [
                'name' => 'Lukas Weber',
                'email' => 'lukas.weber@finscalecap.com',
                'phone' => '+49 69 9000 1234',
                'job_title' => 'Managing Partner',
                'industry' => 'Fintech',
                'country' => 'Germany',
                'city' => 'Frankfurt',
                'company_id' => $companyModels[4]->id,
                'lead_source_id' => $sourceMap['referral']->id,
                'assigned_user_id' => $admin->id,
                'status' => Lead::STATUS_NEW,
                'consent_basis' => 'legitimate_interest',
                'consent_source' => 'Client Referral',
                'consent_at' => now()->subDays(1),
                'email_engagement_score' => 10,
                'last_contacted_at' => null,
                'next_followup_at' => now()->addHours(6),
                'notes' => 'Requires secure client onboarding and compliance verification pipeline.',
                'tags' => ['Enterprise', 'High Intent'],
            ],
            [
                'name' => 'Sophia Rodriguez',
                'email' => 's.rodriguez@nextgenretail.us',
                'phone' => '+1 (305) 555-7890',
                'job_title' => 'VP Innovation',
                'industry' => 'E-Commerce & Retail',
                'country' => 'United States',
                'city' => 'Miami',
                'company_id' => $companyModels[3]->id,
                'lead_source_id' => $sourceMap['meta_ads']->id,
                'assigned_user_id' => $salesRep->id,
                'status' => Lead::STATUS_NOT_INTERESTED,
                'consent_basis' => 'explicit_consent',
                'consent_source' => 'Ad lead form',
                'consent_at' => now()->subDays(14),
                'email_engagement_score' => 20,
                'last_contacted_at' => now()->subDays(8),
                'next_followup_at' => null,
                'notes' => 'In-house team selected for the current quarter.',
                'tags' => ['E-Commerce'],
            ],
        ];

        $leadModels = [];
        foreach ($sampleLeads as $leadData) {
            $tagNames = $leadData['tags'] ?? [];
            unset($leadData['tags']);

            $lead = Lead::firstOrCreate(['email' => $leadData['email']], $leadData);
            $leadModels[] = $lead;

            // Attach tags
            $tagIds = [];
            foreach ($tagNames as $tn) {
                if (isset($tagMap[$tn])) {
                    $tagIds[] = $tagMap[$tn]->id;
                }
            }
            $lead->tags()->sync($tagIds);

            // Record initial activity
            $lead->recordActivity('imported', 'Lead added to Nexteck CRM Engine', $admin->id);
            if ($lead->last_contacted_at) {
                $lead->recordActivity('status_change', "Lead status updated to {$lead->status}", $lead->assigned_user_id);
            }

            // Lead Note
            LeadNote::create([
                'lead_id' => $lead->id,
                'user_id' => $lead->assigned_user_id ?? $admin->id,
                'note' => $lead->notes ?? 'Prospected and verified contact details.',
            ]);

            // Lead Task
            if ($lead->next_followup_at) {
                $compName = $lead->company?->name ?? 'Company';
                LeadTask::create([
                    'lead_id' => $lead->id,
                    'assigned_user_id' => $lead->assigned_user_id ?? $salesRep->id,
                    'created_by_id' => $manager->id,
                    'title' => "Follow up with {$lead->name} ({$compName})",
                    'description' => "Review previous discussion points and send updated proposal for {$lead->industry}.",
                    'due_date' => $lead->next_followup_at,
                    'priority' => 'high',
                    'status' => 'pending',
                ]);
            }
        }

        // 7. Email Templates
        $introTemplate = EmailTemplate::firstOrCreate(
            ['name' => 'Nexteck Software & Laravel Modernization Intro'],
            [
                'subject' => 'Accelerating {{company_name}}\'s software roadmap with Nexteck',
                'body_html' => '<p>Hi {{first_name}},</p><p>I noticed the impressive work {{company_name}} is leading in the {{industry}} space. Building scalable digital infrastructure often comes down to choosing the right engineering partners.</p><p>At <strong>Nexteck</strong>, we engineer high-performance web applications, custom software platforms, and mobile apps with modern Laravel and cloud-native architectures.</p><p>Are you open to a brief 10-minute discovery call next Tuesday?</p><p>Best regards,<br><strong>Nexteck Engineering Team</strong></p><p style="font-size:11px;color:#9ca3af;">If you prefer not to receive future emails, you can <a href="{{unsubscribe_url}}">unsubscribe here</a>.</p>',
                'body_plain' => "Hi {{first_name}},\n\nI noticed {{company_name}}'s work in {{industry}}. At Nexteck, we architect high-performance Laravel web applications and custom software.\n\nAre you open to a quick 10-minute chat next week?\n\nUnsubscribe: {{unsubscribe_url}}",
                'category' => 'cold_outreach',
                'variables_schema' => ['first_name', 'company_name', 'industry', 'service_name', 'unsubscribe_url'],
                'created_by_id' => $admin->id,
            ]
        );

        $caseStudyTemplate = EmailTemplate::firstOrCreate(
            ['name' => 'Nexteck Case Study & Architecture Value'],
            [
                'subject' => 'How we reduced cloud latency by 45% for enterprise platforms',
                'body_html' => '<p>Hi {{first_name}},</p><p>Following up on my note regarding {{company_name}}. I wanted to share a brief case study on how we recently helped an enterprise client rebuild their core dispatch system using Laravel queues and modern API integrations.</p><p>Would this be relevant for your team this quarter?</p><p>Best,<br>Nexteck Lead Solutions</p><p style="font-size:11px;color:#9ca3af;"><a href="{{unsubscribe_url}}">Unsubscribe</a></p>',
                'body_plain' => "Hi {{first_name}},\n\nFollowing up on my note regarding {{company_name}}. We recently helped an enterprise client reduce latency by 45% with modern Laravel architectures.\n\nWould this be relevant for you?\n\nUnsubscribe: {{unsubscribe_url}}",
                'category' => 'follow_up',
                'variables_schema' => ['first_name', 'company_name', 'unsubscribe_url'],
                'created_by_id' => $manager->id,
            ]
        );

        // 8. Follow-up Sequence Engine (Phase A, B, C as in Section 10)
        $sequence = FollowupSequence::firstOrCreate(
            ['name' => 'Enterprise Software Development Outreach Sequence'],
            [
                'description' => 'Multi-phase automated outreach: Phase A weekly (Steps 1-4), Phase B twice-monthly, Phase C monthly.',
                'status' => 'active',
                'send_window_start' => '09:00:00',
                'send_window_end' => '17:00:00',
                'timezone' => 'UTC',
                'exclude_weekends' => true,
                'exclude_holidays' => true,
                'created_by_id' => $admin->id,
            ]
        );

        // Steps for sequence
        $steps = [
            ['step_order' => 1, 'phase' => 'phase_a', 'delay_days' => 0, 'subject' => 'Accelerating {{company_name}}\'s software roadmap', 'body_template' => 'Initial outreach email template'],
            ['step_order' => 2, 'phase' => 'phase_a', 'delay_days' => 7, 'subject' => 'Quick question regarding engineering capacity at {{company_name}}', 'body_template' => 'Weekly follow-up #1'],
            ['step_order' => 3, 'phase' => 'phase_a', 'delay_days' => 7, 'subject' => 'Case study: scalable Laravel architectures for {{industry}}', 'body_template' => 'Weekly follow-up #2 with case study'],
            ['step_order' => 4, 'phase' => 'phase_a', 'delay_days' => 7, 'subject' => 'Ideas for {{company_name}}\'s digital pipeline', 'body_template' => 'Weekly follow-up #3'],
            ['step_order' => 5, 'phase' => 'phase_b', 'delay_days' => 14, 'subject' => 'Checking in on {{company_name}} mid-quarter priorities', 'body_template' => 'Twice-monthly check-in #1'],
            ['step_order' => 6, 'phase' => 'phase_b', 'delay_days' => 14, 'subject' => 'Nexteck custom automation capabilities', 'body_template' => 'Twice-monthly check-in #2'],
            ['step_order' => 7, 'phase' => 'phase_c', 'delay_days' => 30, 'subject' => 'Staying connected with {{company_name}}', 'body_template' => 'Monthly touchpoint'],
        ];

        foreach ($steps as $st) {
            SequenceStep::firstOrCreate(
                ['followup_sequence_id' => $sequence->id, 'step_order' => $st['step_order']],
                array_merge($st, ['email_template_id' => $introTemplate->id])
            );
        }

        // Enroll active leads into sequence
        if (isset($leadModels[1])) {
            SequenceEnrollment::firstOrCreate(
                ['followup_sequence_id' => $sequence->id, 'lead_id' => $leadModels[1]->id],
                [
                    'current_step_id' => SequenceStep::where('followup_sequence_id', $sequence->id)->where('step_order', 2)->first()?->id,
                    'status' => 'active',
                    'next_run_at' => now()->addDays(4),
                    'last_sent_at' => now()->subDays(3),
                ]
            );
        }

        // 9. Email Campaign
        $campaign = EmailCampaign::firstOrCreate(
            ['name' => 'Q4 Enterprise Digital Transformation Campaign'],
            [
                'email_template_id' => $introTemplate->id,
                'status' => 'completed',
                'scheduled_at' => now()->subDays(7),
                'completed_at' => now()->subDays(7)->addHours(2),
                'target_segment_filter' => ['industry' => 'Software & SaaS, Supply Chain, Healthcare Tech'],
                'subject' => 'Modernizing digital platforms with Nexteck',
                'from_name' => 'Nexteck Outreach',
                'from_email' => 'outreach@nexteck.com',
                'reply_to' => 'replies@nexteck.com',
                'total_recipients' => 45,
                'sent_count' => 45,
                'delivered_count' => 43,
                'bounced_count' => 1,
                'complained_count' => 0,
                'reply_count' => 8,
                'unsubscribe_count' => 1,
                'created_by_id' => $admin->id,
            ]
        );

        // Inbound Reply & Handover
        $targetLead = $leadModels[0]; // Marcus Vance
        $message = EmailMessage::firstOrCreate(
            ['subject' => 'Re: Accelerating AcroTech Solutions roadmap with Nexteck'],
            [
                'lead_id' => $targetLead->id,
                'campaign_id' => $campaign->id,
                'direction' => 'inbound',
                'body_text' => "Hi team,\n\nThanks for reaching out. We are actually exploring a rewrite of our billing backend to Laravel 12 next month. Do you have experience with Stripe billing and high-throughput webhooks? Let's talk next Thursday at 2 PM EST.\n\nBest,\nMarcus Vance",
                'from_email' => $targetLead->email,
                'to_email' => 'replies@nexteck.com',
            ]
        );

        $reply = InboundReply::firstOrCreate(
            ['from_email' => $targetLead->email],
            [
                'lead_id' => $targetLead->id,
                'email_message_id' => $message->id,
                'campaign_id' => $campaign->id,
                'subject' => 'Re: Accelerating AcroTech Solutions roadmap with Nexteck',
                'body' => $message->body_text,
                'ai_classification' => 'interested',
                'ai_confidence' => 0.94,
                'ai_summary' => 'Lead expressed high interest in Laravel 12 billing rewrite with Stripe integration. Requested call next Thursday at 2 PM EST.',
                'is_reviewed' => false,
                'status' => 'assigned',
                'received_at' => now()->subHours(5),
            ]
        );

        ManagerHandover::firstOrCreate(
            ['lead_id' => $targetLead->id],
            [
                'inbound_reply_id' => $reply->id,
                'assigned_manager_id' => $manager->id,
                'handover_reason' => 'positive_reply',
                'urgency' => 'urgent',
                'status' => 'pending',
                'handoff_notes' => 'Hot prospect Marcus Vance (CTO) requested meeting for Laravel 12 Stripe integration.',
            ]
        );

        // 10. Social Accounts & Posts
        $linkedinAccount = SocialAccount::firstOrCreate(
            ['platform' => 'linkedin', 'account_id' => 'nexteck-technologies-page'],
            [
                'account_name' => 'Nexteck Technologies (LinkedIn)',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Nexteck+Tech&background=0A66C2&color=fff',
                'access_token' => 'mock_encrypted_oauth_token_linkedin_prod_access',
                'refresh_token' => 'mock_encrypted_oauth_token_linkedin_prod_refresh',
                'token_expires_at' => now()->addDays(60),
                'is_active' => true,
                'metadata' => ['followers' => 14200, 'vanity_name' => 'nexteck'],
            ]
        );

        $fbAccount = SocialAccount::firstOrCreate(
            ['platform' => 'facebook', 'account_id' => 'nexteck-official-facebook'],
            [
                'account_name' => 'Nexteck Official Page (Facebook)',
                'avatar_url' => 'https://ui-avatars.com/api/?name=Nexteck+FB&background=1877F2&color=fff',
                'access_token' => 'mock_encrypted_oauth_token_facebook_prod_access',
                'token_expires_at' => now()->addDays(45),
                'is_active' => true,
                'metadata' => ['page_likes' => 8400],
            ]
        );

        $post = SocialPost::firstOrCreate(
            ['title' => 'Why Modern Teams Choose Laravel 12 for High-Scale Applications'],
            [
                'content' => "Building scalable enterprise applications doesn't have to mean cumbersome microservices complexity. Discover how Laravel 12 delivers asynchronous processing, real-time queues, and robust APIs.\n\nRead our architectural breakdown or speak to our solutions team today.\n\n#Laravel #SoftwareArchitecture #WebDevelopment #Nexteck",
                'target_platforms' => ['linkedin', 'facebook'],
                'status' => 'published',
                'scheduled_at' => now()->subDays(1),
                'published_at' => now()->subDays(1),
                'approved_by_id' => $manager->id,
                'created_by_id' => $admin->id,
                'hashtags' => ['#Laravel', '#SoftwareArchitecture', '#Nexteck'],
            ]
        );

        SocialPostAccount::firstOrCreate(
            ['social_post_id' => $post->id, 'social_account_id' => $linkedinAccount->id],
            [
                'platform_post_id' => 'urn:li:share:7189201948',
                'status' => 'published',
                'engagement_metrics' => ['likes' => 124, 'comments' => 18, 'shares' => 9, 'impressions' => 2850],
                'published_at' => now()->subDays(1),
            ]
        );

        SocialComment::firstOrCreate(
            ['comment_id' => 'li_c_998120'],
            [
                'social_account_id' => $linkedinAccount->id,
                'platform_post_id' => 'urn:li:share:7189201948',
                'author_name' => 'Robert Sterling',
                'author_id' => 'in_r_sterling',
                'content' => 'Great overview! Does your team also handle migrations from legacy Symfony or CodeIgniter setups?',
                'sentiment' => 'lead_inquiry',
                'status' => 'unread',
                'commented_at' => now()->subHours(3),
            ]
        );

        // 11. Content Calendar (Weekly planner)
        $calendarItems = [
            [
                'title' => 'LinkedIn Insight: 5 Signs Your Web App Architecture Needs Modernization',
                'scheduled_date' => now()->startOfWeek()->toDateString(),
                'platform' => 'linkedin',
                'content_type' => 'post',
                'status' => 'published',
                'target_audience' => 'CTOs, Engineering Leads, Founders',
                'industry' => 'B2B Tech',
                'campaign_goal' => 'Thought leadership & Inbound leads',
            ],
            [
                'title' => 'Instagram Carousel: Before and After Custom ERP System Transformation',
                'scheduled_date' => now()->startOfWeek()->addDays(1)->toDateString(),
                'platform' => 'instagram',
                'content_type' => 'carousel',
                'status' => 'scheduled',
                'target_audience' => 'Operations Managers & Business Owners',
                'industry' => 'Logistics & Manufacturing',
                'campaign_goal' => 'Brand showcase',
            ],
            [
                'title' => 'Facebook Case Highlight: How Nexteck Scaled an E-Commerce Platform to 100k Daily Orders',
                'scheduled_date' => now()->startOfWeek()->addDays(2)->toDateString(),
                'platform' => 'facebook',
                'content_type' => 'post',
                'status' => 'approved',
                'target_audience' => 'E-Commerce Directors',
                'industry' => 'Retail',
                'campaign_goal' => 'Conversion',
            ],
            [
                'title' => 'TikTok / Short-Form Video Hook: Is your database secretly holding back your web app?',
                'scheduled_date' => now()->startOfWeek()->addDays(3)->toDateString(),
                'platform' => 'tiktok',
                'content_type' => 'video_script',
                'status' => 'planned',
                'target_audience' => 'Developers & Tech Entrepreneurs',
                'industry' => 'Tech',
                'campaign_goal' => 'Engagement & Reach',
            ],
            [
                'title' => 'Blog Publication: Comprehensive Guide to Answer Engine Optimization (AEO) in 2026',
                'scheduled_date' => now()->startOfWeek()->addDays(4)->toDateString(),
                'platform' => 'blog',
                'content_type' => 'blog',
                'status' => 'draft',
                'target_audience' => 'Marketing leaders & CMOs',
                'industry' => 'Digital Marketing',
                'campaign_goal' => 'Organic Search Traffic',
            ],
        ];

        foreach ($calendarItems as $ci) {
            ContentCalendar::firstOrCreate(
                ['title' => $ci['title']],
                array_merge($ci, [
                    'assigned_user_id' => $salesRep->id,
                    'scheduled_time' => '10:00:00',
                ])
            );
        }

        // 12. Blog CMS, SEO, AEO & GEO
        $categoryDev = BlogCategory::firstOrCreate(
            ['slug' => 'software-development'],
            ['name' => 'Software Development', 'description' => 'Architectural patterns, Laravel, APIs, and cloud systems.']
        );
        $categoryAeo = BlogCategory::firstOrCreate(
            ['slug' => 'ai-and-seo-optimization'],
            ['name' => 'AI & Search Optimization', 'description' => 'AEO, GEO, answer engines, and LLM discoverability.']
        );

        $tagLaravel = BlogTag::firstOrCreate(['slug' => 'laravel'], ['name' => 'Laravel']);
        $tagAeo = BlogTag::firstOrCreate(['slug' => 'aeo-optimization'], ['name' => 'AEO']);
        $tagEnterprise = BlogTag::firstOrCreate(['slug' => 'enterprise-tech'], ['name' => 'Enterprise Tech']);

        $blog1 = Blog::firstOrCreate(
            ['slug' => 'building-scalable-applications-with-laravel-12-best-practices'],
            [
                'title' => 'Building Scalable Enterprise Applications with Laravel: Architectural Best Practices',
                'summary' => 'Explore battle-tested architectural strategies for developing high-throughput web applications with Laravel, queues, caching, and clean domain design.',
                'body' => '<h2>Why Modern Enterprise Engineering Chooses Laravel</h2><p>As enterprise software demands increase, choosing a maintainable, high-performance foundation is essential. Laravel provides an expressive syntax combined with enterprise-grade capabilities like asynchronous job queues, database connection pooling, and built-in rate limiting.</p><h3>Key Architectural Decisions</h3><ul><li><strong>Decoupled Service Layers:</strong> Separate business domain logic from HTTP controllers.</li><li><strong>Queue-Driven Pipelines:</strong> Offload email campaigns, third-party API synchronization, and data transformations to background workers.</li><li><strong>Idempotent Operations:</strong> Ensure webhooks and scheduled sync tasks can safely retry without side effects.</li></ul><p>At Nexteck, our engineers specialize in architecting scalable platforms that grow seamlessly alongside your user base.</p>',
                'category_id' => $categoryDev->id,
                'author_id' => $admin->id,
                'status' => 'published',
                'published_at' => now()->subDays(5),
                'primary_keyword' => 'Laravel enterprise application architecture',
                'secondary_keywords' => ['scalable web apps', 'Laravel development agency', 'high throughput backend'],
                'search_intent' => 'commercial',
                'meta_title' => 'Building Scalable Enterprise Applications with Laravel | Nexteck',
                'meta_description' => 'Learn proven architectural patterns for high-throughput enterprise software with Laravel 12, queues, and decoupled domain services from Nexteck architects.',
                'canonical_url' => config('app.url') . '/blog/building-scalable-applications-with-laravel-12-best-practices',
                'robots_directive' => 'index, follow',
                'og_title' => 'Building Scalable Enterprise Applications with Laravel',
                'og_description' => 'Explore battle-tested strategies for scalable web applications built by Nexteck engineers.',
                'aeo_geo_faq' => [
                    [
                        'question' => 'Is Laravel suitable for high-concurrency enterprise applications?',
                        'answer' => 'Yes. When combined with Redis caching, database connection pooling, and background queue workers (via Horizon or Redis), Laravel easily supports thousands of requests per second with sub-100ms response times.'
                    ],
                    [
                        'question' => 'How does Nexteck architect Laravel web applications for longevity?',
                        'answer' => 'Nexteck implements strict separation of concerns through domain services, repository patterns where beneficial, rigorous automated test suites, and Dockerized CI/CD deployment pipelines.'
                    ]
                ],
                'entities_and_definitions' => 'Entities: Laravel Framework, Domain-Driven Design (DDD), Asynchronous Queues, Nexteck Engineering Team, MySQL, Redis.',
                'seo_score' => 95,
                'revision_number' => 1,
            ]
        );
        $blog1->tags()->sync([$tagLaravel->id, $tagEnterprise->id]);

        // 13. Integration Settings
        $integrations = [
            ['service_name' => 'smtp_email', 'health_status' => 'healthy', 'settings' => ['host' => '127.0.0.1', 'port' => 2525, 'encryption' => 'tls', 'from' => 'outreach@nexteck.com']],
            ['service_name' => 'gemini_ai', 'health_status' => 'healthy', 'settings' => ['provider' => 'Google Gemini Flash', 'status' => 'connected', 'rpm_limit' => 60]],
            ['service_name' => 'linkedin', 'health_status' => 'healthy', 'settings' => ['account' => 'Nexteck Technologies', 'scopes' => 'w_member_social,r_organization_social']],
            ['service_name' => 'facebook', 'health_status' => 'healthy', 'settings' => ['page' => 'Nexteck Official', 'status' => 'connected']],
            ['service_name' => 'instagram', 'health_status' => 'healthy', 'settings' => ['handle' => '@nexteck_tech', 'status' => 'connected']],
            ['service_name' => 'tiktok', 'health_status' => 'healthy', 'settings' => ['handle' => '@nexteck_official', 'status' => 'connected']],
        ];

        foreach ($integrations as $ig) {
            IntegrationSetting::firstOrCreate(
                ['service_name' => $ig['service_name']],
                [
                    'health_status' => $ig['health_status'],
                    'settings' => $ig['settings'],
                    'is_active' => true,
                    'last_health_check_at' => now(),
                ]
            );
        }
    }
}
