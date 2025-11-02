<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\V1\SIS\School\School;
use App\Models\Settings\Tenant;

class GradingSystemsSeeder extends Seeder
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
            
            if (!$tenant || !$school) {
                $this->command->warn('Required dependencies not found. Please run other seeders first.');
                return;
            }
            
            // Create grading systems
            $this->createGradingSystems($tenant->id, $school->id);
            
            DB::commit();
            
            $this->command->info('✅ Grading systems seeded successfully!');
            $this->command->info("📊 Created multiple grading systems for the school");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function createGradingSystems($tenantId, $schoolId)
    {
        $gradingSystems = [
            [
                'name' => 'Sistema de Notas Tradicional (A-F)',
                'system_type' => 'traditional_letter',
                'applicable_grades' => ['6th Grade', '7th Grade', '8th Grade', '9th Grade', '10th Grade', '11th Grade', '12th Grade'],
                'applicable_subjects' => ['mathematics', 'science', 'language_arts', 'social_studies', 'foreign_language', 'arts', 'physical_education'],
                'is_primary' => true,
                'configuration_json' => [
                    'grade_scale' => [
                        'A+' => ['min_percentage' => 97, 'max_percentage' => 100, 'gpa_value' => 4.0],
                        'A' => ['min_percentage' => 93, 'max_percentage' => 96, 'gpa_value' => 4.0],
                        'A-' => ['min_percentage' => 90, 'max_percentage' => 92, 'gpa_value' => 3.7],
                        'B+' => ['min_percentage' => 87, 'max_percentage' => 89, 'gpa_value' => 3.3],
                        'B' => ['min_percentage' => 83, 'max_percentage' => 86, 'gpa_value' => 3.0],
                        'B-' => ['min_percentage' => 80, 'max_percentage' => 82, 'gpa_value' => 2.7],
                        'C+' => ['min_percentage' => 77, 'max_percentage' => 79, 'gpa_value' => 2.3],
                        'C' => ['min_percentage' => 73, 'max_percentage' => 76, 'gpa_value' => 2.0],
                        'C-' => ['min_percentage' => 70, 'max_percentage' => 72, 'gpa_value' => 1.7],
                        'D+' => ['min_percentage' => 67, 'max_percentage' => 69, 'gpa_value' => 1.3],
                        'D' => ['min_percentage' => 63, 'max_percentage' => 66, 'gpa_value' => 1.0],
                        'F' => ['min_percentage' => 0, 'max_percentage' => 62, 'gpa_value' => 0.0]
                    ],
                    'passing_grade' => 'D',
                    'honor_roll_threshold' => 'B+',
                    'dean_list_threshold' => 'A-'
                ],
                'status' => 'active'
            ],
            [
                'name' => 'Sistema de Notas por Percentual',
                'system_type' => 'percentage',
                'applicable_grades' => ['1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade'],
                'applicable_subjects' => ['mathematics', 'science', 'language_arts', 'social_studies', 'arts', 'physical_education'],
                'is_primary' => false,
                'configuration_json' => [
                    'grade_scale' => [
                        'excellent' => ['min_percentage' => 90, 'max_percentage' => 100],
                        'good' => ['min_percentage' => 80, 'max_percentage' => 89],
                        'satisfactory' => ['min_percentage' => 70, 'max_percentage' => 79],
                        'needs_improvement' => ['min_percentage' => 60, 'max_percentage' => 69],
                        'unsatisfactory' => ['min_percentage' => 0, 'max_percentage' => 59]
                    ],
                    'passing_percentage' => 70,
                    'decimal_places' => 1
                ],
                'status' => 'active'
            ],
            [
                'name' => 'Sistema Baseado em Padrões',
                'system_type' => 'standards_based',
                'applicable_grades' => ['K', '1st Grade', '2nd Grade', '3rd Grade', '4th Grade', '5th Grade'],
                'applicable_subjects' => ['mathematics', 'science', 'language_arts', 'social_studies'],
                'is_primary' => false,
                'configuration_json' => [
                    'performance_levels' => [
                        'exceeds_standard' => ['description' => 'Exceeds grade level expectations', 'color' => '#28a745'],
                        'meets_standard' => ['description' => 'Meets grade level expectations', 'color' => '#17a2b8'],
                        'approaching_standard' => ['description' => 'Approaching grade level expectations', 'color' => '#ffc107'],
                        'below_standard' => ['description' => 'Below grade level expectations', 'color' => '#dc3545']
                    ],
                    'standards_categories' => [
                        'mathematics' => ['number_operations', 'algebra', 'geometry', 'measurement', 'data_analysis'],
                        'language_arts' => ['reading', 'writing', 'speaking', 'listening', 'language'],
                        'science' => ['physical_science', 'life_science', 'earth_science', 'scientific_inquiry'],
                        'social_studies' => ['history', 'geography', 'civics', 'economics']
                    ]
                ],
                'status' => 'active'
            ],
            [
                'name' => 'Sistema de Pontos',
                'system_type' => 'points',
                'applicable_grades' => ['9th Grade', '10th Grade', '11th Grade', '12th Grade'],
                'applicable_subjects' => ['mathematics', 'science', 'language_arts', 'social_studies', 'foreign_language'],
                'is_primary' => false,
                'configuration_json' => [
                    'point_scale' => [
                        'max_points' => 100,
                        'passing_points' => 60,
                        'honor_roll_points' => 85,
                        'dean_list_points' => 90
                    ],
                    'assignment_types' => [
                        'homework' => ['weight' => 0.1, 'max_points' => 10],
                        'quizzes' => ['weight' => 0.2, 'max_points' => 20],
                        'tests' => ['weight' => 0.4, 'max_points' => 100],
                        'projects' => ['weight' => 0.2, 'max_points' => 100],
                        'participation' => ['weight' => 0.1, 'max_points' => 10]
                    ]
                ],
                'status' => 'active'
            ],
            [
                'name' => 'Sistema Narrativo',
                'system_type' => 'narrative',
                'applicable_grades' => ['K', '1st Grade', '2nd Grade'],
                'applicable_subjects' => ['language_arts', 'mathematics', 'science', 'social_studies', 'arts'],
                'is_primary' => false,
                'configuration_json' => [
                    'narrative_categories' => [
                        'academic_progress' => 'Progresso acadêmico do estudante',
                        'social_development' => 'Desenvolvimento social e emocional',
                        'work_habits' => 'Hábitos de trabalho e organização',
                        'strengths' => 'Pontos fortes do estudante',
                        'areas_for_growth' => 'Áreas para crescimento e melhoria'
                    ],
                    'template_sections' => [
                        'overview' => 'Visão geral do desempenho',
                        'subject_specific' => 'Comentários específicos por matéria',
                        'recommendations' => 'Recomendações para o próximo período',
                        'parent_communication' => 'Comunicação com os pais'
                    ]
                ],
                'status' => 'active'
            ]
        ];

        foreach ($gradingSystems as $system) {
            // Check if grading system already exists
            $existingSystem = DB::table('grading_systems')
                ->where('school_id', $schoolId)
                ->where('name', $system['name'])
                ->first();
                
            if (!$existingSystem) {
                DB::table('grading_systems')->insert([
                    'school_id' => $schoolId,
                    'tenant_id' => $tenantId,
                    'name' => $system['name'],
                    'system_type' => $system['system_type'],
                    'applicable_grades' => json_encode($system['applicable_grades']),
                    'applicable_subjects' => json_encode($system['applicable_subjects']),
                    'is_primary' => $system['is_primary'],
                    'configuration_json' => json_encode($system['configuration_json']),
                    'status' => $system['status'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
