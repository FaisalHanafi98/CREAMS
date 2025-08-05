<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CREAMSLetterTemplatesSeeder extends Seeder
{
    /**
     * Run the database seeds for letter templates
     */
    public function run(): void
    {
        $this->command->info('📝 Creating essential letter templates...');

        try {
            DB::beginTransaction();

            // Get first centre and admin user for template creation
            $centre = DB::table('centres')->first();
            $admin = DB::table('users')->where('role', 'admin')->first();

            if (!$centre || !$admin) {
                throw new \Exception('Centre or admin user not found. Please run centres and users seeders first.');
            }

            $letterTemplates = [
                [
                    'template_name' => 'Progress Report Letter',
                    'template_description' => 'Official letter for trainee progress reports to parents/guardians',
                    'template_content' => $this->getProgressReportTemplate(),
                    'template_type' => 'progress_report',
                    'template_variables' => json_encode([
                        'trainee_name', 'guardian_name', 'period_start', 'period_end',
                        'achievements', 'areas_improvement', 'next_goals', 'therapist_name'
                    ]),
                    'is_active' => true,
                    'usage_count' => 0,
                ],
                [
                    'template_name' => 'Assessment Report Letter',
                    'template_description' => 'Letter template for initial assessment results',
                    'template_content' => $this->getAssessmentReportTemplate(),
                    'template_type' => 'assessment_report',
                    'template_variables' => json_encode([
                        'trainee_name', 'guardian_name', 'assessment_date',
                        'assessment_results', 'recommendations', 'therapist_name'
                    ]),
                    'is_active' => true,
                    'usage_count' => 0,
                ],
                [
                    'template_name' => 'Attendance Notice',
                    'template_description' => 'Letter template for attendance-related communications',
                    'template_content' => $this->getAttendanceNoticeTemplate(),
                    'template_type' => 'attendance_notice',
                    'template_variables' => json_encode([
                        'trainee_name', 'guardian_name', 'attendance_period',
                        'attendance_rate', 'missed_sessions', 'action_required'
                    ]),
                    'is_active' => true,
                    'usage_count' => 0,
                ],
                [
                    'template_name' => 'Program Completion Certificate',
                    'template_description' => 'Certificate of completion for rehabilitation programs',
                    'template_content' => $this->getCertificateTemplate(),
                    'template_type' => 'certificate',
                    'template_variables' => json_encode([
                        'trainee_name', 'program_name', 'completion_date',
                        'duration', 'achievements', 'centre_name'
                    ]),
                    'is_active' => true,
                    'usage_count' => 0,
                ],
                [
                    'template_name' => 'Appointment Reminder',
                    'template_description' => 'Letter template for appointment reminders',
                    'template_content' => $this->getAppointmentReminderTemplate(),
                    'template_type' => 'appointment',
                    'template_variables' => json_encode([
                        'trainee_name', 'guardian_name', 'appointment_date',
                        'appointment_time', 'location', 'preparation_notes'
                    ]),
                    'is_active' => true,
                    'usage_count' => 0,
                ],
            ];

            foreach ($letterTemplates as $template) {
                DB::table('letter_templates')->insert(array_merge($template, [
                    'created_by' => $admin->id,
                    'centre_id' => $centre->centre_id,
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ]));
            }

            DB::commit();

            $this->command->info('✅ Letter templates seeded successfully!');
            $this->showLetterTemplatesStatistics();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->command->error('❌ Failed to seed letter templates: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get progress report template content
     */
    private function getProgressReportTemplate(): string
    {
        return '
[CENTRE_LETTERHEAD]

Date: [DATE]

Dear [guardian_name],

RE: Progress Report for [trainee_name]

We are pleased to provide you with the progress report for [trainee_name] for the period from [period_start] to [period_end].

ACHIEVEMENTS:
[achievements]

AREAS FOR IMPROVEMENT:
[areas_improvement]

NEXT PERIOD GOALS:
[next_goals]

We appreciate your continued support and look forward to [trainee_name]\'s continued progress.

Sincerely,

[therapist_name]
[CENTRE_NAME]
[CENTRE_CONTACT]
        ';
    }

    /**
     * Get assessment report template content
     */
    private function getAssessmentReportTemplate(): string
    {
        return '
[CENTRE_LETTERHEAD]

Date: [DATE]

Dear [guardian_name],

RE: Assessment Report for [trainee_name]

Following the comprehensive assessment conducted on [assessment_date], we are pleased to share the results and our recommendations.

ASSESSMENT RESULTS:
[assessment_results]

RECOMMENDATIONS:
[recommendations]

We look forward to working with you to support [trainee_name]\'s development.

Best regards,

[therapist_name]
Assessment Team
[CENTRE_NAME]
        ';
    }

    /**
     * Get attendance notice template content
     */
    private function getAttendanceNoticeTemplate(): string
    {
        return '
[CENTRE_LETTERHEAD]

Date: [DATE]

Dear [guardian_name],

RE: Attendance Notice for [trainee_name]

We would like to bring to your attention [trainee_name]\'s attendance record for [attendance_period].

Current attendance rate: [attendance_rate]%
Sessions missed: [missed_sessions]

[action_required]

Please contact us if you have any concerns or need to discuss alternative arrangements.

Regards,

Administration Team
[CENTRE_NAME]
[CENTRE_CONTACT]
        ';
    }

    /**
     * Get certificate template content
     */
    private function getCertificateTemplate(): string
    {
        return '
[CENTRE_LETTERHEAD]

CERTIFICATE OF COMPLETION

This is to certify that

[trainee_name]

has successfully completed the

[program_name]

on [completion_date]

Duration: [duration]

Achievements: [achievements]

Awarded by [centre_name]

_____________________
Program Director
        ';
    }

    /**
     * Get appointment reminder template content
     */
    private function getAppointmentReminderTemplate(): string
    {
        return '
[CENTRE_LETTERHEAD]

Date: [DATE]

Dear [guardian_name],

RE: Appointment Reminder for [trainee_name]

This is to remind you of [trainee_name]\'s upcoming appointment:

Date: [appointment_date]
Time: [appointment_time]
Location: [location]

PREPARATION NOTES:
[preparation_notes]

Please contact us if you need to reschedule.

Best regards,

Appointment Team
[CENTRE_NAME]
        ';
    }

    /**
     * Show letter templates statistics
     */
    private function showLetterTemplatesStatistics(): void
    {
        $count = DB::table('letter_templates')->count();
        $activeCount = DB::table('letter_templates')->where('is_active', true)->count();
        
        $this->command->info("\n📊 LETTER TEMPLATES STATISTICS:");
        $this->command->line("   📝 Total Templates: {$count}");
        $this->command->line("   ✅ Active Templates: {$activeCount}");
        $this->command->line("   📋 Types: Progress Report, Assessment, Attendance, Certificate, Appointment");
    }
}