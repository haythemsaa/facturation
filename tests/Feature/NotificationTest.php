<?php

namespace Tests\Feature;

use App\Models\Notification;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class NotificationTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        // Create tenant and user for testing
        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create(['tenant_id' => $this->tenant->id]);
    }

    /**
     * Test user can retrieve their notifications.
     */
    public function test_user_can_retrieve_notifications(): void
    {
        Sanctum::actingAs($this->user);

        // Create some notifications for the user
        Notification::factory(3)->create([
            'tenant_id' => $this->tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
        ]);

        $response = $this->getJson('/api/notifications');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data');
    }

    /**
     * Test user can get unread count.
     */
    public function test_user_can_get_unread_count(): void
    {
        Sanctum::actingAs($this->user);

        // Create 2 unread and 1 read notification
        Notification::factory(2)->create([
            'tenant_id' => $this->tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => now(),
        ]);

        $response = $this->getJson('/api/notifications/unread-count');

        $response->assertStatus(200)
            ->assertJson(['count' => 2]);
    }

    /**
     * Test user can mark notification as read.
     */
    public function test_user_can_mark_notification_as_read(): void
    {
        Sanctum::actingAs($this->user);

        $notification = Notification::factory()->create([
            'tenant_id' => $this->tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->postJson("/api/notifications/{$notification->id}/read");

        $response->assertStatus(200);
        $this->assertNotNull($notification->fresh()->read_at);
    }

    /**
     * Test user can mark all notifications as read.
     */
    public function test_user_can_mark_all_notifications_as_read(): void
    {
        Sanctum::actingAs($this->user);

        // Create 3 unread notifications
        Notification::factory(3)->create([
            'tenant_id' => $this->tenant->id,
            'notifiable_type' => User::class,
            'notifiable_id' => $this->user->id,
            'read_at' => null,
        ]);

        $response = $this->postJson('/api/notifications/mark-all-read');

        $response->assertStatus(200);

        // Verify all notifications are now marked as read
        $unreadCount = Notification::where('notifiable_id', $this->user->id)
            ->whereNull('read_at')
            ->count();

        $this->assertEquals(0, $unreadCount);
    }

    /**
     * Test guest cannot access notifications.
     */
    public function test_guest_cannot_access_notifications(): void
    {
        $response = $this->getJson('/api/notifications');

        $response->assertStatus(401);
    }
}
