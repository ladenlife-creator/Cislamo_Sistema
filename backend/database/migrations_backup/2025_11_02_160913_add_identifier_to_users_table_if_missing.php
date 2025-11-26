<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Check if identifier column already exists
        if (!Schema::hasColumn('users', 'identifier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('identifier')->unique()->nullable()->after('name');
            });

            // Migrate existing email data to identifier if email column exists
            if (Schema::hasColumn('users', 'email')) {
                DB::statement('UPDATE users SET identifier = email WHERE identifier IS NULL AND email IS NOT NULL');
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('users', 'identifier')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropColumn('identifier');
            });
        }
    }
};
