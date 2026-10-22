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
use App\Http\Controllers\ManagerHandoverController;
use App\Http\Controllers\PublicBlogController;
use App\Http\Controllers\ReportsController;
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
    return auth()->check() ? redirect()->route('dashboard') : redirect()->route('login');
});

// Authentication
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login']);
Route::post('/quick-login', [AuthController::class, 'quickLogin'])->name('quick-login');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Public Unsubscribe & Preference Center
Route::get('/unsubscribe/{token}', [UnsubscribeController::class, 'show'])->name('unsubscribe.show');
Route::post('/unsubscribe/{token}', [UnsubscribeController::class, 'process'])->name('unsubscribe.process');

// Public Blog & SEO/AEO/GEO Endpoints
Route::get('/blog', [PublicBlogController::class, 'index'])->name('public.blog.index');
Route::get('/blog/{slug}', [PublicBlogController::class, 'show'])->name('public.blog.show');
Route::get('/sitemap.xml', [PublicBlogController::class, 'sitemap'])->name('public.sitemap');
Route::get('/llms.txt', [PublicBlogController::class, 'llmsTxt'])->name('public.llms_txt');

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

    // Social Media Publishing & Engagement
    Route::prefix('social')->name('social.')->group(function () {
        Route::get('/', [SocialPublishingController::class, 'index'])->name('index');
        Route::get('/create', [SocialPublishingController::class, 'create'])->name('create');
        Route::post('/', [SocialPublishingController::class, 'store'])->name('store');
        Route::post('/posts/{post}/retry', [SocialPublishingController::class, 'retry'])->name('retry');
        Route::post('/accounts/connect', [SocialPublishingController::class, 'connectAccount'])->name('connect');
    });

    // Blog CMS & SEO/AEO/GEO
    Route::resource('blogs', BlogController::class);

    // Reports & Analytics
    Route::get('/reports', [ReportsController::class, 'index'])->name('reports.index');
});
