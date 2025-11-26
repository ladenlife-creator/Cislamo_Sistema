<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DocumentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = DB::table('users')->pluck('id');
        
        if ($users->isEmpty()) {
            $this->command->warn('Nenhum usuário encontrado. Execute UserSeeder primeiro.');
            return;
        }

        $documents = [
            [
                'title' => 'Manual do Sistema ERP CISLAMO',
                'description' => 'Documentação completa do sistema ERP CISLAMO',
                'category' => 'Documentação',
                'file_path' => '/documents/manual-erp-cislamo.pdf',
                'file_type' => 'pdf',
                'file_size' => 2048000,
                'user_id' => $users->first(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Política de Privacidade',
                'description' => 'Política de privacidade e proteção de dados',
                'category' => 'Legal',
                'file_path' => '/documents/politica-privacidade.pdf',
                'file_type' => 'pdf',
                'file_size' => 512000,
                'user_id' => $users->first(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Guia de Uso - Eventos',
                'description' => 'Guia completo para criação e gerenciamento de eventos',
                'category' => 'Guia',
                'file_path' => '/documents/guia-eventos.pdf',
                'file_type' => 'pdf',
                'file_size' => 1024000,
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Relatório Mensal - Janeiro 2024',
                'description' => 'Relatório de atividades do mês de janeiro',
                'category' => 'Relatório',
                'file_path' => '/documents/relatorio-janeiro-2024.xlsx',
                'file_type' => 'xlsx',
                'file_size' => 256000,
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Apresentação Institucional',
                'description' => 'Apresentação sobre o ERP CISLAMO',
                'category' => 'Apresentação',
                'file_path' => '/documents/apresentacao-institucional.pptx',
                'file_type' => 'pptx',
                'file_size' => 5120000,
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('documents')->insert($documents);
        $this->command->info('✅ ' . count($documents) . ' documentos criados com sucesso!');
    }
}
