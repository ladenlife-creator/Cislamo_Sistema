<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\V1\SIS\Student\Student;
use App\Models\V1\Academic\AcademicClass;
use App\Models\V1\SIS\School\School;
use App\Models\Settings\Tenant;
use App\Models\User;

class GradeEntriesSeeder extends Seeder
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
            
            // Get or create teacher
            $teacher = $this->getOrCreateTeacher($tenant->id, $school->id, $user->id);
            
            // Get or create academic term
            $academicTerm = $this->getOrCreateAcademicTerm($tenant->id, $school->id);
            
            // Get existing students and classes
            $students = Student::where('school_id', $school->id)->take(5)->get();
            $classes = AcademicClass::where('school_id', $school->id)->take(4)->get();
            
            if ($students->isEmpty() || $classes->isEmpty()) {
                $this->command->warn('No students or classes found. Please run StudentClassEnrollmentSeeder first.');
                return;
            }
            
            // Create grade entries
            $this->createGradeEntries($tenant->id, $school->id, $students, $classes, $teacher->id, $academicTerm->id);
            
            DB::commit();
            
            $this->command->info('✅ Grade entries seeded successfully!');
            $this->command->info("📊 Created grade entries for " . count($students) . " students");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getOrCreateTeacher($tenantId, $schoolId, $userId)
    {
        $teacher = DB::table('teachers')->where('school_id', $schoolId)->first();
        if (!$teacher) {
            $teacherId = DB::table('teachers')->insertGetId([
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'user_id' => $userId,
                'employee_id' => 'TCH001',
                'first_name' => 'Prof.',
                'last_name' => 'Silva',
                'title' => 'Dr.',
                'employment_type' => 'full_time',
                'hire_date' => now()->subYears(2)->toDateString(),
                'status' => 'active',
                'department' => 'Mathematics',
                'position' => 'Senior Teacher',
                'specializations_json' => json_encode(['mathematics', 'algebra', 'geometry']),
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $teacher = (object) ['id' => $teacherId];
        }
        return $teacher;
    }

    private function getOrCreateAcademicTerm($tenantId, $schoolId)
    {
        $academicTerm = DB::table('academic_terms')->where('school_id', $schoolId)->first();
        if (!$academicTerm) {
            $academicYear = DB::table('academic_years')->where('school_id', $schoolId)->first();
            if (!$academicYear) {
                $academicYearId = DB::table('academic_years')->insertGetId([
                    'tenant_id' => $tenantId,
                    'school_id' => $schoolId,
                    'name' => '2024-2025',
                    'start_date' => '2024-02-01',
                    'end_date' => '2024-12-15',
                    'is_current' => true,
                    'status' => 'active',
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            } else {
                $academicYearId = $academicYear->id;
            }
            
            $academicTermId = DB::table('academic_terms')->insertGetId([
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'academic_year_id' => $academicYearId,
                'name' => '1st Semester',
                'type' => 'semester',
                'term_number' => 1,
                'start_date' => '2024-02-01',
                'end_date' => '2024-07-15',
                'is_current' => true,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
            $academicTerm = (object) ['id' => $academicTermId];
        }
        return $academicTerm;
    }

    private function createGradeEntries($tenantId, $schoolId, $students, $classes, $teacherId, $academicTermId)
    {
        $gradeEntries = [
            // João Silva - Mathematics
            [
                'student' => $students[0],
                'class' => $classes[0],
                'assessment_name' => 'Algebra Quiz 1',
                'assessment_type' => 'quiz',
                'assessment_date' => '2024-03-15',
                'raw_score' => 18.0,
                'percentage_score' => 90.0,
                'letter_grade' => 'A',
                'points_earned' => 18.0,
                'points_possible' => 20.0,
                'grade_category' => 'Quizzes',
                'weight' => 0.3,
                'teacher_comments' => 'Excellent work! Shows strong understanding of algebraic concepts.'
            ],
            [
                'student' => $students[0],
                'class' => $classes[0],
                'assessment_name' => 'Geometry Midterm',
                'assessment_type' => 'exam',
                'assessment_date' => '2024-04-20',
                'raw_score' => 85.0,
                'percentage_score' => 85.0,
                'letter_grade' => 'B+',
                'points_earned' => 85.0,
                'points_possible' => 100.0,
                'grade_category' => 'Exams',
                'weight' => 0.4,
                'teacher_comments' => 'Good performance. Review triangle properties for improvement.'
            ],
            
            // Maria Santos - Mathematics
            [
                'student' => $students[1],
                'class' => $classes[0],
                'assessment_name' => 'Algebra Quiz 1',
                'assessment_type' => 'quiz',
                'assessment_date' => '2024-03-15',
                'raw_score' => 20.0,
                'percentage_score' => 100.0,
                'letter_grade' => 'A+',
                'points_earned' => 20.0,
                'points_possible' => 20.0,
                'grade_category' => 'Quizzes',
                'weight' => 0.3,
                'teacher_comments' => 'Perfect score! Outstanding algebraic reasoning.'
            ],
            [
                'student' => $students[1],
                'class' => $classes[0],
                'assessment_name' => 'Geometry Midterm',
                'assessment_type' => 'exam',
                'assessment_date' => '2024-04-20',
                'raw_score' => 92.0,
                'percentage_score' => 92.0,
                'letter_grade' => 'A-',
                'points_earned' => 92.0,
                'points_possible' => 100.0,
                'grade_category' => 'Exams',
                'weight' => 0.4,
                'teacher_comments' => 'Excellent work. Minor calculation errors in complex problems.'
            ],
            
            // Pedro Oliveira - Science
            [
                'student' => $students[2],
                'class' => $classes[2],
                'assessment_name' => 'Chemistry Lab Report',
                'assessment_type' => 'project',
                'assessment_date' => '2024-03-25',
                'raw_score' => 78.0,
                'percentage_score' => 78.0,
                'letter_grade' => 'C+',
                'points_earned' => 78.0,
                'points_possible' => 100.0,
                'grade_category' => 'Projects',
                'weight' => 0.25,
                'teacher_comments' => 'Good experimental design. Improve data analysis section.'
            ],
            [
                'student' => $students[2],
                'class' => $classes[2],
                'assessment_name' => 'Physics Test',
                'assessment_type' => 'exam',
                'assessment_date' => '2024-04-10',
                'raw_score' => 88.0,
                'percentage_score' => 88.0,
                'letter_grade' => 'B+',
                'points_earned' => 88.0,
                'points_possible' => 100.0,
                'grade_category' => 'Exams',
                'weight' => 0.4,
                'teacher_comments' => 'Strong understanding of physics concepts. Good problem-solving skills.'
            ],
            
            // Ana Costa - Science
            [
                'student' => $students[3],
                'class' => $classes[2],
                'assessment_name' => 'Chemistry Lab Report',
                'assessment_type' => 'project',
                'assessment_date' => '2024-03-25',
                'raw_score' => 95.0,
                'percentage_score' => 95.0,
                'letter_grade' => 'A',
                'points_earned' => 95.0,
                'points_possible' => 100.0,
                'grade_category' => 'Projects',
                'weight' => 0.25,
                'teacher_comments' => 'Outstanding lab report! Excellent methodology and conclusions.'
            ],
            [
                'student' => $students[3],
                'class' => $classes[2],
                'assessment_name' => 'Physics Test',
                'assessment_type' => 'exam',
                'assessment_date' => '2024-04-10',
                'raw_score' => 91.0,
                'percentage_score' => 91.0,
                'letter_grade' => 'A-',
                'points_earned' => 91.0,
                'points_possible' => 100.0,
                'grade_category' => 'Exams',
                'weight' => 0.4,
                'teacher_comments' => 'Excellent performance. Shows mastery of physics principles.'
            ],
            
            // Carlos Ferreira - History
            [
                'student' => $students[4],
                'class' => $classes[3],
                'assessment_name' => 'Ancient Civilizations Essay',
                'assessment_type' => 'project',
                'assessment_date' => '2024-03-30',
                'raw_score' => 82.0,
                'percentage_score' => 82.0,
                'letter_grade' => 'B-',
                'points_earned' => 82.0,
                'points_possible' => 100.0,
                'grade_category' => 'Projects',
                'weight' => 0.3,
                'teacher_comments' => 'Good historical analysis. Work on citing sources properly.'
            ],
            [
                'student' => $students[4],
                'class' => $classes[3],
                'assessment_name' => 'Class Participation',
                'assessment_type' => 'participation',
                'assessment_date' => '2024-04-15',
                'raw_score' => 90.0,
                'percentage_score' => 90.0,
                'letter_grade' => 'A-',
                'points_earned' => 90.0,
                'points_possible' => 100.0,
                'grade_category' => 'Participation',
                'weight' => 0.15,
                'teacher_comments' => 'Very engaged in class discussions. Great questions!'
            ]
        ];

        foreach ($gradeEntries as $entry) {
            // Check if grade entry already exists
            $existingEntry = DB::table('grade_entries')
                ->where('student_id', $entry['student']->id)
                ->where('class_id', $entry['class']->id)
                ->where('assessment_name', $entry['assessment_name'])
                ->first();
                
            if (!$existingEntry) {
                DB::table('grade_entries')->insert([
                    'tenant_id' => $tenantId,
                    'school_id' => $schoolId,
                    'student_id' => $entry['student']->id,
                    'class_id' => $entry['class']->id,
                    'academic_term_id' => $academicTermId,
                    'assessment_name' => $entry['assessment_name'],
                    'assessment_type' => $entry['assessment_type'],
                    'assessment_date' => $entry['assessment_date'],
                    'raw_score' => $entry['raw_score'],
                    'percentage_score' => $entry['percentage_score'],
                    'letter_grade' => $entry['letter_grade'],
                    'points_earned' => $entry['points_earned'],
                    'points_possible' => $entry['points_possible'],
                    'grade_category' => $entry['grade_category'],
                    'weight' => $entry['weight'],
                    'entered_by' => $teacherId,
                    'entered_at' => now(),
                    'teacher_comments' => $entry['teacher_comments'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }
    }
}
