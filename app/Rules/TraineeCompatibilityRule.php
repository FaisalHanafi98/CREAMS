<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use App\Models\Trainee;
use App\Models\Category;

class TraineeCompatibilityRule implements Rule
{
    protected $categoryId;
    protected $incompatibleTrainees = [];

    public function __construct($categoryId)
    {
        $this->categoryId = $categoryId;
    }

    public function passes($attribute, $value)
    {
        if (!$value || !$this->categoryId) {
            return true; // No participants selected is valid
        }

        $category = Category::find($this->categoryId);
        if (!$category) {
            return false;
        }

        // Parse participant IDs (comma-separated string or array)
        $participantIds = is_array($value) ? $value : explode(',', $value);
        $participantIds = array_filter(array_map('trim', $participantIds));

        $this->incompatibleTrainees = [];

        foreach ($participantIds as $traineeId) {
            $trainee = Trainee::find($traineeId);
            if (!$trainee) continue;

            $incompatibilityReasons = $this->checkTraineeCompatibility($trainee, $category);
            if (!empty($incompatibilityReasons)) {
                $this->incompatibleTrainees[] = [
                    'name' => $trainee->name,
                    'reasons' => $incompatibilityReasons
                ];
            }
        }

        return empty($this->incompatibleTrainees);
    }

    public function message()
    {
        if (empty($this->incompatibleTrainees)) {
            return 'Some selected participants are not compatible with this activity.';
        }

        $messages = [];
        foreach ($this->incompatibleTrainees as $incompatible) {
            $reasons = implode(', ', $incompatible['reasons']);
            $messages[] = "{$incompatible['name']}: {$reasons}";
        }

        return 'The following participants may not be suitable for this activity: ' . implode('; ', $messages);
    }

    private function checkTraineeCompatibility($trainee, $category)
    {
        $incompatibilityReasons = [];

        // Get trainee conditions (assuming these fields exist or are in a JSON field)
        $conditions = $this->getTraineeConditions($trainee);

        // Check category-specific requirements
        switch ($category->category_type) {
            case 'rehabilitation':
                // Most rehabilitation activities are adaptive
                break;

            case 'academic':
                // Check cognitive compatibility
                if (isset($conditions['severe_cognitive_impairment']) && $conditions['severe_cognitive_impairment']) {
                    // Check if it's a basic/adaptive academic program
                    $categoryName = strtolower($category->category_name);
                    if (!str_contains($categoryName, 'basic') && !str_contains($categoryName, 'adaptive')) {
                        $incompatibilityReasons[] = 'may need adaptive academic program due to cognitive challenges';
                    }
                }
                break;

            case 'recreational':
                // Check specific recreational activity requirements
                $categoryName = strtolower($category->category_name);
                
                if (str_contains($categoryName, 'music') || str_contains($categoryName, 'audio')) {
                    if (isset($conditions['hearing_impaired']) && $conditions['hearing_impaired']) {
                        $incompatibilityReasons[] = 'hearing impairment may limit participation in audio-based activities';
                    }
                }
                
                if (str_contains($categoryName, 'visual') || str_contains($categoryName, 'art') || str_contains($categoryName, 'reading')) {
                    if (isset($conditions['visually_impaired']) && $conditions['visually_impaired']) {
                        $incompatibilityReasons[] = 'visual impairment may require special accommodations for visual activities';
                    }
                }
                
                if (str_contains($categoryName, 'physical') || str_contains($categoryName, 'sports') || str_contains($categoryName, 'movement')) {
                    if (isset($conditions['mobility_impaired']) && $conditions['mobility_impaired']) {
                        $incompatibilityReasons[] = 'mobility limitations may require adaptive physical activities';
                    }
                }
                break;

            case 'faith':
                // Faith activities are generally inclusive
                // Check for any behavioral considerations
                if (isset($conditions['severe_behavioral_issues']) && $conditions['severe_behavioral_issues']) {
                    $incompatibilityReasons[] = 'may need additional behavioral support during group faith activities';
                }
                break;
        }

        // Check age appropriateness if category has age restrictions
        if (method_exists($category, 'getAgeRangeAttribute')) {
            $ageRange = $category->age_range;
            $traineeAge = $trainee->age ?? $this->calculateAge($trainee->date_of_birth ?? null);
            
            if ($ageRange && $traineeAge && !$this->isAgeAppropriate($traineeAge, $ageRange)) {
                $incompatibilityReasons[] = "age ({$traineeAge}) may not be suitable for this activity's target age range";
            }
        }

        return $incompatibilityReasons;
    }

    private function getTraineeConditions($trainee)
    {
        // Check if trainee has a conditions JSON field or individual boolean fields
        if (isset($trainee->conditions) && is_string($trainee->conditions)) {
            return json_decode($trainee->conditions, true) ?? [];
        }

        // Fallback to individual fields if they exist
        $conditions = [];
        $conditionFields = [
            'hearing_impaired', 'visually_impaired', 'mobility_impaired',
            'cognitive_impairment', 'behavioral_issues', 'speech_impairment',
            'autism_spectrum', 'down_syndrome', 'cerebral_palsy'
        ];

        foreach ($conditionFields as $field) {
            if (isset($trainee->$field)) {
                $conditions[$field] = $trainee->$field;
            }
        }

        // Parse trainee_condition field if it exists
        if (isset($trainee->trainee_condition) && is_string($trainee->trainee_condition)) {
            $conditionText = strtolower($trainee->trainee_condition);
            
            // Simple keyword detection
            $conditions['hearing_impaired'] = str_contains($conditionText, 'hearing') || str_contains($conditionText, 'deaf');
            $conditions['visually_impaired'] = str_contains($conditionText, 'visual') || str_contains($conditionText, 'blind');
            $conditions['mobility_impaired'] = str_contains($conditionText, 'mobility') || str_contains($conditionText, 'wheelchair');
            $conditions['cognitive_impairment'] = str_contains($conditionText, 'cognitive') || str_contains($conditionText, 'intellectual');
            $conditions['autism_spectrum'] = str_contains($conditionText, 'autism') || str_contains($conditionText, 'asd');
        }

        return $conditions;
    }

    private function calculateAge($dateOfBirth)
    {
        if (!$dateOfBirth) return null;
        
        try {
            return \Carbon\Carbon::parse($dateOfBirth)->age;
        } catch (\Exception $e) {
            return null;
        }
    }

    private function isAgeAppropriate($age, $ageRange)
    {
        // Simple age range checking (can be enhanced based on your age range format)
        if (is_string($ageRange)) {
            if (str_contains($ageRange, '-')) {
                [$min, $max] = explode('-', $ageRange);
                return $age >= (int)$min && $age <= (int)$max;
            }
        }
        
        return true; // Default to appropriate if can't parse
    }
}