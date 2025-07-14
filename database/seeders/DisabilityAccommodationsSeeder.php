<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DisabilityAccommodationsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $accommodations = [
            // Autism Spectrum Disorder
            [
                'disability_type' => 'Autism Spectrum Disorder',
                'subject_category' => 'bahasa_malaysia',
                'recommended_duration_minutes' => 30,
                'break_frequency_minutes' => 15,
                'teaching_strategies' => json_encode([
                    'visual_schedules',
                    'concrete_examples', 
                    'repetitive_practice',
                    'sensory_breaks',
                    'structured_routine'
                ]),
                'assessment_modifications' => json_encode([
                    'visual_cues',
                    'shorter_assessments',
                    'alternative_response_formats'
                ]),
                'special_notes' => 'May need sensory breaks. Use visual supports and maintain consistent routine.'
            ],
            [
                'disability_type' => 'Autism Spectrum Disorder',
                'subject_category' => 'mathematics',
                'recommended_duration_minutes' => 25,
                'break_frequency_minutes' => 10,
                'teaching_strategies' => json_encode([
                    'concrete_manipulatives',
                    'visual_number_lines',
                    'step_by_step_instructions',
                    'real_world_applications'
                ]),
                'assessment_modifications' => json_encode([
                    'extended_time',
                    'calculator_allowed',
                    'visual_aids'
                ]),
                'special_notes' => 'Focus on concrete concepts before abstract. Use visual math aids.'
            ],
            
            // Down Syndrome
            [
                'disability_type' => 'Down Syndrome',
                'subject_category' => 'english_language',
                'recommended_duration_minutes' => 35,
                'break_frequency_minutes' => 20,
                'teaching_strategies' => json_encode([
                    'multisensory_approach',
                    'phonics_based',
                    'repetition',
                    'peer_support',
                    'positive_reinforcement'
                ]),
                'assessment_modifications' => json_encode([
                    'oral_assessment_option',
                    'picture_cues',
                    'extended_time'
                ]),
                'special_notes' => 'May have speech articulation challenges. Focus on comprehension first.'
            ],
            [
                'disability_type' => 'Down Syndrome',
                'subject_category' => 'life_skills',
                'recommended_duration_minutes' => 40,
                'break_frequency_minutes' => 0,
                'teaching_strategies' => json_encode([
                    'hands_on_practice',
                    'task_analysis',
                    'modeling',
                    'guided_practice',
                    'independence_building'
                ]),
                'assessment_modifications' => json_encode([
                    'performance_based',
                    'checklist_format',
                    'multiple_attempts'
                ]),
                'special_notes' => 'Focus on practical, daily living skills with lots of practice opportunities.'
            ],

            // Cerebral Palsy  
            [
                'disability_type' => 'Cerebral Palsy',
                'subject_category' => 'science',
                'recommended_duration_minutes' => 45,
                'break_frequency_minutes' => 30,
                'teaching_strategies' => json_encode([
                    'adapted_materials',
                    'assistive_technology',
                    'collaborative_learning',
                    'demonstration_based'
                ]),
                'assessment_modifications' => json_encode([
                    'alternative_response_methods',
                    'extended_time',
                    'accessible_materials'
                ]),
                'special_notes' => 'May need physical supports and adapted tools for hands-on activities.'
            ],

            // Hearing Impairment
            [
                'disability_type' => 'Hearing Impairment',
                'subject_category' => 'bahasa_malaysia',
                'recommended_duration_minutes' => 45,
                'break_frequency_minutes' => 0,
                'teaching_strategies' => json_encode([
                    'visual_learning',
                    'sign_language_support',
                    'written_instructions',
                    'lip_reading_support',
                    'technology_aids'
                ]),
                'assessment_modifications' => json_encode([
                    'written_format',
                    'visual_prompts',
                    'sign_language_interpreter'
                ]),
                'special_notes' => 'Ensure visual access to all content. May need sign language support.'
            ],

            // Visual Impairment
            [
                'disability_type' => 'Visual Impairment', 
                'subject_category' => 'mathematics',
                'recommended_duration_minutes' => 50,
                'break_frequency_minutes' => 0,
                'teaching_strategies' => json_encode([
                    'tactile_materials',
                    'braille_support',
                    'audio_descriptions',
                    'large_print',
                    'concrete_manipulatives'
                ]),
                'assessment_modifications' => json_encode([
                    'braille_format',
                    'audio_format',
                    'extended_time',
                    'tactile_graphics'
                ]),
                'special_notes' => 'Provide tactile and audio alternatives to visual content.'
            ],

            // Intellectual Disability
            [
                'disability_type' => 'Intellectual Disability',
                'subject_category' => 'life_skills',
                'recommended_duration_minutes' => 60,
                'break_frequency_minutes' => 30,
                'teaching_strategies' => json_encode([
                    'simple_language',
                    'concrete_concepts',
                    'repetitive_practice',
                    'step_by_step_instruction',
                    'positive_reinforcement'
                ]),
                'assessment_modifications' => json_encode([
                    'simplified_language',
                    'multiple_choice_format',
                    'performance_based',
                    'extended_time'
                ]),
                'special_notes' => 'Break down complex tasks into smaller steps. Use concrete examples.'
            ],

            // Learning Disability
            [
                'disability_type' => 'Learning Disability',
                'subject_category' => 'english_language',
                'recommended_duration_minutes' => 60,
                'break_frequency_minutes' => 20,
                'teaching_strategies' => json_encode([
                    'multisensory_approach',
                    'structured_literacy',
                    'assistive_technology',
                    'graphic_organizers',
                    'explicit_instruction'
                ]),
                'assessment_modifications' => json_encode([
                    'extended_time',
                    'alternative_formats',
                    'assistive_technology',
                    'oral_assessment_option'
                ]),
                'special_notes' => 'May need reading and writing supports. Focus on individual learning style.'
            ],

            // Speech and Language Disorder
            [
                'disability_type' => 'Speech and Language Disorder',
                'subject_category' => 'bahasa_malaysia',
                'recommended_duration_minutes' => 40,
                'break_frequency_minutes' => 15,
                'teaching_strategies' => json_encode([
                    'visual_supports',
                    'picture_communication',
                    'speech_therapy_integration',
                    'peer_interaction',
                    'augmentative_communication'
                ]),
                'assessment_modifications' => json_encode([
                    'alternative_communication_methods',
                    'visual_response_options',
                    'extended_response_time'
                ]),
                'special_notes' => 'Focus on communication goals. May need AAC supports.'
            ]
        ];

        foreach ($accommodations as $accommodation) {
            $accommodation['created_at'] = now();
            $accommodation['updated_at'] = now();
        }

        DB::table('disability_accommodations')->insert($accommodations);
    }
}