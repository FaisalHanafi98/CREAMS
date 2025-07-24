<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Trainee Management Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the enhanced trainee
    | management system including conditions, validation rules, and defaults.
    |
    */

    'conditions' => [
        'Autism Spectrum Disorder',
        'Down Syndrome',
        'Cerebral Palsy',
        'Hearing Impairment',
        'Visual Impairment',
        'Intellectual Disability',
        'Physical Disability',
        'Speech and Language Disorder',
        'Learning Disability',
        'Multiple Disabilities',
        'Others'
    ],

    'statuses' => [
        'active' => 'Active',
        'inactive' => 'Inactive',
        'graduated' => 'Graduated',
        'transferred' => 'Transferred'
    ],

    'genders' => [
        'male' => 'Male',
        'female' => 'Female',
        'other' => 'Other'
    ],

    'relationships' => [
        'Parent',
        'Guardian',
        'Grandparent',
        'Sibling',
        'Uncle/Aunt',
        'Cousin',
        'Family Friend',
        'Legal Guardian',
        'Foster Parent',
        'Other'
    ],

    'guardian_relationships' => [
        'Parent',
        'Guardian',
        'Grandparent',
        'Sibling',
        'Uncle/Aunt',
        'Cousin',
        'Family Friend',
        'Legal Guardian',
        'Foster Parent',
        'Other'
    ],

    'document_types' => [
        'birth_certificate' => 'Birth Certificate',
        'ic_copy' => 'IC Copy',
        'medical_report' => 'Medical Report',
        'assessment_report' => 'Assessment Report',
        'photo' => 'Photograph',
        'guardian_ic' => 'Guardian IC',
        'consent_form' => 'Consent Form',
        'other' => 'Other Document'
    ],

    'skill_areas' => [
        'communication' => 'Communication',
        'social_skills' => 'Social Skills',
        'cognitive' => 'Cognitive Development',
        'motor_skills' => 'Motor Skills',
        'self_care' => 'Self Care',
        'academic' => 'Academic Skills',
        'behavioral' => 'Behavioral Management',
        'vocational' => 'Vocational Skills'
    ],

    'assessment_scales' => [
        'communication' => [
            'min' => 0,
            'max' => 100,
            'excellent' => 80,
            'good' => 60,
            'fair' => 40,
            'poor' => 20
        ],
        'default' => [
            'min' => 0,
            'max' => 100,
            'excellent' => 80,
            'good' => 60,
            'fair' => 40,
            'poor' => 20
        ]
    ],

    'validation' => [
        'max_file_size' => 5120, // 5MB in KB
        'allowed_image_types' => ['jpeg', 'jpg', 'png', 'gif'],
        'allowed_document_types' => ['pdf', 'doc', 'docx', 'jpg', 'jpeg', 'png'],
        'max_documents_per_trainee' => 20,
        'duplicate_check_enabled' => true,
        'duplicate_tolerance_days' => 7 // Days to consider for duplicate birth dates
    ],

    'ui' => [
        'pagination_per_page' => 20,
        'search_min_length' => 2,
        'auto_save_interval' => 30, // seconds
        'session_timeout_warning' => 5, // minutes before expiry
        'birthday_notification_days' => 30
    ],

    'exports' => [
        'formats' => ['csv', 'excel', 'pdf'],
        'max_records' => 1000,
        'filename_prefix' => 'trainees_export_',
        'include_sensitive_data' => false // Set to true for admin exports
    ],

    'audit' => [
        'retention_months' => 24,
        'log_all_changes' => true,
        'log_views' => false, // Set to true to log when records are viewed
        'log_searches' => true
    ],

    'cache' => [
        'statistics_ttl' => 300, // 5 minutes
        'search_results_ttl' => 60, // 1 minute
        'trainee_details_ttl' => 600 // 10 minutes
    ],

    'notifications' => [
        'birthday_reminders' => true,
        'document_expiry' => true,
        'assessment_due' => true,
        'duplicate_detected' => true
    ],

    'security' => [
        'require_two_approvals_for_deletion' => true,
        'mask_sensitive_data_in_logs' => true,
        'encrypt_medical_data' => true,
        'audit_data_access' => true
    ],

    'integrations' => [
        'enable_backup_sync' => false,
        'external_systems' => [],
        'api_rate_limits' => [
            'search' => 100, // per minute
            'export' => 10,  // per hour
            'bulk_operations' => 20 // per hour
        ]
    ]
];