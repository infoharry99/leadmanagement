<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Throwable;

class DeploymentController extends Controller
{
    /**
     * Shared Hosting Database Migration & Seeding Tool
     * Allows running migrations, seeders, and storage link via browser.
     */
    public function handle(Request $request)
    {
        // Force file/array drivers for deployment requests to prevent touching non-existent tables
        config([
            'session.driver' => 'file',
            'cache.default' => 'file',
        ]);

        // Security token check (can be set in .env as DEPLOY_TOKEN)
        $expectedToken = env('DEPLOY_TOKEN', 'nexteck2026deploy');
        $providedToken = $request->query('token');

        if ($providedToken !== $expectedToken) {
            return response(
                '<div style="font-family:sans-serif;padding:40px;text-align:center;color:#ef4444;">'
                . '<h2>403 Forbidden: Invalid or Missing Deploy Token</h2>'
                . '<p>Please provide the correct security token in the URL: <code>?token=' . htmlspecialchars($expectedToken) . '</code></p>'
                . '</div>',
                403
            );
        }

        $action = $request->query('action', 'status');
        $output = '';
        $status = 'success';
        $message = '';

        try {
            switch ($action) {
                case 'migrate':
                    Artisan::call('migrate', ['--force' => true]);
                    $output = Artisan::output();
                    $message = 'Database migrations executed successfully!';
                    break;

                case 'seed':
                    Artisan::call('db:seed', ['--force' => true]);
                    $output = Artisan::output();
                    $message = 'Database seeder executed successfully!';
                    break;

                case 'migrate-seed':
                    Artisan::call('migrate', ['--force' => true]);
                    $output .= "=== MIGRATIONS ===\n" . Artisan::output() . "\n";
                    Artisan::call('db:seed', ['--force' => true]);
                    $output .= "=== SEEDING ===\n" . Artisan::output();
                    $message = 'Database migrated & seeded successfully with demo records!';
                    break;

                case 'storage-link':
                    Artisan::call('storage:link');
                    $output = Artisan::output();
                    $message = 'Storage symbolic link created successfully!';
                    break;

                case 'optimize-clear':
                    Artisan::call('optimize:clear');
                    $output = Artisan::output();
                    $message = 'Application cache, routes, and views cleared successfully!';
                    break;

                case 'process-queue':
                    Artisan::call('queue:work', ['--stop-when-empty' => true]);
                    $output = Artisan::output() ?: 'Queue worker finished processing pending jobs.';
                    $message = 'Pending queue jobs and campaign emails processed successfully!';
                    break;

                case 'test-email':
                    $mailer = config('mail.default');
                    $to = $request->query('to', 'infoharry99@gmail.com');
                    $fromAddress = config('mail.from.address');
                    $fromName = config('mail.from.name', 'Nexteck Test');
                    $smtpHost = config('mail.mailers.smtp.host');
                    $smtpPort = config('mail.mailers.smtp.port');

                    if ($mailer === 'log') {
                        $status = 'error';
                        $message = "CRITICAL: MAIL_MAILER is currently set to 'log' in active config! Emails are written to storage/logs/laravel.log and NOT sent over the internet.";
                        $output = "Current Mailer: log\n"
                            . "To fix this:\n"
                            . "1. Edit .env and change: MAIL_MAILER=smtp\n"
                            . "2. Click 'Clear App & View Cache (optimize:clear)' button on this page.\n"
                            . "3. Re-test email delivery.";
                        break;
                    }

                    \Illuminate\Support\Facades\Mail::raw(
                        "Hello Hariom,\n\nThis is a LIVE SMTP delivery confirmation from Nexteck Lead Engine!\nSent at: " . now()->toDateTimeString() . "\n\nServer Mailer: {$mailer}\nSMTP Host: {$smtpHost}:{$smtpPort}\nFrom: {$fromAddress}\nTo: {$to}\n\nIf you see this, your SMTP delivery is working perfectly!",
                        function ($msg) use ($to, $fromAddress, $fromName) {
                            $msg->to($to)
                                ->from($fromAddress, $fromName)
                                ->subject('Nexteck CRM - Live SMTP Delivery Test (' . now()->format('H:i:s') . ')');
                        }
                    );

                    $message = "Test email sent to {$to} via {$mailer} ({$smtpHost}:{$smtpPort})!";
                    $output = "SUCCESS!\n"
                        . "Mailer: {$mailer}\n"
                        . "Host: {$smtpHost}:{$smtpPort}\n"
                        . "From: {$fromAddress}\n"
                        . "To: {$to}\n\n"
                        . "Check your Gmail inbox and Spam folder now.";
                    break;

                case 'optimize':
                    Artisan::call('config:cache');
                    $output .= Artisan::output();
                    Artisan::call('route:cache');
                    $output .= Artisan::output();
                    Artisan::call('view:cache');
                    $output .= Artisan::output();
                    $message = 'Config, routes, and views cached successfully!';
                    break;

                default:
                    $message = 'Ready. Select an action below to execute.';
                    $output = 'Connected to database: ' . config('database.connections.mysql.database') . ' on ' . config('database.connections.mysql.host');
                    break;
            }
        } catch (Throwable $e) {
            $status = 'error';
            $message = 'Execution failed: ' . $e->getMessage();
            $output = $e->getTraceAsString();
        }

        // Check current DB tables
        $tables = [];
        $dbConnected = false;
        try {
            $tables = Schema::getTableListing();
            $dbConnected = true;
        } catch (Throwable $e) {
            $dbConnected = false;
        }

        return view('deployment.index', [
            'token' => $providedToken,
            'action' => $action,
            'status' => $status,
            'message' => $message,
            'output' => $output,
            'dbConnected' => $dbConnected,
            'tablesCount' => count($tables),
            'databaseName' => config('database.connections.mysql.database'),
            'activeMailer' => config('mail.default'),
            'fromAddress' => config('mail.from.address'),
            'smtpHost' => config('mail.mailers.smtp.host'),
        ]);
    }
}
