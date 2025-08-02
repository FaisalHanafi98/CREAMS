<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class InstructorCompatibilityController extends Controller
{
    /**
     * Check instructor compatibility with activity category
     */
    public function checkCompatibility(Request $request, $instructorId)
    {
        try {
            $instructor = User::findOrFail($instructorId);
            $categoryType = $request->get('category_type');
            $categoryName = $request->get('category_name');
            
            $warnings = $this->generateCompatibilityWarnings($instructor, $categoryType, $categoryName);
            
            return response()->json([
                'instructor_id' => $instructorId,
                'category_type' => $categoryType,
                'category_name' => $categoryName,
                'warnings' => $warnings,
                'is_compatible' => empty($warnings)
            ]);
            
        } catch (\Exception $e) {
            Log::error('Error checking instructor compatibility', [
                'instructor_id' => $instructorId,
                'error' => $e->getMessage()
            ]);
            
            return response()->json([
                'error' => 'Unable to check compatibility'
            ], 500);
        }
    }
    
    /**
     * Generate compatibility warnings based on instructor qualifications
     */
    private function generateCompatibilityWarnings($instructor, $categoryType, $categoryName)
    {
        $warnings = [];
        
        $educationLevel = strtolower($instructor->education_level ?? '');
        $educationSpecialization = strtolower($instructor->education_specialization ?? '');
        $teachingSpecialization = strtolower($instructor->teaching_specialization ?? '');
        
        // Define ideal qualifications for each category type
        $idealQualifications = [
            'rehabilitation' => [
                'education_levels' => ['bachelor', 'master', 'phd', 'degree'],
                'specializations' => [
                    'physical therapy', 'occupational therapy', 'speech therapy', 
                    'physiotherapy', 'rehabilitation', 'special education',
                    'behavioral therapy', 'psychology', 'special needs'
                ]
            ],
            'academic' => [
                'education_levels' => ['bachelor', 'master', 'phd', 'degree', 'diploma'],
                'specializations' => [
                    'education', 'special education', 'mathematics', 'science',
                    'computer science', 'literacy', 'teaching', 'curriculum',
                    'learning disabilities', 'academic support'
                ]
            ],
            'creative_social' => [
                'education_levels' => ['bachelor', 'master', 'diploma', 'certificate'],
                'specializations' => [
                    'art therapy', 'music therapy', 'social work', 'psychology',
                    'creative arts', 'fine arts', 'music', 'drama therapy',
                    'recreational therapy', 'social skills training'
                ]
            ]
        ];
        
        $categoryRequirements = $idealQualifications[$categoryType] ?? [];
        
        // Check education level
        $hasRequiredEducationLevel = false;
        if (!empty($categoryRequirements['education_levels'])) {
            foreach ($categoryRequirements['education_levels'] as $requiredLevel) {
                if (strpos($educationLevel, $requiredLevel) !== false) {
                    $hasRequiredEducationLevel = true;
                    break;
                }
            }
        }
        
        // Check specialization match
        $hasRelevantSpecialization = false;
        if (!empty($categoryRequirements['specializations'])) {
            $allSpecializations = $educationSpecialization . ' ' . $teachingSpecialization;
            
            foreach ($categoryRequirements['specializations'] as $requiredSpec) {
                if (strpos($allSpecializations, $requiredSpec) !== false) {
                    $hasRelevantSpecialization = true;
                    break;
                }
            }
        }
        
        // Generate warnings based on gaps
        if (!$hasRequiredEducationLevel && !empty($educationLevel)) {
            $warnings[] = "Education level may not be optimal for {$categoryName} activities";
        }
        
        if (!$hasRelevantSpecialization) {
            $warnings[] = "No specialized training found for {$categoryName} activities";
            $warnings[] = "Consider providing additional training or supervision";
        }
        
        // Role-specific warnings
        if ($instructor->role === 'ajk') {
            $warnings[] = "AJK role may require additional supervision for specialized activities";
        }
        
        // Experience warnings (if we had experience fields)
        if (empty($instructor->teaching_specialization) && empty($instructor->education_specialization)) {
            $warnings[] = "Limited specialization information available for compatibility assessment";
        }
        
        return $warnings;
    }
}
