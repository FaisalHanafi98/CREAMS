<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Final database cleanup and optimization:
     * 1. Add comprehensive indexes for performance
     * 2. Add database comments for documentation
     * 3. Create views for common queries
     * 4. Add final data integrity checks
     */
    public function up(): void
    {
        // 1. ADD PERFORMANCE INDEXES
        $this->addPerformanceIndexes();

        // 2. ADD COMPREHENSIVE DATABASE COMMENTS
        $this->addDatabaseComments();

        // 3. CREATE USEFUL VIEWS FOR REPORTING
        $this->createReportingViews();

        // 4. ADD DATA INTEGRITY CHECKS
        $this->addDataIntegrityChecks();

        // 5. FINAL STATISTICS UPDATE
        $this->updateSystemStatistics();
    }

    /**
     * Add performance indexes for common queries
     */
    private function addPerformanceIndexes(): void
    {
        // Activity and enrollment queries
        DB::statement("CREATE INDEX IF NOT EXISTS idx_activity_enrollments_status_date ON activity_enrollments (enrollment_status, enrollment_date)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_activity_sessions_date_status ON activity_sessions (session_date, session_status)");

        // Attendance performance
        DB::statement("CREATE INDEX IF NOT EXISTS idx_session_attendance_status_date ON session_attendance (attendance_status, created_at)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_trainee_attendance_search ON trainee_attendances (trainee_id, attendance_date, status)");

        // Asset management performance
        DB::statement("CREATE INDEX IF NOT EXISTS idx_assets_search ON assets (status, `condition`, is_active)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_asset_maintenance_search ON asset_maintenance (status, scheduled_date, priority)");

        // Communication and alerts
        DB::statement("CREATE INDEX IF NOT EXISTS idx_attendance_alerts_unread ON attendance_alerts (is_read, severity, created_at)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_contact_messages_status ON contact_messages (status, created_at)");

        // User and auth performance
        DB::statement("CREATE INDEX IF NOT EXISTS idx_users_active_role ON users (status, role, centre_id)");
        DB::statement("CREATE INDEX IF NOT EXISTS idx_trainees_active_centre ON trainees (status, centre_id)");
    }

    /**
     * Add comprehensive database comments for documentation
     */
    private function addDatabaseComments(): void
    {
        // Core tables
        DB::statement("ALTER TABLE users COMMENT = 'Staff users including admin, supervisors, teachers, and AJK personnel'");
        DB::statement("ALTER TABLE trainees COMMENT = 'Trainee/client records with guardian information and consent tracking'");
        DB::statement("ALTER TABLE centres COMMENT = 'Rehabilitation centers with location and contact information'");

        // Activity management
        DB::statement("ALTER TABLE activities COMMENT = 'Rehabilitation programs and services offered'");
        DB::statement("ALTER TABLE activity_sessions COMMENT = 'Individual sessions within activities with scheduling information'");
        DB::statement("ALTER TABLE activity_enrollments COMMENT = 'Trainee enrollments in activities with progress tracking'");

        // Attendance tracking
        DB::statement("ALTER TABLE session_attendance COMMENT = 'Individual trainee attendance records per session'");
        DB::statement("ALTER TABLE attendance_alerts COMMENT = 'Automated alerts for low attendance or attendance issues'");

        // Asset management
        DB::statement("ALTER TABLE assets COMMENT = 'Equipment and asset inventory with tracking information'");
        DB::statement("ALTER TABLE asset_maintenance COMMENT = 'Maintenance schedules and records for assets'");

        // Communication
        DB::statement("ALTER TABLE contact_messages COMMENT = 'Public contact form submissions from website'");
        DB::statement("ALTER TABLE volunteers COMMENT = 'Volunteer applications and management'");
        DB::statement("ALTER TABLE letters COMMENT = 'Generated letters and documents with templates'");
    }

    /**
     * Create useful reporting views
     */
    private function createReportingViews(): void
    {
        // Active trainee summary view
        DB::statement("
            CREATE OR REPLACE VIEW v_active_trainees AS
            SELECT
                t.id,
                t.trainee_id,
                CONCAT(t.trainee_first_name, ' ', t.trainee_last_name) as full_name,
                t.trainee_email,
                t.gender,
                t.centre_id,
                t.status,
                COUNT(DISTINCT ae.id) as total_enrollments,
                COUNT(DISTINCT CASE WHEN ae.enrollment_status = 'enrolled' THEN ae.id END) as active_enrollments,
                ROUND(AVG(ae.progress_percentage), 2) as avg_progress
            FROM trainees t
            LEFT JOIN activity_enrollments ae ON t.id = ae.trainee_id
            WHERE t.status = 'active'
            GROUP BY t.id, t.trainee_id, t.trainee_first_name, t.trainee_last_name,
                     t.trainee_email, t.gender, t.centre_id, t.status
        ");

        // Activity performance summary view
        DB::statement("
            CREATE OR REPLACE VIEW v_activity_summary AS
            SELECT
                a.id,
                a.activity_name,
                a.centre_id,
                COUNT(DISTINCT ae.id) as total_enrollments,
                COUNT(DISTINCT CASE WHEN ae.enrollment_status = 'enrolled' THEN ae.id END) as active_enrollments,
                COUNT(DISTINCT CASE WHEN ae.enrollment_status = 'completed' THEN ae.id END) as completed_enrollments,
                COUNT(DISTINCT asess.id) as total_sessions,
                COUNT(DISTINCT CASE WHEN asess.session_status = 'completed' THEN asess.id END) as completed_sessions,
                ROUND(AVG(ae.progress_percentage), 2) as avg_progress
            FROM activities a
            LEFT JOIN activity_enrollments ae ON a.id = ae.activity_id
            LEFT JOIN activity_sessions asess ON a.id = asess.activity_id
            WHERE a.is_active = 1
            GROUP BY a.id, a.activity_name, a.centre_id
        ");

        // Attendance rate view
        DB::statement("
            CREATE OR REPLACE VIEW v_attendance_rates AS
            SELECT
                t.id as trainee_id,
                CONCAT(t.trainee_first_name, ' ', t.trainee_last_name) as trainee_name,
                t.centre_id,
                COUNT(sa.id) as total_sessions,
                SUM(CASE WHEN sa.attendance_status IN ('present', 'late') THEN 1 ELSE 0 END) as attended_sessions,
                ROUND(
                    (SUM(CASE WHEN sa.attendance_status IN ('present', 'late') THEN 1 ELSE 0 END) * 100.0) / COUNT(sa.id),
                    1
                ) as attendance_rate
            FROM trainees t
            JOIN activity_enrollments ae ON t.id = ae.trainee_id
            JOIN activity_sessions asess ON ae.activity_id = asess.activity_id
            JOIN session_attendance sa ON asess.id = sa.session_id AND sa.trainee_id = t.id
            WHERE asess.session_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY t.id, t.trainee_first_name, t.trainee_last_name, t.centre_id
        ");
    }

    /**
     * Add data integrity checks
     */
    private function addDataIntegrityChecks(): void
    {
        // Create a function to check data integrity
        DB::statement("
            CREATE OR REPLACE VIEW v_data_integrity_check AS
            SELECT
                'Orphaned Enrollments' as check_name,
                COUNT(*) as issue_count,
                'activity_enrollments without valid trainee_id' as description
            FROM activity_enrollments ae
            LEFT JOIN trainees t ON ae.trainee_id = t.id
            WHERE t.id IS NULL

            UNION ALL

            SELECT
                'Missing Enrolled By',
                COUNT(*),
                'activity_enrollments without enrolled_by user'
            FROM activity_enrollments
            WHERE enrolled_by IS NULL

            UNION ALL

            SELECT
                'Future Sessions Overdue',
                COUNT(*),
                'activity_sessions marked as completed but in future'
            FROM activity_sessions
            WHERE session_status = 'completed' AND session_date > CURDATE()

            UNION ALL

            SELECT
                'Assets Without Location',
                COUNT(*),
                'assets without assigned location'
            FROM assets
            WHERE location_id IS NULL AND status != 'disposed'

            UNION ALL

            SELECT
                'Inactive Users With Active Sessions',
                COUNT(*),
                'inactive users assigned to upcoming sessions'
            FROM activity_sessions asess
            JOIN users u ON asess.instructor_id = u.id
            WHERE u.status != 'active' AND asess.session_date >= CURDATE()
        ");
    }

    /**
     * Update system statistics
     */
    private function updateSystemStatistics(): void
    {
        // Update current_participants counts one final time
        DB::statement("
            UPDATE activity_sessions
            SET current_participants = (
                SELECT COUNT(DISTINCT ae.trainee_id)
                FROM activity_enrollments ae
                WHERE ae.activity_id = activity_sessions.activity_id
                AND ae.enrollment_status IN ('enrolled', 'pending')
            )
        ");

        // Note: times_conducted column was removed in previous migration
        // Skip updating times_conducted as column no longer exists

        // Update trainee attendance counts
        DB::statement("
            UPDATE activity_enrollments ae
            SET attendance_count = (
                SELECT COUNT(*)
                FROM session_attendance sa
                JOIN activity_sessions asess ON sa.session_id = asess.id
                WHERE sa.trainee_id = ae.trainee_id
                AND asess.activity_id = ae.activity_id
                AND sa.attendance_status IN ('present', 'late')
            )
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop views
        DB::statement("DROP VIEW IF EXISTS v_active_trainees");
        DB::statement("DROP VIEW IF EXISTS v_activity_summary");
        DB::statement("DROP VIEW IF EXISTS v_attendance_rates");
        DB::statement("DROP VIEW IF EXISTS v_data_integrity_check");

        // Drop performance indexes
        DB::statement("DROP INDEX IF EXISTS idx_activity_enrollments_status_date ON activity_enrollments");
        DB::statement("DROP INDEX IF EXISTS idx_activity_sessions_date_status ON activity_sessions");
        DB::statement("DROP INDEX IF EXISTS idx_session_attendance_status_date ON session_attendance");
        DB::statement("DROP INDEX IF EXISTS idx_trainee_attendance_search ON trainee_attendances");
        DB::statement("DROP INDEX IF EXISTS idx_assets_search ON assets");
        DB::statement("DROP INDEX IF EXISTS idx_asset_maintenance_search ON asset_maintenance");
        DB::statement("DROP INDEX IF EXISTS idx_attendance_alerts_unread ON attendance_alerts");
        DB::statement("DROP INDEX IF EXISTS idx_contact_messages_status ON contact_messages");
        DB::statement("DROP INDEX IF EXISTS idx_users_active_role ON users");
        DB::statement("DROP INDEX IF EXISTS idx_trainees_active_centre ON trainees");
    }
};