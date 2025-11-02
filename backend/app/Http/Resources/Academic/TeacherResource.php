<?php

namespace App\Http\Resources\Academic;

use Illuminate\Http\Request;

class TeacherResource extends BaseAcademicResource
{
    /**
     * Transform the resource into an array
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'school_id' => $this->school_id,
            'user_id' => $this->user_id,
            'employee_id' => $this->employee_id,
            'first_name' => $this->first_name,
            'middle_name' => $this->middle_name,
            'last_name' => $this->last_name,
            'preferred_name' => $this->preferred_name,
            'title' => $this->title,
            'full_name' => trim($this->first_name . ' ' . ($this->middle_name ?? '') . ' ' . $this->last_name),
            'display_name' => $this->preferred_name ?: trim($this->first_name . ' ' . $this->last_name),
            'date_of_birth' => $this->formatDate($this->date_of_birth),
            'gender' => $this->gender,
            'nationality' => $this->nationality,
            'phone' => $this->phone,
            'email' => $this->email,
            'address_json' => $this->address_json,
            'employment_type' => $this->employment_type,
            'hire_date' => $this->formatDate($this->hire_date),
            'termination_date' => $this->formatDate($this->termination_date),
            'status' => $this->status,
            'education_json' => $this->education_json,
            'certifications_json' => $this->certifications_json,
            'specializations_json' => $this->specializations_json,
            'department' => $this->department,
            'position' => $this->position,
            'salary' => $this->formatDecimal($this->salary),
            'schedule_json' => $this->schedule_json,
            'emergency_contacts_json' => $this->emergency_contacts_json,
            'bio' => $this->bio,
            'profile_photo_path' => $this->profile_photo_path,
            'preferences_json' => $this->preferences_json,
            'created_at' => $this->formatDateTime($this->created_at),
            'updated_at' => $this->formatDateTime($this->updated_at),
            
            // Relationships
            'user' => $this->whenLoaded('user'),
            'school' => $this->whenLoaded('school'),
            'subjects' => $this->whenLoaded('subjects'),
            'classes' => $this->whenLoaded('classes'),
        ];
    }
}
