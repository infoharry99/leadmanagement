<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role', 30)->default('sales_rep')->after('email'); // admin, sales_manager, sales_rep
            $table->string('phone', 50)->nullable()->after('role');
            $table->string('status', 20)->default('active')->after('phone'); // active, inactive
            $table->string('avatar')->nullable()->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'phone', 'status', 'avatar']);
        });
    }
};
