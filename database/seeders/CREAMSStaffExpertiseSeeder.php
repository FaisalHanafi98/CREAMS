<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CREAMSStaffExpertiseSeeder extends Seeder
{
    /**
     * Run the database seeds to add expertise data to existing staff
     */
    public function run(): void
    {
        $this->command->info('🎓 Adding expertise and qualification data to staff...');

        $staff = User::whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])->get();

        foreach ($staff as $staffMember) {
            $expertise = $this->generateExpertiseData($staffMember);
            
            $staffMember->update($expertise);
        }

        $this->command->info("✅ Updated {$staff->count()} staff members with expertise data");
        $this->showExpertiseStatistics();
    }

    /**
     * Generate realistic expertise data based on staff role and specialization
     */
    private function generateExpertiseData($staff)
    {
        $expertise = [];

        // Experience years based on role and age
        $expertise['experience_years'] = $this->generateExperienceYears($staff->role);

        // Certifications based on specialization
        $expertise['certifications'] = $this->generateCertifications($staff);

        // Disability expertise
        $expertise['disability_expertise'] = json_encode($this->generateDisabilityExpertise($staff));

        // Age group preferences
        $expertise['age_group_preference'] = json_encode($this->generateAgeGroupPreference($staff));

        // Additional skills
        $expertise['additional_skills'] = $this->generateAdditionalSkills($staff);

        // Qualification score
        $expertise['qualification_score'] = $this->calculateQualificationScore($staff, $expertise);

        return $expertise;
    }

    /**
     * Generate experience years
     */
    private function generateExperienceYears($role)
    {
        switch ($role) {
            case 'admin':
                return rand(8, 20);
            case 'supervisor':
                return rand(5, 15);
            case 'teacher':
                return rand(2, 12);
            case 'ajk':
                return rand(1, 8);
            default:
                return rand(1, 5);
        }
    }

    /**
     * Generate certifications based on specialization
     */
    private function generateCertifications($staff)
    {
        $baseCertifications = [];
        
        // Add certifications based on education specialization
        if ($staff->education_specialization) {
            $eduSpec = strtolower($staff->education_specialization);
            
            if (str_contains($eduSpec, 'occupational therapy')) {
                $baseCertifications[] = 'Certified Occupational Therapist (COT)';
                $baseCertifications[] = 'Malaysian Occupational Therapy Association (MOTA) Member';
            }
            
            if (str_contains($eduSpec, 'physical therapy') || str_contains($eduSpec, 'physiotherapy')) {
                $baseCertifications[] = 'Licensed Physiotherapist Malaysia';
                $baseCertifications[] = 'Malaysian Physiotherapy Association (MPA) Member';
            }
            
            if (str_contains($eduSpec, 'speech therapy')) {
                $baseCertifications[] = 'Certified Speech Language Pathologist';
                $baseCertifications[] = 'Malaysian Association of Speech-Language and Hearing (MASH) Member';
            }
            
            if (str_contains($eduSpec, 'psychology')) {
                $baseCertifications[] = 'Licensed Clinical Psychologist';
                $baseCertifications[] = 'Malaysian Psychological Association (PSIMA) Member';
            }
            
            if (str_contains($eduSpec, 'education') || str_contains($eduSpec, 'teaching')) {
                $baseCertifications[] = 'Teaching License Malaysia';
                $baseCertifications[] = 'Special Education Certification';
            }
        }

        // Add role-based certifications
        switch ($staff->role) {
            case 'admin':
                $baseCertifications[] = 'Healthcare Administration Certification';
                $baseCertifications[] = 'ISO 9001:2015 Quality Management';
                break;
            case 'supervisor':
                $baseCertifications[] = 'Team Leadership Certification';
                $baseCertifications[] = 'First Aid & CPR Certified';
                break;
            case 'teacher':
                $baseCertifications[] = 'First Aid & CPR Certified';
                $baseCertifications[] = 'Behavioral Management Training';
                break;
        }

        // Add some random additional certifications
        $additionalCerts = [
            'Autism Spectrum Disorder Training',
            'Applied Behavior Analysis (ABA) Certification',
            'Assistive Technology Training',
            'Family-Centered Care Training',
            'Crisis Intervention Training',
            'Multi-sensory Learning Approaches',
            'Inclusive Education Training'
        ];

        $numAdditional = rand(1, 3);
        $randomCerts = array_rand(array_flip($additionalCerts), $numAdditional);
        if (!is_array($randomCerts)) $randomCerts = [$randomCerts];
        
        $baseCertifications = array_merge($baseCertifications, $randomCerts);

        return implode('; ', array_unique($baseCertifications));
    }

    /**
     * Generate disability expertise
     */
    private function generateDisabilityExpertise($staff)
    {
        $allDisabilities = [
            'Autism Spectrum Disorder',
            'Down Syndrome',
            'Cerebral Palsy',
            'Intellectual Disability',
            'Attention Deficit Hyperactivity Disorder (ADHD)',
            'Speech and Language Disorders',
            'Hearing Impairment',
            'Visual Impairment',
            'Physical Disabilities',
            'Learning Disabilities',
            'Developmental Delays',
            'Behavioral Disorders'
        ];

        // Number of expertise areas based on experience
        $experienceYears = $this->generateExperienceYears($staff->role);
        $numExpertise = min(6, max(2, floor($experienceYears / 2)));

        // Select expertise based on teaching specialization if available
        $selectedExpertise = [];
        
        if ($staff->teaching_specialization) {
            $teachSpec = strtolower($staff->teaching_specialization);
            
            if (str_contains($teachSpec, 'autism')) {
                $selectedExpertise[] = 'Autism Spectrum Disorder';
            }
            if (str_contains($teachSpec, 'speech')) {
                $selectedExpertise[] = 'Speech and Language Disorders';
            }
            if (str_contains($teachSpec, 'physical')) {
                $selectedExpertise[] = 'Physical Disabilities';
                $selectedExpertise[] = 'Cerebral Palsy';
            }
            if (str_contains($teachSpec, 'down syndrome')) {
                $selectedExpertise[] = 'Down Syndrome';
            }
        }

        // Fill remaining slots randomly
        $remainingSlots = $numExpertise - count($selectedExpertise);
        if ($remainingSlots > 0) {
            $availableDisabilities = array_diff($allDisabilities, $selectedExpertise);
            $randomExpertise = array_rand(array_flip($availableDisabilities), min($remainingSlots, count($availableDisabilities)));
            if (!is_array($randomExpertise)) $randomExpertise = [$randomExpertise];
            $selectedExpertise = array_merge($selectedExpertise, $randomExpertise);
        }

        return array_values(array_unique($selectedExpertise));
    }

    /**
     * Generate age group preferences
     */
    private function generateAgeGroupPreference($staff)
    {
        $ageGroups = [
            'Early Childhood (0-5 years)',
            'School Age (6-12 years)',
            'Adolescents (13-18 years)',
            'Young Adults (19-25 years)',
            'Adults (26+ years)'
        ];

        // Select 2-3 age groups based on role and experience
        $numGroups = rand(2, 3);
        $selectedGroups = array_rand(array_flip($ageGroups), $numGroups);
        if (!is_array($selectedGroups)) $selectedGroups = [$selectedGroups];

        return array_values($selectedGroups);
    }

    /**
     * Generate additional skills
     */
    private function generateAdditionalSkills($staff)
    {
        $skills = [
            'Bilingual (Bahasa Malaysia/English)',
            'Sign Language Proficiency',
            'Computer Assisted Learning',
            'Art Therapy Techniques',
            'Music Therapy',
            'Sensory Integration',
            'Assistive Technology',
            'Behavioral Assessment',
            'IEP Development',
            'Parent Counseling',
            'Team Collaboration',
            'Documentation and Reporting',
            'Crisis Management',
            'Cultural Sensitivity Training'
        ];

        $numSkills = rand(3, 6);
        $selectedSkills = array_rand(array_flip($skills), $numSkills);
        if (!is_array($selectedSkills)) $selectedSkills = [$selectedSkills];

        return implode('; ', $selectedSkills);
    }

    /**
     * Calculate qualification score based on various factors
     */
    private function calculateQualificationScore($staff, $expertise)
    {
        $score = 0;

        // Base score by role
        $roleScores = [
            'admin' => 85,
            'supervisor' => 80,
            'teacher' => 75,
            'ajk' => 65
        ];
        $score += $roleScores[$staff->role] ?? 60;

        // Experience bonus (up to 15 points)
        $experienceYears = $expertise['experience_years'] ?? 0;
        $score += min(15, $experienceYears * 1.5);

        // Education level bonus
        if ($staff->education_level) {
            $eduLevel = strtolower($staff->education_level);
            if (str_contains($eduLevel, 'phd') || str_contains($eduLevel, 'doctorate')) {
                $score += 15;
            } elseif (str_contains($eduLevel, 'master')) {
                $score += 10;
            } elseif (str_contains($eduLevel, 'bachelor') || str_contains($eduLevel, 'degree')) {
                $score += 5;
            }
        }

        // Specialization bonus
        if ($staff->education_specialization || $staff->teaching_specialization) {
            $score += 5;
        }

        // Expertise diversity bonus
        $disabilityExpertise = json_decode($expertise['disability_expertise'], true) ?? [];
        $score += count($disabilityExpertise) * 2;

        // Cap at 100
        return min(100, round($score, 2));
    }

    /**
     * Show expertise statistics
     */
    private function showExpertiseStatistics()
    {
        $this->command->info("\n📊 STAFF EXPERTISE STATISTICS:");
        
        $avgScore = DB::table('users')
            ->whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])
            ->avg('qualification_score');
            
        $highQualified = DB::table('users')
            ->whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])
            ->where('qualification_score', '>=', 80)
            ->count();
            
        $totalStaff = DB::table('users')
            ->whereIn('role', ['admin', 'supervisor', 'teacher', 'ajk'])
            ->count();

        $this->command->line("   📈 Average Qualification Score: " . round($avgScore, 1));
        $this->command->line("   🌟 Highly Qualified Staff (80+): {$highQualified}/{$totalStaff}");
        
        // Show expertise by role
        $roles = ['admin', 'supervisor', 'teacher', 'ajk'];
        foreach ($roles as $role) {
            $roleAvg = DB::table('users')
                ->where('role', $role)
                ->avg('qualification_score');
            $this->command->line("   {$role}: " . round($roleAvg, 1) . " avg score");
        }
    }
}