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

        if (!$instructor) {
            $this->message = 'Invalid instructor selected.';
            return false;
        }

        // Check if instructor has teaching role
        if (!in_array($instructor->role, ['teacher', 'supervisor', 'admin'])) {
            $this->message = 'Selected user is not authorized to teach activities.';
            return false;
        }

        // Resolve category: try DB first, fall back to enum-based mapping
        $category = null;
        try {
            $category = Category::find($this->categoryId);
        } catch (\Exception $e) {
            // activity_categories table may not exist; use enum mapping
        }

        $categoryType = $category->category_type ?? $this->resolveCategoryType($this->categoryId);

        if (!$categoryType) {
            // Unknown category — allow instructor (category validated elsewhere)
            return true;
        }

        // Faith-based activities - anyone can teach but prefer qualified
        if ($categoryType === 'faith') {
            $hasReligiousQualification = $this->hasReligiousQualification($instructor);

            if (!$hasReligiousQualification) {
                $this->message = 'Warning: Selected instructor does not have religious qualifications. Consider selecting someone with Islamic studies background for better outcomes.';
                return true; // Still allow
            }
            return true;
        }

        // Check specific qualifications for other categories
        $isQualified = $this->checkCategoryQualificationByType($instructor, $categoryType);

        if (!$isQualified) {
            $requiredQuals = $this->getRequiredQualificationsByType($categoryType);
            $this->message = "Instructor does not have required qualifications for {$categoryType} activities. Required: " . implode(', ', $requiredQuals);
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

    private function resolveCategoryType($categoryName)
    {
        $categoryTypeMap = [
            'Autism Spectrum Support' => 'rehabilitation',
            'Hearing Impairment' => 'rehabilitation',
            'Visual Impairment' => 'rehabilitation',
            'Physical Disabilities' => 'rehabilitation',
            'Learning Support' => 'academic',
            'Speech Therapy' => 'rehabilitation',
        ];

        return $categoryTypeMap[$categoryName] ?? null;
    }

    private function checkCategoryQualification($instructor, $category)
    {
        return $this->checkCategoryQualificationByType($instructor, $category->category_type);
    }

    private function checkCategoryQualificationByType($instructor, $categoryType)
    {
        $requiredQuals = $this->getRequiredQualificationsByType($categoryType);

        $educationSpec = strtolower($instructor->education_specialization ?? '');
        $teachingSpec = strtolower($instructor->teaching_specialization ?? '');

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
        return $this->getRequiredQualificationsByType($category->category_type);
    }

    private function getRequiredQualificationsByType($categoryType)
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

        return $qualificationMap[$categoryType] ?? [];
    }
}