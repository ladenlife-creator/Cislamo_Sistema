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
        // Remover foreign keys primeiro para evitar erros
        // Remover tabelas que dependem de students e schools primeiro
        Schema::dropIfExists('student_documents');
        Schema::dropIfExists('student_transport_events');
        
        // Remover students (depende de schools e tenants)
        Schema::dropIfExists('students');
        
        // Remover schools (depende de tenants)
        Schema::dropIfExists('schools');
        
        // Remover tenants
        Schema::dropIfExists('tenants');
        
        // Remover tabelas do Laravel (jobs, queues, etc)
        Schema::dropIfExists('failed_jobs');
        Schema::dropIfExists('job_batches');
        Schema::dropIfExists('jobs');
        
        // Remover tabelas de autenticação/sessão
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('personal_access_tokens');
        Schema::dropIfExists('sessions');
        
        // Nota: A tabela 'migrations' não será removida aqui para evitar conflitos
        // Se precisar removê-la, faça manualmente após esta migração ser executada
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Nota: Não vamos recriar todas essas tabelas no down()
        // pois seria muito complexo e pode não ser necessário
        // Se precisar reverter, execute as migrações originais novamente
    }
};

