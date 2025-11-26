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
        Schema::dropIfExists('tenant_users');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('tenant_users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('tenant_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->unsignedBigInteger('role_id')->nullable();
            $table->json('permissions')->nullable();
            $table->boolean('current_tenant')->default(false);
            $table->enum('status', ['active', 'inactive', 'suspended'])->default('active');
            $table->timestamp('joined_at')->useCurrent();
            $table->timestamps();
            
            $table->unique(['tenant_id', 'user_id']);
            $table->index(['user_id', 'current_tenant']);
            $table->index(['tenant_id', 'status']);
            $table->index('role_id');
        });
    }
};

