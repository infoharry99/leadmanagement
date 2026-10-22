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
        ]);
    }
}
