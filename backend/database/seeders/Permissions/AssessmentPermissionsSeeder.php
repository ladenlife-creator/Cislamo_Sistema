<?php

namespace Database\Seeders\Permissions;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;

class AssessmentPermissionsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            // Assessment permissions
            'assessments.view',
            'assessments.create',
            'assessments.edit',
            'assessments.delete',
            'assessments.manage',
            'assessments.publish',
            'assessments.grade',

            // Grade permissions
            'grades.view',
            'grades.create',
            'grades.edit',
            'grades.delete',
            'grades.manage',
            'grades.export',
            'grades.import',

            // Gradebook permissions
            'gradebooks.view',
            'gradebooks.create',
            'gradebooks.edit',
            'gradebooks.delete',
            'gradebooks.manage',
            'gradebooks.publish',

            // Grade review permissions
            'grade_reviews.view',
            'grade_reviews.create',
            'grade_reviews.edit',
            'grade_reviews.delete',
            'grade_reviews.manage',
            'grade_reviews.approve',

            // Assessment settings permissions
            'assessment_settings.view',
            'assessment_settings.edit',
            'assessment_settings.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['name' => $permission, 'guard_name' => 'api'],
                ['category' => 'assessment']
            );
        }
    }
}
