<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\V1\SIS\Student\Student;
use App\Models\V1\SIS\School\School;
use App\Models\Settings\Tenant;
use App\Models\User;

class StudentDocumentsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Get existing data
            $tenant = Tenant::first();
            $school = School::first();
            $user = User::first();
            
            if (!$tenant || !$school || !$user) {
                $this->command->warn('Required dependencies not found. Please run other seeders first.');
                return;
            }
            
            // Get existing students
            $students = Student::where('school_id', $school->id)->take(5)->get();
            
            if ($students->isEmpty()) {
                $this->command->warn('No students found. Please run StudentClassEnrollmentSeeder first.');
                return;
            }
            
            // Create student documents
            $this->createStudentDocuments($school->id, $students, $user->id);
            
            DB::commit();
            
            $this->command->info('✅ Student documents seeded successfully!');
            $this->command->info("📊 Created documents for " . count($students) . " students");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createStudentDocuments($schoolId, $students, $userId)
    {
        $documents = [
            // João Silva documents
            [
                'student' => $students[0],
                'document_name' => 'Certidão de Nascimento - João Silva',
                'document_type' => 'birth_certificate',
                'document_category' => 'Legal Documents',
                'file_name' => 'certidao_nascimento_joao_silva.pdf',
                'file_path' => '/documents/students/joao_silva/certidao_nascimento.pdf',
                'file_type' => 'pdf',
                'file_size' => 245760,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Documento verificado e aprovado pela secretaria.'
            ],
            [
                'student' => $students[0],
                'document_name' => 'Cartão de Vacinação - João Silva',
                'document_type' => 'vaccination_records',
                'document_category' => 'Health Records',
                'file_name' => 'cartao_vacinacao_joao_silva.pdf',
                'file_path' => '/documents/students/joao_silva/cartao_vacinacao.pdf',
                'file_type' => 'pdf',
                'file_size' => 189440,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Vacinas em dia conforme calendário escolar.'
            ],
            [
                'student' => $students[0],
                'document_name' => 'Histórico Escolar Anterior - João Silva',
                'document_type' => 'previous_transcripts',
                'document_category' => 'Academic Records',
                'file_name' => 'historico_escolar_joao_silva.pdf',
                'file_path' => '/documents/students/joao_silva/historico_escolar.pdf',
                'file_type' => 'pdf',
                'file_size' => 156780,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Histórico validado pela escola anterior.'
            ],

            // Maria Santos documents
            [
                'student' => $students[1],
                'document_name' => 'Certidão de Nascimento - Maria Santos',
                'document_type' => 'birth_certificate',
                'document_category' => 'Legal Documents',
                'file_name' => 'certidao_nascimento_maria_santos.pdf',
                'file_path' => '/documents/students/maria_santos/certidao_nascimento.pdf',
                'file_type' => 'pdf',
                'file_size' => 267890,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Documento original verificado.'
            ],
            [
                'student' => $students[1],
                'document_name' => 'RG - Maria Santos',
                'document_type' => 'identification',
                'document_category' => 'Identity Documents',
                'file_name' => 'rg_maria_santos.jpg',
                'file_path' => '/documents/students/maria_santos/rg.jpg',
                'file_type' => 'jpg',
                'file_size' => 456720,
                'mime_type' => 'image/jpeg',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'RG válido e legível.'
            ],
            [
                'student' => $students[1],
                'document_name' => 'Ficha de Matrícula - Maria Santos',
                'document_type' => 'enrollment_form',
                'document_category' => 'Enrollment Documents',
                'file_name' => 'ficha_matricula_maria_santos.pdf',
                'file_path' => '/documents/students/maria_santos/ficha_matricula.pdf',
                'file_type' => 'pdf',
                'file_size' => 198450,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Ficha preenchida corretamente e assinada pelos responsáveis.'
            ],

            // Pedro Oliveira documents
            [
                'student' => $students[2],
                'document_name' => 'Certidão de Nascimento - Pedro Oliveira',
                'document_type' => 'birth_certificate',
                'document_category' => 'Legal Documents',
                'file_name' => 'certidao_nascimento_pedro_oliveira.pdf',
                'file_path' => '/documents/students/pedro_oliveira/certidao_nascimento.pdf',
                'file_type' => 'pdf',
                'file_size' => 234560,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Documento autenticado.'
            ],
            [
                'student' => $students[2],
                'document_name' => 'Atestado Médico - Pedro Oliveira',
                'document_type' => 'medical_records',
                'document_category' => 'Health Records',
                'file_name' => 'atestado_medico_pedro_oliveira.pdf',
                'file_path' => '/documents/students/pedro_oliveira/atestado_medico.pdf',
                'file_type' => 'pdf',
                'file_size' => 123450,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => false,
                'verified' => true,
                'verification_notes' => 'Atestado válido até dezembro de 2024.'
            ],

            // Ana Costa documents
            [
                'student' => $students[3],
                'document_name' => 'Certidão de Nascimento - Ana Costa',
                'document_type' => 'birth_certificate',
                'document_category' => 'Legal Documents',
                'file_name' => 'certidao_nascimento_ana_costa.pdf',
                'file_path' => '/documents/students/ana_costa/certidao_nascimento.pdf',
                'file_type' => 'pdf',
                'file_size' => 278910,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Documento em perfeito estado.'
            ],
            [
                'student' => $students[3],
                'document_name' => 'Contatos de Emergência - Ana Costa',
                'document_type' => 'emergency_contacts',
                'document_category' => 'Emergency Information',
                'file_name' => 'contatos_emergencia_ana_costa.pdf',
                'file_path' => '/documents/students/ana_costa/contatos_emergencia.pdf',
                'file_type' => 'pdf',
                'file_size' => 98765,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => true,
                'verified' => true,
                'verification_notes' => 'Contatos atualizados e verificados.'
            ],
            [
                'student' => $students[3],
                'document_name' => 'Autorização para Fotos - Ana Costa',
                'document_type' => 'photo_permission',
                'document_category' => 'Permissions',
                'file_name' => 'autorizacao_fotos_ana_costa.pdf',
                'file_path' => '/documents/students/ana_costa/autorizacao_fotos.pdf',
                'file_type' => 'pdf',
                'file_size' => 87654,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => false,
                'verified' => true,
                'verification_notes' => 'Autorização assinada pelos responsáveis.'
            ],

            // Carlos Ferreira documents
            [
                'student' => $students[4],
                'document_name' => 'Certidão de Nascimento - Carlos Ferreira',
                'document_type' => 'birth_certificate',
                'document_category' => 'Legal Documents',
                'file_name' => 'certidao_nascimento_carlos_ferreira.pdf',
                'file_path' => '/documents/students/carlos_ferreira/certidao_nascimento.pdf',
                'file_type' => 'pdf',
                'file_size' => 256780,
                'mime_type' => 'application/pdf',
                'status' => 'pending',
                'required' => true,
                'verified' => false,
                'verification_notes' => null
            ],
            [
                'student' => $students[4],
                'document_name' => 'Relatório de Educação Especial - Carlos Ferreira',
                'document_type' => 'special_education',
                'document_category' => 'Special Needs',
                'file_name' => 'relatorio_educacao_especial_carlos_ferreira.pdf',
                'file_path' => '/documents/students/carlos_ferreira/relatorio_educacao_especial.pdf',
                'file_type' => 'pdf',
                'file_size' => 345670,
                'mime_type' => 'application/pdf',
                'status' => 'approved',
                'required' => false,
                'verified' => true,
                'verification_notes' => 'Relatório atualizado com recomendações pedagógicas.'
            ]
        ];

        foreach ($documents as $doc) {
            // Check if document already exists
            $existingDoc = DB::table('student_documents')
                ->where('student_id', $doc['student']->id)
                ->where('document_name', $doc['document_name'])
                ->first();
                
            if (!$existingDoc) {
                $documentData = [
                    'school_id' => $schoolId,
                    'student_id' => $doc['student']->id,
                    'document_name' => $doc['document_name'],
                    'document_type' => $doc['document_type'],
                    'document_category' => $doc['document_category'],
                    'file_name' => $doc['file_name'],
                    'file_path' => $doc['file_path'],
                    'file_type' => $doc['file_type'],
                    'file_size' => $doc['file_size'],
                    'mime_type' => $doc['mime_type'],
                    'status' => $doc['status'],
                    'required' => $doc['required'],
                    'verified' => $doc['verified'],
                    'uploaded_by' => $userId,
                    'verification_notes' => $doc['verification_notes'],
                    'ferpa_protected' => true,
                    'created_at' => now(),
                    'updated_at' => now()
                ];

                // Add verification data if document is verified
                if ($doc['verified']) {
                    $documentData['verified_by'] = $userId;
                    $documentData['verified_at'] = now();
                }

                // Add expiration date for certain document types
                if (in_array($doc['document_type'], ['medical_records', 'vaccination_records'])) {
                    $documentData['expiration_date'] = now()->addYear()->toDateString();
                }

                DB::table('student_documents')->insert($documentData);
            }
        }
    }
}
