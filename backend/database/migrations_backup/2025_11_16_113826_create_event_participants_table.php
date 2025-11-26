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
        Schema::create('event_participants', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('event_id');
            $table->string('nome');
            $table->string('email');
            $table->string('telefone')->nullable();
            $table->string('participation_code')->unique()->nullable(); // Código único para participação
            $table->string('meeting_link')->nullable(); // Link do Google Meeting ou outro
            $table->string('meeting_code')->nullable(); // Código do meeting (se aplicável)
            $table->enum('status', ['pending', 'confirmed', 'attended', 'cancelled'])->default('pending');
            $table->text('observations')->nullable();
            $table->timestamp('registered_at')->useCurrent();
            $table->timestamps();
            
            $table->foreign('event_id')->references('id')->on('events')->onDelete('cascade');
            $table->index('participation_code');
            $table->index('event_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_participants');
    }
};
