<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AcademicPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Academic Year permissions
            'academic_years.view',
            'academic_years.create',
            'academic_years.edit',
            'academic_years.delete',
            'academic_years.manage',

            // School permissions
            'schools.view',
            'schools.create',
            'schools.edit',
            'schools.delete',
            'schools.manage',

            // Student permissions
            'students.view',
            'students.create',
            'students.edit',
            'students.delete',
            'students.manage',
            'students.enroll',
            'students.transfer',
            'students.graduate',

            // Teacher permissions
            'teachers.view',
            'teachers.create',
            'teachers.edit',
            'teachers.delete',
            'teachers.manage',
            'teachers.assign_subjects',
            'teachers.assign_classes',

            // Subject permissions
            'subjects.view',
            'subjects.create',
            'subjects.edit',
            'subjects.delete',
            'subjects.manage',

            // Class permissions
            'classes.view',
            'classes.create',
            'classes.edit',
            'classes.delete',
            'classes.manage',
            'classes.assign_students',
            'classes.assign_teachers',

            // Schedule permissions
            'schedules.view',
            'schedules.create',
            'schedules.edit',
            'schedules.delete',
            'schedules.manage',

            // Lesson permissions
            'lessons.view',
            'lessons.create',
            'lessons.edit',
            'lessons.delete',
            'lessons.manage',
            'lessons.attendance',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['category' => 'academic']
            );
        }
    }
}
