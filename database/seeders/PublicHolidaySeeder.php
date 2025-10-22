<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\PublicHoliday;

class PublicHolidaySeeder extends Seeder
{
    /**
     * Seed Malaysia Public Holidays for 2025
     * Source: Malaysian Government Official Calendar
     */
    public function run(): void
    {
        $holidays = [
            // Federal Public Holidays 2025
            [
                'date' => '2025-01-01',
                'name' => 'New Year\'s Day',
                'name_ms' => 'Tahun Baru',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-01-29',
                'name' => 'Chinese New Year',
                'name_ms' => 'Tahun Baru Cina',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-01-30',
                'name' => 'Chinese New Year (2nd day)',
                'name_ms' => 'Tahun Baru Cina (Hari Kedua)',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-03-31',
                'name' => 'Hari Raya Puasa',
                'name_ms' => 'Hari Raya Aidilfitri',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
                'description' => 'Estimated date (subject to moon sighting)',
            ],
            [
                'date' => '2025-04-01',
                'name' => 'Hari Raya Puasa (2nd day)',
                'name_ms' => 'Hari Raya Aidilfitri (Hari Kedua)',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
                'description' => 'Estimated date (subject to moon sighting)',
            ],
            [
                'date' => '2025-05-01',
                'name' => 'Labour Day',
                'name_ms' => 'Hari Pekerja',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-05-12',
                'name' => 'Wesak Day',
                'name_ms' => 'Hari Wesak',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-06-02',
                'name' => 'Agong\'s Birthday',
                'name_ms' => 'Hari Keputeraan Yang di-Pertuan Agong',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-06-07',
                'name' => 'Hari Raya Haji',
                'name_ms' => 'Hari Raya Aidiladha',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
                'description' => 'Estimated date (subject to moon sighting)',
            ],
            [
                'date' => '2025-06-28',
                'name' => 'Awal Muharram',
                'name_ms' => 'Awal Muharram (Maal Hijrah)',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
                'description' => 'Islamic New Year',
            ],
            [
                'date' => '2025-08-31',
                'name' => 'National Day',
                'name_ms' => 'Hari Kebangsaan',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-09-05',
                'name' => 'Prophet Muhammad\'s Birthday',
                'name_ms' => 'Maulidur Rasul',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-09-16',
                'name' => 'Malaysia Day',
                'name_ms' => 'Hari Malaysia',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-10-20',
                'name' => 'Deepavali',
                'name_ms' => 'Deepavali',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],
            [
                'date' => '2025-12-25',
                'name' => 'Christmas Day',
                'name_ms' => 'Hari Krismas',
                'type' => 'public',
                'state' => null,
                'year' => 2025,
            ],

            // Selangor State Holidays (Gombak Centre)
            [
                'date' => '2025-02-05',
                'name' => 'Thaipusam',
                'name_ms' => 'Thaipusam',
                'type' => 'state',
                'state' => 'Selangor',
                'year' => 2025,
            ],
            [
                'date' => '2025-12-11',
                'name' => 'Sultan of Selangor\'s Birthday',
                'name_ms' => 'Hari Keputeraan Sultan Selangor',
                'type' => 'state',
                'state' => 'Selangor',
                'year' => 2025,
            ],

            // Johor State Holidays (Pagoh Centre)
            [
                'date' => '2025-02-05',
                'name' => 'Thaipusam',
                'name_ms' => 'Thaipusam',
                'type' => 'state',
                'state' => 'Johor',
                'year' => 2025,
            ],
            [
                'date' => '2025-03-23',
                'name' => 'Sultan of Johor\'s Birthday',
                'name_ms' => 'Hari Keputeraan Sultan Johor',
                'type' => 'state',
                'state' => 'Johor',
                'year' => 2025,
            ],
            [
                'date' => '2025-09-13',
                'name' => 'Johor Hol (Awal Muharram)',
                'name_ms' => 'Hol Johor (Awal Muharram)',
                'type' => 'state',
                'state' => 'Johor',
                'year' => 2025,
            ],

            // Pahang State Holidays (Kuantan & Gambang Centres)
            [
                'date' => '2025-02-05',
                'name' => 'Thaipusam',
                'name_ms' => 'Thaipusam',
                'type' => 'state',
                'state' => 'Pahang',
                'year' => 2025,
            ],
            [
                'date' => '2025-05-22',
                'name' => 'Sultan of Pahang\'s Birthday',
                'name_ms' => 'Hari Keputeraan Sultan Pahang',
                'type' => 'state',
                'state' => 'Pahang',
                'year' => 2025,
            ],
            [
                'date' => '2025-07-07',
                'name' => 'Hari Hol Pahang',
                'name_ms' => 'Hari Hol Pahang',
                'type' => 'state',
                'state' => 'Pahang',
                'year' => 2025,
            ],
        ];

        foreach ($holidays as $holiday) {
            PublicHoliday::create($holiday);
        }
    }
}
