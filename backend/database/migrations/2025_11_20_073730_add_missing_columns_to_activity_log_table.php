<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $connection = config('activitylog.database_connection', null);
        $tableName = config('activitylog.table_name', 'activity_log');
        $schema = Schema::connection($connection);
        
        // Adicionar coluna 'event' se não existir
        if (!$schema->hasColumn($tableName, 'event')) {
            $schema->table($tableName, function (Blueprint $table) {
                $table->string('event')->nullable()->after('subject_type');
            });
        }
        
        // Adicionar coluna 'batch_uuid' se não existir
        if (!$schema->hasColumn($tableName, 'batch_uuid')) {
            $schema->table($tableName, function (Blueprint $table) {
                $table->uuid('batch_uuid')->nullable()->after('properties');
                $table->index('batch_uuid');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $connection = config('activitylog.database_connection', null);
        $tableName = config('activitylog.table_name', 'activity_log');
        $schema = Schema::connection($connection);
        
        if ($schema->hasColumn($tableName, 'event')) {
            $schema->table($tableName, function (Blueprint $table) {
                $table->dropColumn('event');
            });
        }
        
        if ($schema->hasColumn($tableName, 'batch_uuid')) {
            $schema->table($tableName, function (Blueprint $table) {
                $table->dropIndex(['batch_uuid']);
                $table->dropColumn('batch_uuid');
            });
        }
    }
};
