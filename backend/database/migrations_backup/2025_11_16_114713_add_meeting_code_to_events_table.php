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
        Schema::table('events', function (Blueprint $table) {
            $table->string('meeting_code')->nullable()->after('location'); // Código do meeting específico do evento
            $table->string('meeting_link')->nullable()->after('meeting_code'); // Link do meeting (opcional)
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn(['meeting_code', 'meeting_link']);
        });
    }
};
