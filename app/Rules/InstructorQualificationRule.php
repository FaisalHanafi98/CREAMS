<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\User;
use App\Models\Category;

class InstructorQualificationRule implements Rule
{
    protected $categoryId;
    protected $message;

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function passes($attribute, $value)
    {
        if (!$value || !$this->categoryId) {
            return false;
        }

        $instructor = User::find($value);
        $category = Category::find($this->categoryId);

        if (!$instructor || !$category) {
            $this->message = 'Invalid instructor or category selected.';
            return false;
        }

        // Check if instructor has teaching role
        if (!in_array($instructor->role, ['teacher', 'supervisor', 'admin'])) {
            $this->message = 'Selected user is not authorized to teach activities.';
            return false;
        }

        // Faith-based activities - anyone can teach but prefer qualified
        if ($category->category_type === 'faith') {
            // Check if instructor has religious qualifications
            $hasReligiousQualification = $this->hasReligiousQualification($instructor);
            
            if (!$hasReligiousQualification) {
                // Allow but add warning message
                $this->message = 'Warning: Selected instructor does not have religious qualifications. Consider selecting someone with Islamic studies background for better outcomes.';
                return true; // Still allow
            }
            return true;
        }

        // Check specific qualifications for other categories
        $isQualified = $this->checkCategoryQualification($instructor, $category);
        
        if (!$isQualified) {
            $requiredQuals = $this->getRequiredQualifications($category);
            $this->message = "Instructor does not have required qualifications for {$category->category_type} activities. Required: " . implode(', ', $requiredQuals);
            return false;
        }

        return true;
    }

    public function message()
    {
        return $this->message ?? 'The selected instructor is not qualified to teach this type of activity.';
    }

    private function hasReligiousQualification($instructor)
    {
        $religiousKeywords = ['islamic', 'religion', 'quran', 'hadith', 'fiqh', 'theology', 'divinity'];
        
        $educationSpec = strtolower($instructor->education_specialization ?? '');
        $teachingSpec = strtolower($instructor->teaching_specialization ?? '');
        
        foreach ($religiousKeywords as $keyword) {
            if (str_contains($educationSpec, $keyword) || str_contains($teachingSpec, $keyword)) {
                return true;
            }
        }
        
        return false;
    }

    private function checkCategoryQualification($instructor, $category)
    {
        $requiredQuals = $this->getRequiredQualifications($category);
        
        $educationSpec = strtolower($instructor->education_specialization ?? '');
        $teachingSpec = strtolower($instructor->teaching_specialization ?? '');
        
        // Check if instructor has any of the required qualifications
        foreach ($requiredQuals as $qualification) {
            if (str_contains($educationSpec, strtolower($qualification)) || 
                str_contains($teachingSpec, strtolower($qualification))) {
                return true;
            }
        }
        
        return false;
    }

    private function getRequiredQualifications($category)
    {
        $qualificationMap = [
            'rehabilitation' => [
                'Physical Therapy', 'Occupational Therapy', 'Physiotherapy', 
                'Rehabilitation', 'Special Education', 'Speech Therapy',
                'Behavioral Therapy', 'Psychology'
            ],
            'academic' => [
                'Education', 'Teaching', 'Special Education', 'Early Childhood Education',
                'Learning Disabilities', 'Academic Support', 'Tutoring'
            ],
            'recreational' => [
                'Recreation Therapy', 'Arts Therapy', 'Music Therapy',
                'Physical Education', 'Sports Therapy', 'Creative Arts'
            ],
            'faith' => [
                'Islamic Studies', 'Religious Studies', 'Theology',
                'Quran Studies', 'Islamic Education'
            ]
        ];

        return $qualificationMap[$category->category_type] ?? [];
    }
}