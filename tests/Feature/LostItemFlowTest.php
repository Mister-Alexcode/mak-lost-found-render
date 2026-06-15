<?php

namespace Tests\Feature;

use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\ItemNotification;
use App\Models\LostItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class LostItemFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    public function test_authenticated_user_can_report_a_lost_item(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('lost-items.store'), [
            'item_name'     => 'Black Backpack',
            'category'      => 'Bags',
            'description'   => 'Black canvas backpack with red zipper',
            'color'         => 'black',
            'brand'         => null,
            'location_lost' => 'Main Library',
            'date_lost'     => '2026-04-20',
        ]);

        $response->assertRedirect(route('lost-items.index'));
        $this->assertDatabaseHas('lost_items', [
            'user_id'   => $user->id,
            'item_name' => 'Black Backpack',
            'status'    => 'active',
        ]);
    }

    public function test_reporting_lost_item_with_matching_found_creates_match_and_notification(): void
    {
        $owner  = User::factory()->create();
        $finder = User::factory()->create();

        // Pre-existing found item that should match the new lost report
        FoundItem::create([
            'user_id'        => $finder->id,
            'item_name'      => 'HP Laptop',
            'category'       => 'Electronics',
            'description'    => 'Black HP Pavilion 14, sticker on lid',
            'color'          => 'black',
            'brand'          => 'HP',
            'location_found' => 'CIT',
            'date_found'     => '2026-04-21',
            'status'         => 'active',
            'tracking_id'    => 'FOUND-MATCH001',
        ]);

        $this->actingAs($owner)->post(route('lost-items.store'), [
            'item_name'     => 'HP Laptop',
            'category'      => 'Electronics',
            'description'   => 'Black HP Pavilion 14 laptop with sticker',
            'color'         => 'black',
            'brand'         => 'HP',
            'location_lost' => 'College of Computing',
            'date_lost'     => '2026-04-20',
        ]);

        $this->assertEquals(1, ItemMatch::count());
        $match = ItemMatch::first();
        $this->assertGreaterThanOrEqual(55, $match->confidence_score);

        // Both reporters should have been notified in-app
        $this->assertTrue(
            ItemNotification::where('user_id', $owner->id)->where('type', 'match_found')->exists()
        );
        $this->assertTrue(
            ItemNotification::where('user_id', $finder->id)->where('type', 'match_found')->exists()
        );
    }

    public function test_owner_can_update_their_lost_item(): void
    {
        $owner = User::factory()->create();

        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'Old Name',
            'category'      => 'Bags',
            'description'   => 'A bag',
            'color'         => 'black',
            'location_lost' => 'Library',
            'date_lost'     => '2026-04-20',
            'status'        => 'active',
            'tracking_id'   => 'LOST-UPDATE001',
        ]);

        $response = $this->actingAs($owner)->put(route('lost-items.update', $lost), [
            'item_name'     => 'New Name',
            'category'      => 'Bags',
            'description'   => 'Updated description here',
            'color'         => 'black',
            'location_lost' => 'Library',
            'date_lost'     => '2026-04-20',
        ]);

        $response->assertRedirect(route('lost-items.index'));
        $this->assertDatabaseHas('lost_items', [
            'id'        => $lost->id,
            'item_name' => 'New Name',
        ]);
    }

    public function test_non_owner_cannot_update_lost_item(): void
    {
        $owner    = User::factory()->create();
        $stranger = User::factory()->create();

        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'Owner Item',
            'category'      => 'Bags',
            'description'   => 'A bag',
            'color'         => 'black',
            'location_lost' => 'Library',
            'date_lost'     => '2026-04-20',
            'status'        => 'active',
            'tracking_id'   => 'LOST-OWN001',
        ]);

        $response = $this->actingAs($stranger)->put(route('lost-items.update', $lost), [
            'item_name'     => 'Hijacked',
            'category'      => 'Bags',
            'description'   => 'attempted hijack',
            'color'         => 'black',
            'location_lost' => 'Library',
            'date_lost'     => '2026-04-20',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('lost_items', [
            'id'        => $lost->id,
            'item_name' => 'Owner Item',
        ]);
    }

    public function test_lost_item_creation_validates_required_fields(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->from(route('lost-items.create'))
            ->post(route('lost-items.store'), [
                'item_name' => '',
            ]);

        $response->assertRedirect(route('lost-items.create'));
        $response->assertSessionHasErrors(['item_name', 'category', 'description', 'color', 'location_lost', 'date_lost']);
    }
}
