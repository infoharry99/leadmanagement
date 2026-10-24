<?php

use App\Http\Controllers\AiStudioController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BlogController;
use App\Http\Controllers\ContentCalendarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeploymentController;
use App\Http\Controllers\EmailCampaignController;
use App\Http\Controllers\EmailTemplateController;
use App\Http\Controllers\FollowupSequenceController;
use App\Http\Controllers\LeadController;
use App\Http\Controllers\LeadImportExportController;
use App\Http\Controllers\LegalController;
use App\Http\Controllers\ManagerHandoverController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\ReportsController;
use App\Http\Controllers\SocialConfigController;
use App\Http\Controllers\SocialConnectionController;
use App\Http\Controllers\SocialPostController;
use App\Http\Controllers\SocialPublishingController;
use App\Http\Controllers\TaskManagerController;
use App\Http\Controllers\UnifiedInboxController;
use App\Http\Controllers\UnsubscribeController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes & Marketing
|--------------------------------------------------------------------------
*/
Route::get('/', function () {
    return auth()->check() ? redirect()->route('social.posts.dashboard') : redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Unsubscribe & Preference Center
Route::get('/unsubscribe/{token}', [UnsubscribeController::class, 'show'])->name('unsubscribe.show');
Route::post('/unsubscribe/{token}', [UnsubscribeController::class, 'process'])->name('unsubscribe.process');

// Public Legal & Meta Compliance Pages
Route::get('/privacy-policy', [LegalController::class, 'privacyPolicy'])->name('legal.privacy');
Route::get('/terms-of-service', [LegalController::class, 'termsOfService'])->name('legal.terms');
Route::get('/data-deletion', [LegalController::class, 'dataDeletionInstructions'])->name('legal.data_deletion');
Route::post('/data-deletion/callback', [LegalController::class, 'dataDeletionCallback'])->name('legal.data_deletion_callback');
Route::get('/data-deletion/status', [LegalController::class, 'dataDeletionStatus'])->name('legal.data_deletion_status');

// Public Blog & SEO/AEO/GEO Endpoints
Route::get('/blog', [PublicBlogController::class, 'index'])->name('public.blog.index');
Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->name('public.blog.show');
Route::get('/sitemap.xml', [PublicBlogController::class, 'sitemap'])->name('public.sitemap');
Route::get('/llms.txt', [PublicBlogController::class, 'llmsTxt'])->name('public.llms_txt');

// Public Social OAuth Callback & Webhook Verification (Accessible to Meta/LinkedIn/TikTok servers)
Route::match(['get', 'post'], '/social/callback/{platform}', [SocialConnectionController::class, 'oauthCallback'])
    ->name('social.oauth.callback');

Route::match(['get', 'post'], '/webhooks/meta', [SocialConnectionController::class, 'handleMetaWebhook'])
    ->name('webhooks.meta');

// Shared Hosting Deployment & Database Migration Runner (bypasses session/database middleware)
Route::get('/deploy', [DeploymentController::class, 'handle'])
    ->withoutMiddleware('web')
    ->name('deploy.handle');

/*
|--------------------------------------------------------------------------
| Protected Nexteck Lead Engine Routes
|--------------------------------------------------------------------------
*/
Route::middleware(['auth'])->group(function () {

    // Main Admin Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Lead CRM
    Route::prefix('leads')->name('leads.')->group(function () {
        Route::get('/import', [LeadImportExportController::class, 'showImport'])->name('import');
        Route::post('/import', [LeadImportExportController::class, 'processImport'])->name('import.process');
        Route::get('/export', [LeadImportExportController::class, 'export'])->name('export');
        Route::post('/bulk-action', [LeadController::class, 'bulkAction'])->name('bulk-action');
        Route::post('/{lead}/archive', [LeadController::class, 'archive'])->name('archive');
        Route::post('/{id}/restore', [LeadController::class, 'restore'])->name('restore');
        Route::post('/{lead}/notes', [LeadController::class, 'addNote'])->name('notes.store');
        Route::post('/{lead}/tasks', [LeadController::class, 'addTask'])->name('tasks.store');
    });
    Route::resource('leads', LeadController::class);

    // Email Marketing & Campaigns
    Route::prefix('campaigns')->name('campaigns.')->group(function () {
        Route::post('/{campaign}/dispatch', [EmailCampaignController::class, 'dispatchCampaign'])->name('dispatch');
        Route::post('/{campaign}/send-now', [EmailCampaignController::class, 'sendNow'])->name('send-now');
        Route::post('/{campaign}/pause', [EmailCampaignController::class, 'pause'])->name('pause');
        Route::post('/{campaign}/cancel', [EmailCampaignController::class, 'cancel'])->name('cancel');
    });
    Route::resource('campaigns', EmailCampaignController::class);

    // Email Templates
    Route::get('/templates/{template}/preview', [EmailTemplateController::class, 'preview'])->name('templates.preview');
    Route::resource('templates', EmailTemplateController::class);

    // Follow-up Sequence Engine
    Route::prefix('sequences')->name('sequences.')->group(function () {
        Route::post('/{sequence}/enroll', [FollowupSequenceController::class, 'enroll'])->name('enroll');
        Route::post('/enrollments/{enrollment}/pause', [FollowupSequenceController::class, 'pauseEnrollment'])->name('enrollments.pause');
        Route::post('/enrollments/{enrollment}/resume', [FollowupSequenceController::class, 'resumeEnrollment'])->name('enrollments.resume');
    });
    Route::resource('sequences', FollowupSequenceController::class);

    // Unified Engagement Inbox & Reply Handling
    Route::prefix('inbox')->name('inbox.')->group(function () {
        Route::get('/', [UnifiedInboxController::class, 'index'])->name('index');
        Route::get('/reply/{reply}', [UnifiedInboxController::class, 'showReply'])->name('show');
        Route::post('/reply/{reply}/assign', [UnifiedInboxController::class, 'assignReply'])->name('assign');
        Route::post('/reply/{reply}/resolve', [UnifiedInboxController::class, 'resolveReply'])->name('resolve');
        Route::post('/simulate-inbound', [UnifiedInboxController::class, 'simulateInbound'])->name('simulate');
        Route::post('/comments/{comment}/reply', [UnifiedInboxController::class, 'replyToComment'])->name('comment.reply');
    });

    // Manager Handover Queue
    Route::prefix('handovers')->name('handovers.')->group(function () {
        Route::get('/', [ManagerHandoverController::class, 'index'])->name('index');
        Route::post('/{handover}/acknowledge', [ManagerHandoverController::class, 'acknowledge'])->name('acknowledge');
        Route::post('/{handover}/reassign', [ManagerHandoverController::class, 'reassign'])->name('reassign');
        Route::post('/{handover}/update-status', [ManagerHandoverController::class, 'updateStatus'])->name('update-status');
    });

    // Tasks & Reminders
    Route::prefix('tasks')->name('tasks.')->group(function () {
        Route::get('/', [TaskManagerController::class, 'index'])->name('index');
        Route::post('/{task}/complete', [TaskManagerController::class, 'complete'])->name('complete');
        Route::post('/{task}/status', [TaskManagerController::class, 'updateStatus'])->name('status');
    });

    // Weekly Content Calendar
    Route::prefix('calendar')->name('calendar.')->group(function () {
        Route::get('/', [ContentCalendarController::class, 'index'])->name('index');
        Route::get('/create', [ContentCalendarController::class, 'create'])->name('create');
        Route::post('/', [ContentCalendarController::class, 'store'])->name('store');
        Route::post('/{calendar}/status', [ContentCalendarController::class, 'updateStatus'])->name('status');
    });

    // AI Content Studio
    Route::prefix('ai-studio')->name('ai.')->group(function () {
        Route::get('/', [AiStudioController::class, 'index'])->name('index');
        Route::post('/generate', [AiStudioController::class, 'generate'])->name('generate');
        Route::get('/generations/{generation}', [AiStudioController::class, 'show'])->name('show');
        Route::post('/generations/{generation}/save-to-calendar', [AiStudioController::class, 'saveToCalendar'])->name('save-to-calendar');
        Route::post('/save-to-social', [AiStudioController::class, 'saveToSocial'])->name('save-to-social');
    });

    // Simple Social Media Post Scheduler (Clean, Modern, Dedicated Dashboard)
    Route::get('/social-scheduler', [SocialPostController::class, 'dashboard'])->name('social.posts.dashboard');

    Route::prefix('social-scheduler/posts')->name('social.posts.')->group(function () {
        Route::get('/', [SocialPostController::class, 'index'])->name('index');
        Route::get('/create', [SocialPostController::class, 'create'])->name('create');
        Route::post('/', [SocialPostController::class, 'store'])->name('store');
        Route::get('/{post}', [SocialPostController::class, 'show'])->name('show');
        Route::post('/{post}/retry', [SocialPostController::class, 'retry'])->name('retry');
        Route::delete('/{post}', [SocialPostController::class, 'destroy'])->name('destroy');
    });

    // Settings → Social Media Connections (Instagram, Facebook, LinkedIn, TikTok)
    Route::prefix('social-scheduler/connections')->name('social.connections.')->group(function () {
        Route::get('/', [SocialConnectionController::class, 'index'])->name('index');
        Route::post('/manual', [SocialConnectionController::class, 'connectManual'])->name('manual');
        Route::post('/{account}/disconnect', [SocialConnectionController::class, 'disconnect'])->name('disconnect');
    });

    // Official OAuth Redirect (Triggers flow from dashboard)
    Route::get('/social/oauth/{platform}/redirect', [SocialConnectionController::class, 'oauthRedirect'])->name('social.oauth.redirect');

    // Settings → API Configuration & Developer Guides
    Route::prefix('social-scheduler/config')->name('social.config.')->group(function () {
        Route::get('/', [SocialConfigController::class, 'index'])->name('index');
        Route::post('/', [SocialConfigController::class, 'update'])->name('update');
    });

    // Social Media Publishing & Engagement (Mapped to Simple Post Scheduler)
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('/', [SocialPostController::class, 'dashboard'])->name('index');
        Route::get('/create', [SocialPostController::class, 'create'])->name('create');
        Route::post('/', [SocialPostController::class, 'store'])->name('store');
        Route::post('/posts/{post}/retry', [SocialPostController::class, 'retry'])->name('retry');
        Route::post('/accounts/connect', [SocialConnectionController::class, 'connectManual'])->name('connect');
    });

    // Blog CMS & SEO/AEO/GEO
    Route::resource('blogs', BlogController::class);

    // Reports & Analytics
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
});
