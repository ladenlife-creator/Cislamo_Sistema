<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventParticipantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $events = DB::table('events')->pluck('id');
        
        if ($events->isEmpty()) {
            $this->command->warn('Nenhum evento encontrado. Execute EventSeeder primeiro.');
            return;
        }

        $participants = [
            [
                'event_id' => $events->first(),
                'nome' => 'Carlos Oliveira',
                'email' => 'carlos.oliveira@example.com',
                'telefone' => '+1234567893',
                'participation_code' => 'PART-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'observations' => 'Participante confirmado',
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $events->first(),
                'nome' => 'Ana Costa',
                'email' => 'ana.costa@example.com',
                'telefone' => '+1234567894',
                'participation_code' => 'PART-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'observations' => null,
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $events->first(),
                'nome' => 'Pedro Almeida',
                'email' => 'pedro.almeida@example.com',
                'telefone' => '+1234567895',
                'participation_code' => 'PART-' . strtoupper(uniqid()),
                'status' => 'pending',
                'observations' => 'Aguardando confirmação',
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $events->skip(1)->first(),
                'nome' => 'Fernanda Lima',
                'email' => 'fernanda.lima@example.com',
                'telefone' => '+1234567896',
                'participation_code' => 'PART-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'observations' => null,
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'event_id' => $events->skip(1)->first(),
                'nome' => 'Roberto Souza',
                'email' => 'roberto.souza@example.com',
                'telefone' => '+1234567897',
                'participation_code' => 'PART-' . strtoupper(uniqid()),
                'status' => 'confirmed',
                'observations' => 'Participante VIP',
                'registered_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        DB::table('event_participants')->insert($participants);
        $this->command->info('✅ ' . count($participants) . ' participantes criados com sucesso!');
    }
}
