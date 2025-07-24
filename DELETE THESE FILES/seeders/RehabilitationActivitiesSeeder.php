<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\RehabilitationActivity;
use Illuminate\Support\Facades\DB;

class RehabilitationActivitiesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Clear existing data if needed
        DB::table('rehabilitation_activities')->truncate();
        
        // Create activities for Autism Spectrum Disorder
        $this->createAutismActivities();
        
        // Create activities for Hearing Impairment
        $this->createHearingActivities();
        
        // Create activities for Visual Impairment
        $this->createVisualActivities();
        
        // Create activities for Physical Disabilities
        $this->createPhysicalActivities();
        
        // Create activities for Learning Disabilities
        $this->createLearningActivities();
        
        // Create activities for Speech and Language Disorders
        $this->createSpeechActivities();
        
        // Create universal activities for all disability types
        $this->createUniversalActivities();
    }
    
    /**
     * Create activities for Autism Spectrum Disorder.
     */
    private function createAutismActivities()
    {
        $activities = [
            [
                'name' => 'Picture Exchange Communication System (PECS)',
                'description' => 'A visual communication system that helps individuals with autism communicate their needs, wants, and thoughts using pictures and symbols.',
                'category' => 'autism',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 30,
                'materials_required' => 'PECS cards, binder, Velcro, pictures representing common items and activities',
                'staff_requirements' => 'Speech therapist or trained instructor',
                'goals' => 'Improve communication skills, reduce frustration, enhance independence in expressing needs and wants',
                'instructions' => "1. Begin by identifying highly motivating items for the trainee\n2. Use physical prompting to help the trainee pick up the picture and hand it to the communication partner\n3. Gradually reduce physical prompts as the trainee learns to exchange pictures independently\n4. Progress through the phases of PECS: picture exchange, increasing distance, picture discrimination, sentence structure, responsive commenting",
                'modifications' => 'For younger children, use larger pictures with clear, simple images. For older trainees, use more abstract symbols and introduce digital PECS apps',
                'assessment_criteria' => 'Number of successful exchanges, reduction in communication frustration, increased vocabulary of symbols used'
            ],
            [
                'name' => 'Sensory Integration Activities',
                'description' => 'A series of sensory-based activities designed to help individuals with autism process and respond appropriately to sensory stimuli in their environment.',
                'category' => 'autism',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 45,
                'materials_required' => 'Sensory bins, textured materials, swings, balance equipment, weighted items, fidget toys',
                'staff_requirements' => 'Occupational therapist or trained specialist',
                'goals' => 'Improve sensory processing, reduce sensory sensitivities, develop appropriate responses to sensory stimuli',
                'instructions' => "1. Begin with a sensory profile assessment to identify specific sensory needs\n2. Create a personalized sensory diet that includes appropriate activities for each sensory system\n3. Introduce activities gradually, respecting the trainee's tolerance levels\n4. Include a mix of calming and alerting activities based on the trainee's needs\n5. Implement deep pressure techniques, proprioceptive inputs, and vestibular activities as appropriate",
                'modifications' => 'For sensory-seeking trainees, provide more intense sensory experiences. For sensory-avoiding trainees, introduce sensory inputs gradually with more control given to the trainee',
                'assessment_criteria' => 'Improved tolerance to sensory stimuli, reduced sensory-related behaviors, increased participation in daily activities'
            ],
            [
                'name' => 'Social Stories',
                'description' => 'A technique using personalized short stories to help individuals with autism understand social situations, expectations, and appropriate behaviors.',
                'category' => 'autism',
                'difficulty_level' => 1,
                'age_group' => '4-18',
                'duration_minutes' => 20,
                'materials_required' => 'Social story book or cards, pictures, simple text, optional video modeling',
                'staff_requirements' => 'Special education teacher or therapist',
                'goals' => 'Improve understanding of social situations, reduce anxiety, teach appropriate social behaviors',
                'instructions' => "1. Identify a specific social situation that is challenging for the trainee\n2. Create a simple, concise story using descriptive, perspective, directive, and control sentences\n3. Include visuals that are meaningful to the trainee\n4. Read the story with the trainee regularly, especially before encountering the situation\n5. Review and practice the concepts from the story through role-play or discussion",
                'modifications' => 'For younger children, use more pictures and simpler language. For older trainees, include more perspective sentences and complex social concepts',
                'assessment_criteria' => 'Demonstration of target behaviors in actual social situations, reduced anxiety in social settings, ability to recall key points from the story'
            ],
            [
                'name' => 'Visual Schedule System',
                'description' => 'A visual support system that helps individuals with autism understand and follow daily routines and transitions.',
                'category' => 'autism',
                'difficulty_level' => 1,
                'age_group' => '3-18',
                'duration_minutes' => 15,
                'materials_required' => 'Picture cards, schedule board, Velcro, timer or clock',
                'staff_requirements' => 'Teacher or caregiver',
                'goals' => 'Improve independence in following routines, reduce transition anxiety, enhance time management',
                'instructions' => "1. Create a visual schedule using pictures or symbols that represent activities\n2. Arrange the schedule in chronological order\n3. Review the schedule with the trainee at the start of the day or session\n4. Teach the trainee to check the schedule and move to the next activity\n5. Use a \"finished\" pocket or check-off system for completed activities",
                'modifications' => 'For younger children, use a first-then board with only two activities. For older trainees, introduce weekly or monthly calendars',
                'assessment_criteria' => 'Ability to follow the schedule independently, reduced resistance to transitions, decreased dependence on verbal prompts'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create activities for Hearing Impairment.
     */
    private function createHearingActivities()
    {
        $activities = [
            [
                'name' => 'Sign Language Basics',
                'description' => 'Introduction to basic sign language vocabulary and grammar to enhance communication for individuals with hearing impairments.',
                'category' => 'hearing',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 30,
                'materials_required' => 'Sign language flashcards, visual aids, mirror for self-observation, sign language videos',
                'staff_requirements' => 'Sign language instructor or speech therapist',
                'goals' => 'Develop basic sign language vocabulary, improve communication skills, enhance expressive abilities',
                'instructions' => "1. Begin with common signs related to the trainee's daily needs and interests\n2. Demonstrate each sign clearly, explaining the hand shape, movement, and location\n3. Practice signs in front of a mirror to help the trainee see their own movements\n4. Incorporate signs into daily routines and conversations\n5. Gradually build vocabulary by introducing new signs regularly",
                'modifications' => 'For younger children, focus on concrete signs for basic needs. For older trainees, introduce more abstract concepts and grammar',
                'assessment_criteria' => 'Number of signs correctly produced, spontaneous use of signs in communication, ability to recognize signs made by others'
            ],
            [
                'name' => 'Lip Reading Practice',
                'description' => 'Structured practice in visual speech perception to enhance communication abilities for individuals with hearing impairments.',
                'category' => 'hearing',
                'difficulty_level' => 3,
                'age_group' => '6-18',
                'duration_minutes' => 25,
                'materials_required' => 'Mirror, picture cards, video recording device, lip reading practice videos',
                'staff_requirements' => 'Speech therapist or specialized instructor',
                'goals' => 'Improve lip reading skills, enhance visual attention to facial movements, develop compensatory communication strategies',
                'instructions' => "1. Begin with distinguishing visually different sounds (e.g., 'p' vs 's')\n2. Practice with single words before moving to phrases and sentences\n3. Use a clear, natural speaking rate without exaggeration\n4. Incorporate contextual clues and practice in various settings\n5. Gradually increase difficulty by adding background noise or distractions",
                'modifications' => 'For beginners, use words with clear visual differences. For advanced trainees, practice with various speakers and in group settings',
                'assessment_criteria' => 'Accuracy in identifying words and phrases, improvement in conversational understanding, reduced need for repetition'
            ],
            [
                'name' => 'Auditory Training',
                'description' => 'Structured listening activities to maximize use of residual hearing and/or hearing aids/cochlear implants.',
                'category' => 'hearing',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 20,
                'materials_required' => 'Various sound-making objects, recorded sounds, musical instruments, sound discrimination games',
                'staff_requirements' => 'Audiologist or speech therapist',
                'goals' => 'Improve sound awareness, develop sound discrimination skills, enhance auditory processing',
                'instructions' => "1. Begin with awareness of presence vs. absence of sound\n2. Progress to discriminating between different environmental sounds\n3. Practice identifying differences in duration, intensity, and pitch\n4. Work on speech sound discrimination (vowels before consonants)\n5. Gradually increase complexity with words, phrases, and sentences",
                'modifications' => 'For those with cochlear implants, focus on new sounds they can access. For those with hearing aids, emphasize sounds within their audible range',
                'assessment_criteria' => 'Improved detection and discrimination of sounds, better speech understanding, effective use of assistive devices'
            ],
            [
                'name' => 'Visual Attention Training',
                'description' => 'Activities designed to enhance visual attention and perception skills which are crucial for individuals with hearing impairments.',
                'category' => 'hearing',
                'difficulty_level' => 1,
                'age_group' => '3-18',
                'duration_minutes' => 15,
                'materials_required' => 'Visual tracking games, picture cards, matching activities, visual attention apps',
                'staff_requirements' => 'Occupational therapist or special education teacher',
                'goals' => 'Improve visual scanning, enhance sustained visual attention, develop visual discrimination skills',
                'instructions' => "1. Start with simple visual tracking activities (following moving objects)\n2. Progress to visual search tasks (finding specific items in a busy picture)\n3. Practice visual memory games (remembering object locations or sequences)\n4. Incorporate scanning for visual cues in communication\n5. Gradually increase complexity and duration of visual tasks",
                'modifications' => 'For younger children, use brightly colored, high-contrast materials. For older trainees, incorporate real-world visual scanning tasks',
                'assessment_criteria' => 'Improved visual attention span, better visual scanning efficiency, enhanced ability to notice visual communication cues'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create activities for Visual Impairment.
     */
    private function createVisualActivities()
    {
        $activities = [
            [
                'name' => 'Tactile Exploration and Discrimination',
                'description' => 'Activities focused on developing tactile sensitivity and discrimination skills for individuals with visual impairments.',
                'category' => 'visual',
                'difficulty_level' => 1,
                'age_group' => '3-18',
                'duration_minutes' => 25,
                'materials_required' => 'Textured materials, tactile discrimination boards, objects with various surfaces, tactile matching games',
                'staff_requirements' => 'Occupational therapist or vision specialist',
                'goals' => 'Develop tactile sensitivity, improve object identification through touch, enhance tactile discrimination',
                'instructions' => "1. Begin with exploration of clearly different textures (rough vs. smooth)\n2. Progress to more subtle texture differences\n3. Practice identifying common objects by touch alone\n4. Incorporate tactile symbols and pre-braille activities when appropriate\n5. Encourage verbalization of tactile experiences to build descriptive language",
                'modifications' => 'For younger children, use larger objects with pronounced textures. For older trainees, include more complex tactile discrimination tasks',
                'assessment_criteria' => 'Accuracy in tactile identification, improved tactile discrimination abilities, development of systematic tactile exploration strategies'
            ],
            [
                'name' => 'Orientation and Mobility Training',
                'description' => 'Systematic instruction in safe and efficient movement techniques for individuals with visual impairments.',
                'category' => 'visual',
                'difficulty_level' => 3,
                'age_group' => '5-18',
                'duration_minutes' => 45,
                'materials_required' => 'White cane (if appropriate), tactile maps, orientation landmarks, mobility aids',
                'staff_requirements' => 'Certified orientation and mobility specialist',
                'goals' => 'Develop safe navigation skills, improve spatial awareness, enhance independent travel abilities',
                'instructions' => "1. Begin with body awareness and positional concepts\n2. Progress to familiar space orientation with clear landmarks\n3. Teach protective techniques and trailing methods\n4. Introduce cane techniques if appropriate\n5. Practice route planning and navigation in increasingly complex environments",
                'modifications' => 'For trainees with some vision, incorporate use of visual cues alongside non-visual strategies. For young children, focus on basic spatial concepts in familiar settings',
                'assessment_criteria' => 'Ability to navigate familiar environments, use of appropriate safety techniques, development of spatial awareness and mapping skills'
            ],
            [
                'name' => 'Auditory Training for Spatial Awareness',
                'description' => 'Activities designed to enhance the use of auditory information for orientation, localization, and environmental awareness.',
                'category' => 'visual',
                'difficulty_level' => 2,
                'age_group' => '4-18',
                'duration_minutes' => 30,
                'materials_required' => 'Various sound sources, echo location practice spaces, audio recording equipment',
                'staff_requirements' => 'Vision specialist or orientation and mobility instructor',
                'goals' => 'Improve sound localization, develop echo location skills, enhance auditory memory and discrimination',
                'instructions' => "1. Practice identifying direction of sound sources\n2. Work on distance estimation using sound cues\n3. Develop awareness of environmental sounds and their meaning\n4. Introduce basic echolocation techniques (detecting objects by sound reflections)\n5. Practice using auditory cues for orientation in various environments",
                'modifications' => 'For beginners, use distinctive sounds in quiet environments. For advanced trainees, practice in more challenging acoustic environments',
                'assessment_criteria' => 'Accuracy in sound localization, improved navigation using auditory cues, recognition of environmental sounds'
            ],
            [
                'name' => 'Braille Introduction and Practice',
                'description' => 'Systematic instruction in the tactile reading and writing system of braille for individuals with visual impairments.',
                'category' => 'visual',
                'difficulty_level' => 4,
                'age_group' => '6-18',
                'duration_minutes' => 40,
                'materials_required' => 'Braille paper, slate and stylus, braille writer, braille teaching materials, tactile discrimination tools',
                'staff_requirements' => 'Certified teacher of the visually impaired',
                'goals' => 'Develop braille literacy skills, improve tactile discrimination, enhance reading and writing abilities',
                'instructions' => "1. Begin with tactile readiness activities and finger sensitivity exercises\n2. Introduce the braille cell concept and basic letter patterns\n3. Teach letter recognition before word formation\n4. Practice tracking along braille lines with proper finger technique\n5. Gradually build vocabulary and reading fluency",
                'modifications' => 'For younger children, use larger braille cells or pre-braille activities. For older beginners, relate braille patterns to previously known concepts',
                'assessment_criteria' => 'Letter and word recognition accuracy, reading speed, proper finger technique, writing accuracy'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create activities for Physical Disabilities.
     */
    private function createPhysicalActivities()
    {
        $activities = [
            [
                'name' => 'Adaptive Fine Motor Skills Development',
                'description' => 'Activities designed to improve hand strength, dexterity, and coordination for individuals with physical disabilities.',
                'category' => 'physical',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 30,
                'materials_required' => 'Adaptive grips, therapy putty, finger strengthening tools, fine motor manipulation toys, adapted scissors',
                'staff_requirements' => 'Occupational therapist',
                'goals' => 'Improve hand strength and dexterity, enhance precision in manipulation, develop functional grasping patterns',
                'instructions' => "1. Begin with assessment of current fine motor abilities\n2. Start with gross grasping activities before progressing to pincer grasp\n3. Incorporate hand strengthening with therapy putty or similar materials\n4. Practice functional tasks like buttoning, zipping, and using utensils with adaptations as needed\n5. Gradually reduce adaptations as capabilities improve",
                'modifications' => 'For those with limited hand function, use built-up handles or universal cuffs. For those with unilateral involvement, incorporate activities that promote bilateral coordination',
                'assessment_criteria' => 'Improved precision in manipulation tasks, increased strength in standardized measures, greater independence in activities of daily living'
            ],
            [
                'name' => 'Adaptive Mobility and Gross Motor Skills',
                'description' => 'Activities focused on improving strength, balance, coordination, and functional mobility for individuals with physical disabilities.',
                'category' => 'physical',
                'difficulty_level' => 3,
                'age_group' => '3-18',
                'duration_minutes' => 45,
                'materials_required' => 'Therapy balls, balance equipment, mobility aids, parallel bars, stairs with railings, assistive devices as needed',
                'staff_requirements' => 'Physical therapist',
                'goals' => 'Improve balance and coordination, enhance functional mobility, develop strength and endurance',
                'instructions' => "1. Begin with stability activities before mobility\n2. Practice weight shifting, transitional movements, and balance in various positions\n3. Incorporate strength training for major muscle groups\n4. Work on functional movements like sit-to-stand, floor-to-stand, and navigating different surfaces\n5. Train in the proper use of mobility aids if applicable",
                'modifications' => 'For wheelchair users, focus on seated balance and upper body strength. For ambulatory trainees, incorporate gait training and stair navigation',
                'assessment_criteria' => 'Improved balance scores, increased mobility independence, enhanced quality of movement patterns, improved strength measurements'
            ],
            [
                'name' => 'Adaptive Equipment Training',
                'description' => 'Instruction and practice in the effective use of adaptive equipment to enhance independence in daily activities.',
                'category' => 'physical',
                'difficulty_level' => 2,
                'age_group' => '4-18',
                'duration_minutes' => 30,
                'materials_required' => 'Relevant adaptive equipment (communication devices, feeding aids, mobility devices, etc.), practice environments setup',
                'staff_requirements' => 'Occupational therapist or physical therapist',
                'goals' => 'Develop proficiency with adaptive equipment, improve independence in daily tasks, enhance functional capabilities',
                'instructions' => "1. Assess specific needs and match with appropriate adaptive equipment\n2. Introduce equipment with clear demonstration and explanation\n3. Practice basic operations in simplified environment\n4. Gradually increase complexity of tasks and environments\n5. Train caregivers in supporting equipment use",
                'modifications' => 'Equipment selection and training approach must be highly individualized based on specific physical limitations and functional goals',
                'assessment_criteria' => 'Correct usage of equipment, increased independence in target activities, reduced need for physical assistance'
            ],
            [
                'name' => 'Sensory Motor Integration',
                'description' => 'Activities that combine sensory input with motor responses to improve body awareness and movement planning.',
                'category' => 'physical',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 35,
                'materials_required' => 'Therapy balls, textured surfaces, balance equipment, obstacle courses, visual cues',
                'staff_requirements' => 'Occupational therapist or physical therapist',
                'goals' => 'Improve body awareness, enhance motor planning, develop coordinated responses to sensory input',
                'instructions' => "1. Begin with activities that provide clear proprioceptive input\n2. Incorporate multisensory feedback during movement activities\n3. Practice motor planning through obstacle courses and sequenced movements\n4. Use visual, verbal, and tactile cues to support motor learning\n5. Gradually reduce external cues as internal body awareness improves",
                'modifications' => 'For those with high muscle tone, incorporate calming proprioceptive input. For those with low muscle tone, use stimulating sensory input to increase alertness',
                'assessment_criteria' => 'Improved coordination, more efficient movement patterns, enhanced ability to motor plan novel tasks, better postural control'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create activities for Learning Disabilities.
     */
    private function createLearningActivities()
    {
        $activities = [
            [
                'name' => 'Multisensory Reading Instruction',
                'description' => 'Structured literacy approach that engages visual, auditory, tactile, and kinesthetic pathways simultaneously to improve reading skills.',
                'category' => 'learning',
                'difficulty_level' => 2,
                'age_group' => '5-18',
                'duration_minutes' => 30,
                'materials_required' => 'Letter tiles, sand trays, textured letters, decodable texts, phonics cards, mirror for mouth positions',
                'staff_requirements' => 'Special education teacher or reading specialist',
                'goals' => 'Develop phonological awareness, improve decoding skills, enhance reading fluency and comprehension',
                'instructions' => "1. Begin with phonological awareness activities (rhyming, segmenting, blending)\n2. Teach letter-sound correspondences using multiple sensory pathways\n3. Practice tracing letters while saying sounds\n4. Apply skills to decodable words and texts\n5. Use structured, cumulative approach with frequent review",
                'modifications' => 'For younger children, focus more on phonological awareness. For older students, incorporate more advanced phonics patterns and application to content-area vocabulary',
                'assessment_criteria' => 'Improved phonological awareness, increased decoding accuracy, enhanced reading fluency, better comprehension scores'
            ],
            [
                'name' => 'Working Memory Enhancement',
                'description' => 'Activities designed to strengthen working memory capacity, which is often impaired in individuals with learning disabilities.',
                'category' => 'learning',
                'difficulty_level' => 2,
                'age_group' => '6-18',
                'duration_minutes' => 20,
                'materials_required' => 'Memory games, sequence cards, auditory memory activities, visual pattern materials, working memory apps',
                'staff_requirements' => 'Special education teacher or cognitive trainer',
                'goals' => 'Improve working memory capacity, enhance information retention, develop memory strategies',
                'instructions' => "1. Start with simple span tasks (remembering 2-3 items)\n2. Gradually increase memory load as skills improve\n3. Teach explicit memory strategies (chunking, visualization, rehearsal)\n4. Practice both verbal and visual-spatial working memory tasks\n5. Apply memory strategies to academic content",
                'modifications' => 'For trainees with primarily verbal memory deficits, focus more on visual supports. For those with visual-spatial memory challenges, provide verbal scaffolding',
                'assessment_criteria' => 'Increased memory span, better retention of instructions, improved application of memory strategies, enhanced academic performance'
            ],
            [
                'name' => 'Executive Function Training',
                'description' => 'Structured activities to develop planning, organization, time management, and self-monitoring skills.',
                'category' => 'learning',
                'difficulty_level' => 3,
                'age_group' => '7-18',
                'duration_minutes' => 30,
                'materials_required' => 'Planners, checklists, timers, graphic organizers, task analysis sheets, self-monitoring tools',
                'staff_requirements' => 'Special education teacher or executive function coach',
                'goals' => 'Improve planning and organization, enhance time management, develop self-monitoring and metacognitive skills',
                'instructions' => "1. Teach explicit planning strategies (goal setting, breaking tasks into steps)\n2. Practice organization of materials and information using visual systems\n3. Implement time management techniques with visual timers\n4. Develop self-checking routines and error monitoring\n5. Gradually transfer responsibility for strategy implementation to the trainee",
                'modifications' => 'For younger children, use more external supports and simpler tasks. For older students, focus on self-generated strategies and application to complex projects',
                'assessment_criteria' => 'Improved task completion, better organization of materials, enhanced time management, development of self-monitoring skills'
            ],
            [
                'name' => 'Mathematical Reasoning Through Concrete-Representational-Abstract Sequence',
                'description' => 'Systematic instruction in mathematical concepts using hands-on materials, visual representations, and abstract symbols.',
                'category' => 'learning',
                'difficulty_level' => 3,
                'age_group' => '6-18',
                'duration_minutes' => 30,
                'materials_required' => 'Manipulatives (counters, base-10 blocks, fraction pieces), visual models, graph paper, number lines, calculators',
                'staff_requirements' => 'Special education teacher or math specialist',
                'goals' => 'Develop conceptual understanding of mathematics, improve problem-solving skills, enhance mathematical fluency',
                'instructions' => "1. Begin with concrete manipulatives to represent mathematical concepts\n2. Progress to visual representations (drawings, diagrams)\n3. Connect concrete and representational understanding to abstract symbols\n4. Teach explicit problem-solving strategies\n5. Practice retrieval of math facts with appropriate supports",
                'modifications' => 'For trainees with math anxiety, use more collaborative, low-pressure approaches. For those with specific calculation difficulties, provide appropriate accommodations',
                'assessment_criteria' => 'Improved conceptual understanding, increased accuracy in calculations, enhanced problem-solving abilities, better application of mathematical concepts'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create activities for Speech and Language Disorders.
     */
    private function createSpeechActivities()
    {
        $activities = [
            [
                'name' => 'Articulation Therapy',
                'description' => 'Structured activities to improve the production of speech sounds for clear, intelligible speech.',
                'category' => 'speech',
                'difficulty_level' => 2,
                'age_group' => '3-18',
                'duration_minutes' => 25,
                'materials_required' => 'Mirror, articulation cards, sound-specific materials, recording device, articulation apps',
                'staff_requirements' => 'Speech-language pathologist',
                'goals' => 'Improve production of target sounds, enhance speech intelligibility, develop self-monitoring of articulation',
                'instructions' => "1. Begin with assessment to identify specific sound errors\n2. Teach correct tongue, lip, and jaw positions for target sounds\n3. Practice sounds in isolation before progressing to syllables, words, phrases, sentences, and conversation\n4. Use visual and tactile cues to support correct production\n5. Incorporate self-monitoring strategies and recording for feedback",
                'modifications' => 'For multiple sound errors, establish priority hierarchy. For motor planning difficulties, incorporate more tactile and kinesthetic cueing',
                'assessment_criteria' => 'Increased accuracy of target sounds, improved intelligibility ratings, generalization to conversational speech'
            ],
            [
                'name' => 'Expressive Language Development',
                'description' => 'Activities designed to improve vocabulary, grammar, and sentence structure for effective communication.',
                'category' => 'speech',
                'difficulty_level' => 2,
                'age_group' => '2-18',
                'duration_minutes' => 30,
                'materials_required' => 'Picture cards, story sequence cards, toys for play-based therapy, sentence formulation materials, visual supports',
                'staff_requirements' => 'Speech-language pathologist',
                'goals' => 'Expand vocabulary, improve grammar and syntax, enhance narrative skills, develop conversational abilities',
                'instructions' => "1. Target vocabulary expansion through thematic units and multiple exposures\n2. Teach grammatical structures using modeling, recasting, and structured practice\n3. Scaffold sentence expansion and complexity\n4. Develop narrative skills through story grammar frameworks\n5. Practice conversational skills including turn-taking, topic maintenance, and repair strategies",
                'modifications' => 'For early language learners, use play-based approaches and simple phrase targets. For advanced students, focus on complex language and social pragmatics',
                'assessment_criteria' => 'Increased vocabulary usage, improved grammatical accuracy, enhanced syntactic complexity, better narrative structure'
            ],
            [
                'name' => 'Receptive Language Enhancement',
                'description' => 'Activities focused on improving comprehension of spoken language and following directions.',
                'category' => 'speech',
                'difficulty_level' => 2,
                'age_group' => '2-18',
                'duration_minutes' => 25,
                'materials_required' => 'Picture cards, manipulatives, direction following activities, story comprehension materials, visual supports',
                'staff_requirements' => 'Speech-language pathologist',
                'goals' => 'Improve comprehension of vocabulary and concepts, enhance ability to follow directions, develop listening comprehension',
                'instructions' => "1. Begin with simple identification of familiar objects and actions\n2. Progress to basic concepts (spatial, temporal, quantitative)\n3. Practice following directions of increasing length and complexity\n4. Develop active listening strategies and comprehension monitoring\n5. Work on inferencing and predicting from stories and conversations",
                'modifications' => 'For significant comprehension difficulties, provide visual supports. For auditory processing issues, reduce background noise and allow extra processing time',
                'assessment_criteria' => 'Improved response accuracy to questions, enhanced ability to follow multi-step directions, better comprehension of stories and conversations'
            ],
            [
                'name' => 'Augmentative and Alternative Communication Training',
                'description' => 'Instruction in the use of communication systems and devices for individuals with limited or no speech.',
                'category' => 'speech',
                'difficulty_level' => 3,
                'age_group' => '2-18',
                'duration_minutes' => 40,
                'materials_required' => 'Communication boards, picture exchange systems, speech-generating devices, AAC apps, training materials',
                'staff_requirements' => 'Speech-language pathologist with AAC expertise',
                'goals' => 'Develop effective use of AAC system, improve communication independence, enhance expressive communication abilities',
                'instructions' => "1. Conduct thorough assessment to determine appropriate AAC approach\n2. Begin with basic requesting using the selected system\n3. Expand to commenting, asking questions, and social communication\n4. Provide systematic instruction in navigating and operating the system\n5. Train communication partners in supporting and responding to AAC communication",
                'modifications' => 'Systems must be customized to individual motor, cognitive, and sensory abilities. Symbol complexity should match cognitive and visual discrimination abilities',
                'assessment_criteria' => 'Frequency of spontaneous communication, range of communicative functions expressed, efficiency of message generation, reduced communication breakdowns'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
    
    /**
     * Create universal activities for all disability types.
     */
    private function createUniversalActivities()
    {
        $activities = [
            [
                'name' => 'Adaptive Art Expression',
                'description' => 'Creative art activities adapted for accessibility and therapeutic benefit across different disability types.',
                'category' => 'all',
                'difficulty_level' => 1,
                'age_group' => '3-18',
                'duration_minutes' => 45,
                'materials_required' => 'Adaptive art tools, various art media, sensory art materials, supportive seating, accessible workspace',
                'staff_requirements' => 'Art therapist or trained facilitator',
                'goals' => 'Encourage self-expression, develop fine motor skills, promote sensory integration, enhance creativity',
                'instructions' => "1. Set up an accessible workspace with appropriate adaptations\n2. Introduce art materials with demonstration and sensory exploration\n3. Start with process-oriented rather than product-oriented activities\n4. Provide just enough support to enable success while encouraging independence\n5. Create opportunities for sharing and discussion about the artistic process",
                'modifications' => 'For physical disabilities: use adaptive tools, stabilize materials. For visual impairments: incorporate tactile materials. For autism: provide clear structure and sensory options. For learning disabilities: break instructions into small steps',
                'assessment_criteria' => 'Increased engagement in the creative process, development of fine motor skills, expression of thoughts and feelings, demonstrated pride in accomplishments'
            ],
            [
                'name' => 'Adaptive Music and Movement',
                'description' => 'Music-based activities that incorporate movement and rhythm for therapeutic benefit across disability types.',
                'category' => 'all',
                'difficulty_level' => 1,
                'age_group' => '2-18',
                'duration_minutes' => 30,
                'materials_required' => 'Adaptive musical instruments, percussion items, recorded music, visual movement cues, movement props',
                'staff_requirements' => 'Music therapist or trained facilitator',
                'goals' => 'Develop rhythm and timing, improve motor coordination, enhance social interaction, encourage self-expression',
                'instructions' => "1. Begin with basic rhythm activities that match natural movement abilities\n2. Incorporate songs with predictable patterns and movement components\n3. Use instruments that are accessible based on motor abilities\n4. Create opportunities for turn-taking and group participation\n5. Balance structured activities with creative expression",
                'modifications' => 'For hearing impairments: emphasize visual cues and vibration. For physical disabilities: adapt movements and provide support. For autism: provide visual schedules and predictable routines. For learning disabilities: break down sequences into manageable steps',
                'assessment_criteria' => 'Improved rhythmic synchronization, enhanced motor coordination, increased social engagement, demonstrated enjoyment and self-expression'
            ],
            [
                'name' => 'Social Skills Group',
                'description' => 'Structured group activities to develop and practice social interaction skills across disability types.',
                'category' => 'all',
                'difficulty_level' => 2,
                'age_group' => '4-18',
                'duration_minutes' => 45,
                'materials_required' => 'Social scripts, role-play materials, visual supports, social games, video modeling equipment',
                'staff_requirements' => 'Social skills specialist or trained facilitator',
                'goals' => 'Develop turn-taking, improve conversation skills, enhance perspective-taking, build friendship skills',
                'instructions' => "1. Establish group rules and positive behavioral expectations\n2. Introduce target social skill with clear explanation and modeling\n3. Provide structured practice through role-play and guided activities\n4. Create opportunities for naturalistic practice in supported environment\n5. Review and reinforce skill application",
                'modifications' => 'For autism: provide explicit instruction and visual supports. For hearing impairments: ensure communication access. For physical disabilities: adapt activities for mobility differences. For learning disabilities: simplify instructions and provide additional practice',
                'assessment_criteria' => 'Increased appropriate social initiations, improved conversation skills, enhanced perspective-taking, development of friendship skills'
            ],
            [
                'name' => 'Adaptive Yoga and Mindfulness',
                'description' => 'Modified yoga and mindfulness practices designed to be accessible and beneficial for various disability types.',
                'category' => 'all',
                'difficulty_level' => 1,
                'age_group' => '4-18',
                'duration_minutes' => 30,
                'materials_required' => 'Yoga mats, props for positioning, visual pose cards, breathing visual aids, calming sensory tools',
                'staff_requirements' => 'Adaptive yoga instructor or trained facilitator',
                'goals' => 'Improve body awareness, develop self-regulation skills, enhance flexibility and strength, reduce anxiety',
                'instructions' => "1. Begin with simple breathing techniques with concrete visual supports\n2. Introduce basic poses with appropriate modifications\n3. Create predictable sequences that can be learned over time\n4. Incorporate mindfulness activities appropriate to developmental level\n5. Balance movement with relaxation components",
                'modifications' => 'For physical disabilities: offer seated or supported variations. For autism: provide visual schedules and concrete language. For visual impairments: use tactile guidance and clear verbal cues. For learning disabilities: break sequences into simple steps with consistent repetition',
                'assessment_criteria' => 'Improved attention and focus, enhanced self-regulation, increased body awareness, demonstrated relaxation skills'
            ]
        ];
        
        foreach ($activities as $activity) {
            RehabilitationActivity::create($activity);
        }
    }
}