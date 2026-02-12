<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Notification>
 */
class NotificationFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Ensure user exists
        $user = User::factory()->create();

        // Notification types for CREAMS
        $types = [
            'App\\Notifications\\ActivitySessionReminder',
            'App\\Notifications\\AttendanceMarkedNotification',
            'App\\Notifications\\EnrollmentConfirmation',
            'App\\Notifications\\MessageReceived',
            'App\\Notifications\\TraineeProgressUpdate',
            'App\\Notifications\\AssetMaintenanceDue',
            'App\\Notifications\\SystemAlert',
        ];

        $type = fake()->randomElement($types);

        // Generate notification data based on type
        $data = $this->generateNotificationData($type);

        return [
            'type' => $type,
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $user->id,
            'data' => $data,
            'read_at' => fake()->optional(0.6)->dateTimeBetween('-1 week', 'now'),
        ];
    }

    /**
     * Generate appropriate notification data based on type
     */
    protected function generateNotificationData(string $type): array
    {
        $typeShort = basename(str_replace('\\', '/', $type));

        return match (true) {
            str_contains($typeShort, 'Activity') => [
                'title' => 'Activity Session Reminder',
                'message' => 'Your session "' . fake()->catchPhrase() . '" starts in 30 minutes',
                'content' => 'Session scheduled at ' . fake()->time('H:i'),
                'action_url' => '/activities/schedule',
                'activity_id' => fake()->numberBetween(1, 100),
                'session_id' => fake()->numberBetween(1, 500),
            ],
            str_contains($typeShort, 'Attendance') => [
                'title' => 'Attendance Marked',
                'message' => 'Attendance has been marked for your session',
                'content' => fake()->randomElement(['All trainees present', 'Some absences recorded', 'Late arrivals noted']),
                'action_url' => '/activities/attendance',
                'session_id' => fake()->numberBetween(1, 500),
            ],
            str_contains($typeShort, 'Enrollment') => [
                'title' => 'Enrollment Confirmation',
                'message' => 'New trainee enrolled in your activity',
                'content' => fake()->name() . ' has been enrolled',
                'action_url' => '/activities/enrollments',
                'activity_id' => fake()->numberBetween(1, 100),
                'trainee_id' => fake()->numberBetween(1, 200),
            ],
            str_contains($typeShort, 'Message') => [
                'title' => 'New Message Received',
                'message' => 'You have received a new message from ' . fake()->name(),
                'content' => fake()->sentence(),
                'action_url' => '/messages',
                'message_id' => fake()->numberBetween(1, 1000),
            ],
            str_contains($typeShort, 'Trainee') => [
                'title' => 'Trainee Progress Update',
                'message' => 'Progress report updated for ' . fake()->name(),
                'content' => 'Competency level advanced to ' . fake()->randomElement(['Level 1', 'Level 2', 'Level 3', 'Level 4']),
                'action_url' => '/trainees/progress',
                'trainee_id' => fake()->numberBetween(1, 200),
            ],
            str_contains($typeShort, 'Asset') => [
                'title' => 'Asset Maintenance Due',
                'message' => 'Maintenance required for ' . fake()->word(),
                'content' => 'Scheduled maintenance is due within 7 days',
                'action_url' => '/assets/maintenance',
                'asset_id' => fake()->numberBetween(1, 50),
            ],
            str_contains($typeShort, 'System') => [
                'title' => 'System Alert',
                'message' => fake()->randomElement([
                    'System backup completed successfully',
                    'Database optimization completed',
                    'New features available',
                    'Security update applied',
                ]),
                'content' => fake()->sentence(),
                'action_url' => '/dashboard',
            ],
            default => [
                'title' => 'Notification',
                'message' => fake()->sentence(),
                'content' => fake()->paragraph(),
                'action_url' => '/dashboard',
            ],
        };
    }

    /**
     * Indicate that the notification is unread
     */
    public function unread(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => null,
        ]);
    }

    /**
     * Indicate that the notification is read
     */
    public function read(): static
    {
        return $this->state(fn (array $attributes) => [
            'read_at' => fake()->dateTimeBetween('-1 week', 'now'),
        ]);
    }

    /**
     * Indicate that the notification is for a message
     */
    public function message(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'App\\Notifications\\MessageReceived',
            'data' => [
                'title' => 'New Message Received',
                'message' => 'You have received a new message from ' . fake()->name(),
                'content' => fake()->sentence(),
                'action_url' => '/messages',
                'message_id' => fake()->numberBetween(1, 1000),
            ],
        ]);
    }

    /**
     * Indicate that the notification is for an activity
     */
    public function activity(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => 'App\\Notifications\\ActivitySessionReminder',
            'data' => [
                'title' => 'Activity Session Reminder',
                'message' => 'Your session starts soon',
                'content' => 'Session scheduled at ' . fake()->time('H:i'),
                'action_url' => '/activities/schedule',
                'activity_id' => fake()->numberBetween(1, 100),
                'session_id' => fake()->numberBetween(1, 500),
            ],
        ]);
    }
}
