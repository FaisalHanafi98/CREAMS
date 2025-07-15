<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trainee Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains configuration options for trainee management
    | including dropdown options for forms and other trainee-related settings.
    |
    */

    'conditions' => [
        'ADHD',
        'Autism',
        'Autism Spectrum Disorder',
        'Cerebral Palsy',
        'Down Syndrome', 
        'Hearing Impairment',
        'Intellectual Disability',
        'Learning Disability',
        'Multiple Disabilities',
        'Physical Disability',
        'Speech and Language Disorder',
        'Visual Impairment',
        'Others'
    ],

    'guardian_relationships' => [
        'Parent',
        'Sibling',
        'Aunt',
        'Uncle',
        'Aunt/Uncle',
        'Grandparent',
        'Legal Guardian',
        'Other'
    ],

    'gender_options' => [
        'male' => 'Male',
        'female' => 'Female'
    ],

    /*
    |--------------------------------------------------------------------------
    | Avatar Settings
    |--------------------------------------------------------------------------
    */
    'avatar' => [
        'storage_path' => 'trainee_avatars',
        'max_size' => 2048, // in KB
        'allowed_types' => ['jpeg', 'jpg', 'png', 'gif'],
        'default_male' => 'images/default-avatar.png',
        'default_female' => 'images/default-female-avatar.png'
    ],

    /*
    |--------------------------------------------------------------------------
    | Form Validation Rules
    |--------------------------------------------------------------------------
    */
    'validation' => [
        'first_name' => 'required|string|max:255',
        'last_name' => 'required|string|max:255',
        'email' => 'required|email|unique:trainees,trainee_email',
        'phone' => 'nullable|string|max:20',
        'date_of_birth' => 'required|date|before:today',
        'condition' => 'required|string',
        'centre' => 'required|string',
        'avatar' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:2048'
    ]
];