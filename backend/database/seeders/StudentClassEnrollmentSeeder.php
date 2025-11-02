<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\V1\SIS\Student\Student;
use App\Models\V1\Academic\AcademicClass;
use App\Models\V1\SIS\School\School;
use App\Models\Settings\Tenant;
use App\Models\User;

class StudentClassEnrollmentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::beginTransaction();

        try {
            // Get or create required dependencies
            $tenant = $this->getOrCreateTenant();
            $school = $this->getOrCreateSchool($tenant->id);
            $user = $this->getOrCreateUser();
            
            // Create students if they don't exist
            $students = $this->createStudents($tenant->id, $school->id, $user->id);
            
            // Create classes if they don't exist
            $classes = $this->createClasses($tenant->id, $school->id);
            
            // Create enrollments
            $this->createEnrollments($students, $classes);
            
            DB::commit();
            
            $this->command->info('✅ Student class enrollments seeded successfully!');
            $this->command->info("📊 Created enrollments for " . count($students) . " students in " . count($classes) . " classes");
            
        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Error: ' . $e->getMessage());
            throw $e;
        }
    }

    private function getOrCreateTenant()
    {
        $tenant = Tenant::first();
        if (!$tenant) {
            $tenant = Tenant::create([
                'name' => 'Test School System',
                'slug' => 'test-school-system',
                'domain' => 'test.school.com',
                'database' => 'test_db',
                'settings' => ['timezone' => 'UTC', 'locale' => 'en', 'currency' => 'USD'],
                'is_active' => true,
                'created_by' => 1,
                'owner_id' => 1
            ]);
        }
        return $tenant;
    }

    private function getOrCreateSchool($tenantId)
    {
        $school = School::where('tenant_id', $tenantId)->first();
        if (!$school) {
            $school = School::create([
                'tenant_id' => $tenantId,
                'school_code' => 'SCH001',
                'official_name' => 'Test School',
                'display_name' => 'Test School',
                'short_name' => 'TS',
                'school_type' => 'private',
                'educational_levels' => ['elementary', 'middle', 'high'],
                'grade_range_min' => '1st Grade',
                'grade_range_max' => '12th Grade',
                'email' => 'info@testschool.com',
                'phone' => '+1234567890',
                'website' => 'https://testschool.com',
                'country_code' => 'BR',
                'city' => 'São Paulo',
                'timezone' => 'America/Sao_Paulo',
                'accreditation_status' => 'accredited',
                'academic_calendar_type' => 'semester',
                'academic_year_start_month' => 2,
                'grading_system' => 'percentage',
                'attendance_tracking_level' => 'daily',
                'language_instruction' => ['portuguese'],
                'current_enrollment' => 100,
                'staff_count' => 20,
                'subscription_plan' => 'premium',
                'status' => 'active'
            ]);
        }
        return $school;
    }

    private function getOrCreateUser()
    {
        $user = User::first();
        if (!$user) {
            $user = User::create([
                'name' => 'Test User',
                'identifier' => 'test@test.com',
                'type' => 'email',
                'password' => bcrypt('password'),
                'verified_at' => now(),
                'role_id' => 1,
                'is_active' => true
            ]);
        }
        return $user;
    }

    private function createStudents($tenantId, $schoolId, $userId)
    {
        $students = [];
        $studentData = [
            [
                'student_number' => 'STU001',
                'first_name' => 'João',
                'last_name' => 'Silva',
                'date_of_birth' => '2010-01-15',
                'gender' => 'male',
                'current_grade_level' => '7th Grade',
                'enrollment_status' => 'enrolled'
            ],
            [
                'student_number' => 'STU002',
                'first_name' => 'Maria',
                'last_name' => 'Santos',
                'date_of_birth' => '2010-03-22',
                'gender' => 'female',
                'current_grade_level' => '7th Grade',
                'enrollment_status' => 'enrolled'
            ],
            [
                'student_number' => 'STU003',
                'first_name' => 'Pedro',
                'last_name' => 'Oliveira',
                'date_of_birth' => '2009-07-10',
                'gender' => 'male',
                'current_grade_level' => '8th Grade',
                'enrollment_status' => 'enrolled'
            ],
            [
                'student_number' => 'STU004',
                'first_name' => 'Ana',
                'last_name' => 'Costa',
                'date_of_birth' => '2009-11-05',
                'gender' => 'female',
                'current_grade_level' => '8th Grade',
                'enrollment_status' => 'enrolled'
            ],
            [
                'student_number' => 'STU005',
                'first_name' => 'Carlos',
                'last_name' => 'Ferreira',
                'date_of_birth' => '2011-02-18',
                'gender' => 'male',
                'current_grade_level' => '6th Grade',
                'enrollment_status' => 'enrolled'
            ]
        ];

        foreach ($studentData as $data) {
            $student = Student::where('student_number', $data['student_number'])->first();
            if (!$student) {
                $student = Student::create([
                    'tenant_id' => $tenantId,
                    'user_id' => $userId,
                    'school_id' => $schoolId,
                    'student_number' => $data['student_number'],
                    'first_name' => $data['first_name'],
                    'last_name' => $data['last_name'],
                    'date_of_birth' => $data['date_of_birth'],
                    'gender' => $data['gender'],
                    'admission_date' => now()->subMonths(6)->toDateString(),
                    'current_grade_level' => $data['current_grade_level'],
                    'enrollment_status' => $data['enrollment_status']
                ]);
            }
            $students[] = $student;
        }

        return $students;
    }

    private function createClasses($tenantId, $schoolId)
    {
        $classes = [];
        $classData = [
            [
                'name' => '7th Grade Mathematics',
                'section' => 'A',
                'class_code' => 'MATH7A',
                'grade_level' => '7th Grade',
                'max_students' => 30,
                'room_number' => '201'
            ],
            [
                'name' => '7th Grade Portuguese',
                'section' => 'A',
                'class_code' => 'PORT7A',
                'grade_level' => '7th Grade',
                'max_students' => 30,
                'room_number' => '202'
            ],
            [
                'name' => '8th Grade Science',
                'section' => 'B',
                'class_code' => 'SCI8B',
                'grade_level' => '8th Grade',
                'max_students' => 25,
                'room_number' => '301'
            ],
            [
                'name' => '6th Grade History',
                'section' => 'A',
                'class_code' => 'HIST6A',
                'grade_level' => '6th Grade',
                'max_students' => 28,
                'room_number' => '101'
            ]
        ];

        // Get required foreign keys
        $subjectId = $this->getOrCreateSubject($tenantId, $schoolId);
        $academicYearId = $this->getOrCreateAcademicYear($tenantId, $schoolId);

        foreach ($classData as $data) {
            $class = AcademicClass::where('class_code', $data['class_code'])->first();
            if (!$class) {
                $class = AcademicClass::create([
                    'school_id' => $schoolId,
                    'tenant_id' => $tenantId,
                    'subject_id' => $subjectId,
                    'academic_year_id' => $academicYearId,
                    'name' => $data['name'],
                    'section' => $data['section'],
                    'class_code' => $data['class_code'],
                    'grade_level' => $data['grade_level'],
                    'max_students' => $data['max_students'],
                    'current_enrollment' => 0,
                    'room_number' => $data['room_number'],
                    'status' => 'active'
                ]);
            }
            $classes[] = $class;
        }

        return $classes;
    }

    private function getOrCreateSubject($tenantId, $schoolId)
    {
        $subject = DB::table('subjects')->where('tenant_id', $tenantId)->first();
        if (!$subject) {
            $subjectId = DB::table('subjects')->insertGetId([
                'tenant_id' => $tenantId,
                'school_id' => $schoolId,
                'name' => 'Mathematics',
                'code' => 'MATH',
                'description' => 'Mathematics subject',
                'subject_area' => 'mathematics',
                'grade_levels' => json_encode(['6th Grade', '7th Grade', '8th Grade']),
                'credit_hours' => 1.0,
                'is_core_subject' => true,
                'is_elective' => false,
                'status' => 'active',
                'created_at' => now(),
                'updated_at' => now()
            ]);
        } else {
            $subjectId = $subject->id;
        }
        return $subjectId;
    }

    private function getOrCreateAcademicYear($tenantId, $schoolId)
    {
        $academicYear = DB::table('academic_years')->where('tenant_id', $tenantId)->first();
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
        return $academicYearId;
    }

    private function createEnrollments($students, $classes)
    {
        $enrollments = [
            // João Silva enrollments
            ['student' => 0, 'class' => 0, 'status' => 'active', 'final_grade' => 'A'],
            ['student' => 0, 'class' => 1, 'status' => 'active', 'final_grade' => 'B+'],
            
            // Maria Santos enrollments
            ['student' => 1, 'class' => 0, 'status' => 'active', 'final_grade' => 'A-'],
            ['student' => 1, 'class' => 1, 'status' => 'active', 'final_grade' => 'A'],
            
            // Pedro Oliveira enrollments
            ['student' => 2, 'class' => 2, 'status' => 'active', 'final_grade' => 'B'],
            
            // Ana Costa enrollments
            ['student' => 3, 'class' => 2, 'status' => 'active', 'final_grade' => 'A-'],
            
            // Carlos Ferreira enrollments
            ['student' => 4, 'class' => 3, 'status' => 'active', 'final_grade' => 'B+'],
        ];

        foreach ($enrollments as $enrollment) {
            $student = $students[$enrollment['student']];
            $class = $classes[$enrollment['class']];
            
            // Check if enrollment already exists
            $existingEnrollment = DB::table('student_class_enrollments')
                ->where('student_id', $student->id)
                ->where('class_id', $class->id)
                ->first();
                
            if (!$existingEnrollment) {
                DB::table('student_class_enrollments')->insert([
                    'student_id' => $student->id,
                    'class_id' => $class->id,
                    'enrollment_date' => now()->subMonths(2)->toDateString(),
                    'status' => $enrollment['status'],
                    'final_grade' => $enrollment['final_grade'],
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
                
                // Update class enrollment count
                $class->increment('current_enrollment');
            }
        }
    }
}
