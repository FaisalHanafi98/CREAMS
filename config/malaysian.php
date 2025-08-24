<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Malaysian Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains Malaysian-specific configuration settings for the
    | CREAMS rehabilitation system.
    |
    */

    'timezone' => 'Asia/Kuala_Lumpur',
    'currency' => 'MYR',
    'currency_symbol' => 'RM',
    'date_format' => 'd/m/Y',
    'datetime_format' => 'd/m/Y h:i A',
    'time_format' => 'h:i A',
    
    // Malaysian working days (Monday to Friday)
    'working_days' => [
        'monday' => true,
        'tuesday' => true,
        'wednesday' => true,
        'thursday' => true,
        'friday' => true,
        'saturday' => false,
        'sunday' => false,
    ],
    
    // Standard Malaysian working hours
    'office_hours' => [
        'start_time' => '08:00',
        'end_time' => '17:00',
        'lunch_start' => '12:00',
        'lunch_end' => '13:00',
    ],
    
    // Malaysian states (for address validation if needed)
    'states' => [
        'johor' => 'Johor',
        'kedah' => 'Kedah',
        'kelantan' => 'Kelantan',
        'malacca' => 'Malacca',
        'negeri_sembilan' => 'Negeri Sembilan',
        'pahang' => 'Pahang',
        'penang' => 'Penang',
        'perak' => 'Perak',
        'perlis' => 'Perlis',
        'sabah' => 'Sabah',
        'sarawak' => 'Sarawak',
        'selangor' => 'Selangor',
        'terengganu' => 'Terengganu',
        'kuala_lumpur' => 'Kuala Lumpur',
        'labuan' => 'Labuan',
        'putrajaya' => 'Putrajaya',
    ],
    
    // Common Malaysian phone number formats
    'phone_formats' => [
        'mobile' => '/^(\+?6?01)[0-9]{8,9}$/',
        'landline' => '/^(\+?6?0[3-9])[0-9]{7,8}$/',
    ],
    
    // Malaysian public holidays (major ones)
    'public_holidays' => [
        'new_year' => '01-01',
        'chinese_new_year' => 'variable', // Changes yearly
        'labour_day' => '01-05',
        'vesak_day' => 'variable', // Changes yearly
        'hari_raya_aidilfitri' => 'variable', // Changes yearly
        'kings_birthday' => 'variable', // First Saturday of June
        'national_day' => '31-08',
        'malaysia_day' => '16-09',
        'deepavali' => 'variable', // Changes yearly
        'hari_raya_haji' => 'variable', // Changes yearly
        'christmas' => '25-12',
    ],
];