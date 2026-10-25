<?php

namespace Tests\Feature\Jobs;

use App\Constants\Permissions;
use App\Constants\RoomStatus;
use App\Jobs\NotifyStuckCleaningRooms;
use App\Models\Hotel;
use App\Models\Room;
use App\Models\RoomType;
use App\Models\User;
use App\Notifications\Housekeeping\StuckInCleaningNotification;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Spatie\Permission\PermissionRegistrar;
use Tests\TestCase;

class NotifyStuckCleaningRoomsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    public function test_notify_stuck_cleaning_rooms_sends_notification_for_stuck_rooms(): void
    {
        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $hotel = Hotel::factory()->create();

        $manager = User::factory()->create();
        $manager->hotels()->attach($hotel->id);

        // Set the permission team before assigning the role.
        setPermissionsTeamId($hotel->id);
        $manager->assignRole('hotel_manager');

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        // Room stuck in cleaning for three hours.
        $stuckRoom = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subHours(3),
        ]);

        // Room recently moved to cleaning.
        $recentRoom = Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subMinutes(10),
        ]);

        NotifyStuckCleaningRooms::dispatchSync();

        Notification::assertSentTo(
            $manager,
            StuckInCleaningNotification::class,
            function ($notification) use ($stuckRoom) {
                return $notification->room->id === $stuckRoom->id;
            }
        );

        // The job should not mutate room statuses.
        $this->assertEquals(
            RoomStatus::CLEANING,
            $stuckRoom->fresh()->status
        );

        $this->assertEquals(
            RoomStatus::CLEANING,
            $recentRoom->fresh()->status
        );
    }

    public function test_notify_stuck_cleaning_rooms_prevents_duplicate_notifications_within_cooldown(): void
    {
        Notification::fake();

        $this->seed(RolesAndPermissionsSeeder::class);

        $hotel = Hotel::factory()->create();

        $housekeeper = User::factory()->create();
        $housekeeper->hotels()->attach($hotel->id);

        // Set the permission team before assigning the direct permission.
        setPermissionsTeamId($hotel->id);
        $housekeeper->givePermissionTo(Permissions::VIEW_HOUSEKEEPING);

        $roomType = RoomType::factory()->create([
            'hotel_id' => $hotel->id,
        ]);

        Room::factory()->create([
            'hotel_id' => $hotel->id,
            'room_type_id' => $roomType->id,
            'status' => RoomStatus::CLEANING,
            'updated_at' => now()->subHours(3),
        ]);

        // First run should send one notification.
        NotifyStuckCleaningRooms::dispatchSync();

        Notification::assertSentToTimes(
            $housekeeper,
            StuckInCleaningNotification::class,
            1
        );

        // Second run should not send a duplicate within the cooldown.
        NotifyStuckCleaningRooms::dispatchSync();

        Notification::assertSentToTimes(
            $housekeeper,
            StuckInCleaningNotification::class,
            1
        );
    }
}
