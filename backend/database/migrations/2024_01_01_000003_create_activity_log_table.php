<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateActivityLogTable extends Migration
{
    public function up()
    {
        $connection = config('activitylog.database_connection', null);
        $tableName = config('activitylog.table_name', 'activity_log');
        
        Schema::connection($connection)->create($tableName, function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('log_name')->nullable();
            $table->text('description');
            $table->nullableMorphs('subject', 'subject');
            $table->string('event')->nullable()->after('subject_type');
            $table->nullableMorphs('causer', 'causer');
            $table->json('properties')->nullable();
            $table->uuid('batch_uuid')->nullable()->after('properties');
            $table->timestamps();
            $table->index('log_name');
            $table->index('batch_uuid');
        });
    }

    public function down()
    {
        $connection = config('activitylog.database_connection', null);
        $tableName = config('activitylog.table_name', 'activity_log');
        Schema::connection($connection)->dropIfExists($tableName);
    }
}

