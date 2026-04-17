<?php

namespace Tests\Feature\Security;

use App\Models\Centre;
use App\Models\Message;
use App\Models\User;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

/**
 * Verifies that message isolation prevents cross-centre data access.
 *
 * Messages have no centre_id column, so isolation is enforced at the
 * controller layer: only the sender can view or delete their own messages.
 * A Centre B user's messages must be inaccessible to a Centre A user.
 *
 * @see docs/MULTI_CENTRE_ISOLATION.md — Message exception rationale
 * @see app/Http/Controllers/MessageController.php — sender_id ownership checks
 */
class MessageCentreIsolationTest extends TestCase
{
    use DatabaseTransactions;

    private Centre $centreA;
    private Centre $centreB;
    private User $userA;
    private User $userB;

    protected function setUp(): void
    {
        parent::setUp();

        $this->centreA = Centre::firstOrCreate(
            ['centre_id' => '01'],
            [
                'centre_name' => 'Centre Alpha',
                'centre_phone' => '+60100000001',
                'centre_email' => 'alpha@test.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $this->centreB = Centre::firstOrCreate(
            ['centre_id' => '02'],
            [
                'centre_name' => 'Centre Beta',
                'centre_phone' => '+60100000002',
                'centre_email' => 'beta@test.com',
                'centre_capacity' => 50,
                'centre_status' => 'active',
                'is_active' => true,
            ]
        );

        $this->userA = User::factory()->teacher()->create(['centre_id' => '01']);
        $this->userB = User::factory()->teacher()->create(['centre_id' => '02']);
    }

    // ---- Cross-centre read isolation ----

    public function test_centre_a_user_cannot_view_message_sent_by_centre_b_user(): void
    {
        $messageFromB = Message::create([
            'sender_id' => $this->userB->id,
            'subject' => 'Confidential Centre B Communication',
            'message_body' => 'Internal Centre B message that Centre A must not see.',
            'priority' => 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession([
                'id' => $this->userA->id,
                'role' => 'teacher',
                'centre_id' => '01',
                'logged_in' => true,
            ])
            ->get("/messages/{$messageFromB->id}");

        $response->assertRedirect(route('messages.index'));
        $response->assertSessionHas('error');
    }

    // ---- Cross-centre delete isolation ----

    public function test_centre_a_user_cannot_delete_message_sent_by_centre_b_user(): void
    {
        $messageFromB = Message::create([
            'sender_id' => $this->userB->id,
            'subject' => 'Centre B Message',
            'message_body' => 'Centre B content.',
            'priority' => 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession([
                'id' => $this->userA->id,
                'role' => 'teacher',
                'centre_id' => '01',
                'logged_in' => true,
            ])
            ->delete("/messages/{$messageFromB->id}");

        $response->assertRedirect(route('messages.index'));
        $response->assertSessionHas('error');

        // Message must still exist in the database
        $this->assertDatabaseHas('messages', ['id' => $messageFromB->id]);
    }

    // ---- Index isolation ----

    public function test_message_index_only_shows_messages_sent_by_current_user(): void
    {
        Message::create([
            'sender_id' => $this->userA->id,
            'subject' => 'Centre A Subject',
            'message_body' => 'Centre A message body.',
            'priority' => 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        Message::create([
            'sender_id' => $this->userB->id,
            'subject' => 'Centre B Subject',
            'message_body' => 'Centre B message body.',
            'priority' => 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession([
                'id' => $this->userA->id,
                'role' => 'teacher',
                'centre_id' => '01',
                'logged_in' => true,
            ])
            ->get('/messages');

        $response->assertSuccessful();
        $response->assertDontSee('Centre B Subject');
    }

    // ---- Positive case: own messages remain accessible ----

    public function test_user_can_view_their_own_sent_message(): void
    {
        $ownMessage = Message::create([
            'sender_id' => $this->userA->id,
            'subject' => 'My Own Message',
            'message_body' => 'Content I sent myself.',
            'priority' => 'normal',
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        $response = $this->actingAs($this->userA)
            ->withSession([
                'id' => $this->userA->id,
                'role' => 'teacher',
                'centre_id' => '01',
                'logged_in' => true,
            ])
            ->get("/messages/{$ownMessage->id}");

        // Must not be blocked — sender is current user
        $response->assertSuccessful();
    }
}
