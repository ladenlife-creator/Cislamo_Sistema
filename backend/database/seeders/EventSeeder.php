<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EventSeeder extends Seeder
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

        $events = [
            [
                'title' => 'Reunião de Planejamento 2024',
                'description' => 'Reunião para planejamento das atividades do ano de 2024',
                'start_date' => Carbon::now()->addDays(5)->setTime(9, 0),
                'end_date' => Carbon::now()->addDays(5)->setTime(12, 0),
                'location' => 'Sala de Reuniões - Andar 3',
                'status' => 'scheduled',
                'meeting_code' => 'REU-2024-001',
                'meeting_link' => 'https://meet.google.com/abc-defg-hij',
                'user_id' => $users->first(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Workshop de Capacitação',
                'description' => 'Workshop sobre uso do sistema ERP CISLAMO',
                'start_date' => Carbon::now()->addDays(10)->setTime(14, 0),
                'end_date' => Carbon::now()->addDays(10)->setTime(17, 0),
                'location' => 'Auditório Principal',
                'status' => 'scheduled',
                'meeting_code' => 'WS-2024-001',
                'meeting_link' => 'https://meet.google.com/xyz-uvwx-rst',
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Apresentação de Resultados',
                'description' => 'Apresentação dos resultados do primeiro trimestre',
                'start_date' => Carbon::now()->addDays(15)->setTime(10, 0),
                'end_date' => Carbon::now()->addDays(15)->setTime(11, 30),
                'location' => 'Sala de Conferências',
                'status' => 'scheduled',
                'meeting_code' => 'APR-2024-001',
                'meeting_link' => null,
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Treinamento de Novos Funcionários',
                'description' => 'Treinamento sobre processos e procedimentos',
                'start_date' => Carbon::now()->addDays(20)->setTime(8, 30),
                'end_date' => Carbon::now()->addDays(20)->setTime(16, 0),
                'location' => 'Sala de Treinamento',
                'status' => 'scheduled',
                'meeting_code' => 'TRE-2024-001',
                'meeting_link' => 'https://meet.google.com/mno-pqrs-tuv',
                'user_id' => $users->first(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'title' => 'Reunião de Equipe',
                'description' => 'Reunião semanal da equipe',
                'start_date' => Carbon::now()->addDays(3)->setTime(15, 0),
                'end_date' => Carbon::now()->addDays(3)->setTime(16, 0),
                'location' => 'Sala de Reuniões - Andar 2',
                'status' => 'scheduled',
                'meeting_code' => null,
                'meeting_link' => null,
                'user_id' => $users->random(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('events')->insert($events);
        $this->command->info('✅ ' . count($events) . ' eventos criados com sucesso!');
    }
}
