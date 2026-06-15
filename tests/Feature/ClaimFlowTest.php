<?php

namespace Tests\Feature;

use App\Models\Claim;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\ItemNotification;
use App\Models\LostItem;
use App\Models\Reward;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ClaimFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Mail::fake();
    }

    private function buildMatch(User $owner, User $finder): ItemMatch
    {
        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'HP Laptop',
            'category'      => 'Electronics',
            'description'   => 'Black HP Pavilion laptop',
            'color'         => 'black',
            'brand'         => 'HP',
            'location_lost' => 'CIT',
            'date_lost'     => '2026-04-20',
            'status'        => 'active',
            'tracking_id'   => 'LOST-CLAIM' . uniqid(),
        ]);

        $found = FoundItem::create([
            'user_id'        => $finder->id,
            'item_name'      => 'HP Laptop',
            'category'       => 'Electronics',
            'description'    => 'Black HP Pavilion laptop',
            'color'          => 'black',
            'brand'          => 'HP',
            'location_found' => 'CIT',
            'date_found'     => '2026-04-21',
            'status'         => 'active',
            'tracking_id'    => 'FOUND-CLAIM' . uniqid(),
        ]);

        return ItemMatch::create([
            'lost_item_id'     => $lost->id,
            'found_item_id'    => $found->id,
            'confidence_score' => 90,
            'match_status'     => 'pending',
        ]);
    }

    public function test_owner_can_file_a_claim_against_their_match(): void
    {
        $owner  = User::factory()->create();
        $finder = User::factory()->create();
        $match  = $this->buildMatch($owner, $finder);

        $response = $this->actingAs($owner)->post(route('claims.store'), [
            'match_id'             => $match->id,
            'verification_details' => 'I have the original receipt and a unique sticker on the lid.',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('claims', [
            'match_id'     => $match->id,
            'claimant_id'  => $owner->id,
            'claim_status' => 'pending',
        ]);
    }

    public function test_non_owner_cannot_claim_someone_elses_match(): void
    {
        $owner    = User::factory()->create();
        $finder   = User::factory()->create();
        $stranger = User::factory()->create();

        $match = $this->buildMatch($owner, $finder);

        $response = $this->actingAs($stranger)->post(route('claims.store'), [
            'match_id'             => $match->id,
            'verification_details' => 'I am claiming an item that is not mine to test authorization.',
        ]);

        $response->assertForbidden();
        $this->assertDatabaseCount('claims', 0);
    }

    public function test_admin_approval_marks_items_returned_and_awards_points(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $owner  = User::factory()->create(['reward_points' => 0]);
        $finder = User::factory()->create();

        $match = $this->buildMatch($owner, $finder);

        $claim = Claim::create([
            'match_id'             => $match->id,
            'claimant_id'          => $owner->id,
            'verification_details' => 'I have proof of ownership and a sticker description.',
            'claim_status'         => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.claims.approve', $claim));

        $response->assertRedirect();

        // Claim resolved, items returned, match confirmed
        $claim->refresh();
        $this->assertEquals('approved', $claim->claim_status);
        $this->assertNotNull($claim->resolved_at);
        $this->assertEquals('returned', $match->lostItem->fresh()->status);
        $this->assertEquals('returned', $match->foundItem->fresh()->status);
        $this->assertEquals('confirmed', $match->fresh()->match_status);

        // 20 points awarded + Reward record + notifications dispatched
        $this->assertEquals(20, $owner->fresh()->reward_points);
        $this->assertDatabaseHas('rewards', [
            'user_id'        => $owner->id,
            'claim_id'       => $claim->id,
            'action_type'    => 'successful_return',
            'points_awarded' => 20,
        ]);
        $this->assertTrue(
            ItemNotification::where('user_id', $owner->id)->where('type', 'claim_approved')->exists()
        );
        $this->assertTrue(
            ItemNotification::where('user_id', $finder->id)->where('type', 'claim_approved')->exists()
        );
    }

    public function test_admin_rejection_notifies_claimant_without_returning_item(): void
    {
        $admin  = User::factory()->create(['role' => 'admin']);
        $owner  = User::factory()->create(['reward_points' => 0]);
        $finder = User::factory()->create();

        $match = $this->buildMatch($owner, $finder);

        $claim = Claim::create([
            'match_id'             => $match->id,
            'claimant_id'          => $owner->id,
            'verification_details' => 'Verification details for testing rejection flow.',
            'claim_status'         => 'pending',
        ]);

        $response = $this->actingAs($admin)
            ->post(route('admin.claims.reject', $claim), ['reason' => 'Insufficient proof']);

        $response->assertRedirect();

        $claim->refresh();
        $this->assertEquals('rejected', $claim->claim_status);
        $this->assertEquals(0, $owner->fresh()->reward_points);
        $this->assertEquals('active', $match->lostItem->fresh()->status);
        $this->assertTrue(
            ItemNotification::where('user_id', $owner->id)->where('type', 'claim_rejected')->exists()
        );
    }

    public function test_peer_to_peer_confirm_return_marks_item_and_awards_points(): void
    {
        $owner  = User::factory()->create(['reward_points' => 0]);
        $finder = User::factory()->create();

        $match = $this->buildMatch($owner, $finder);

        // Peer-to-peer is gated to non-high-value items
        $match->lostItem->update(['is_high_value' => false]);
        $match->foundItem->update(['is_high_value' => false]);

        $response = $this->actingAs($owner)
            ->post(route('claims.confirm-return', $match));

        $response->assertRedirect();

        $this->assertEquals('returned', $match->lostItem->fresh()->status);
        $this->assertEquals('returned', $match->foundItem->fresh()->status);
        $this->assertEquals('confirmed', $match->fresh()->match_status);
        $this->assertEquals(20, $owner->fresh()->reward_points);
        $this->assertDatabaseHas('claims', [
            'match_id'     => $match->id,
            'claim_status' => 'approved',
        ]);
    }
}
