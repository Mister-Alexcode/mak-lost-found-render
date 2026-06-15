<?php

namespace Tests\Unit;

use App\Models\FoundItem;
use App\Models\ItemMatch;
use App\Models\LostItem;
use App\Models\User;
use App\Services\MatchingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use ReflectionMethod;
use Tests\TestCase;

class MatchingServiceTest extends TestCase
{
    use RefreshDatabase;

    private MatchingService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new MatchingService();
    }

    private function score(LostItem $lost, FoundItem $found): float
    {
        $method = new ReflectionMethod(MatchingService::class, 'calculateScore');
        $method->setAccessible(true);
        return $method->invoke($this->service, $lost, $found);
    }

    private function makeLost(array $overrides = []): LostItem
    {
        return new LostItem(array_merge([
            'user_id'       => 1,
            'item_name'     => 'Black HP Laptop',
            'category'      => 'Electronics',
            'description'   => 'Black HP Pavilion laptop with stickers on the lid',
            'color'         => 'black',
            'brand'         => 'HP',
            'location_lost' => 'CIT Block B',
            'date_lost'     => '2026-04-20',
        ], $overrides));
    }

    private function makeFound(array $overrides = []): FoundItem
    {
        return new FoundItem(array_merge([
            'user_id'        => 2,
            'item_name'      => 'Black HP Laptop',
            'category'       => 'Electronics',
            'description'    => 'Black HP Pavilion laptop with stickers',
            'color'          => 'black',
            'brand'          => 'HP',
            'location_found' => 'CIT Block B',
            'date_found'     => '2026-04-20',
        ], $overrides));
    }

    public function test_identical_items_score_at_or_near_maximum(): void
    {
        $lost  = $this->makeLost();
        $found = $this->makeFound();

        $score = $this->score($lost, $found);

        $this->assertGreaterThanOrEqual(95, $score);
        $this->assertLessThanOrEqual(100, $score);
    }

    public function test_color_synonyms_are_treated_as_matches(): void
    {
        $lost  = $this->makeLost(['color' => 'navy']);
        $found = $this->makeFound(['color' => 'blue']);

        $score = $this->score($lost, $found);

        $this->assertGreaterThanOrEqual(70, $score, 'Navy should be recognized as a blue synonym');
    }

    public function test_brand_aliases_are_treated_as_matches(): void
    {
        $lost  = $this->makeLost(['brand' => 'Hewlett Packard']);
        $found = $this->makeFound(['brand' => 'HP']);

        $score = $this->score($lost, $found);

        $this->assertGreaterThanOrEqual(70, $score, 'Hewlett Packard should match HP via alias');
    }

    public function test_campus_location_synonyms_match(): void
    {
        $lost  = $this->makeLost(['location_lost' => 'College of Computing']);
        $found = $this->makeFound(['location_found' => 'CIT']);

        $score = $this->score($lost, $found);

        $this->assertGreaterThanOrEqual(70, $score, 'CIT and College of Computing should match');
    }

    public function test_date_proximity_decays_correctly(): void
    {
        $closeLost  = $this->makeLost(['date_lost'  => '2026-04-20']);
        $closeFound = $this->makeFound(['date_found' => '2026-04-21']);

        $farLost  = $this->makeLost(['date_lost'  => '2026-03-01']);
        $farFound = $this->makeFound(['date_found' => '2026-04-21']);

        $closeScore = $this->score($closeLost, $closeFound);
        $farScore   = $this->score($farLost, $farFound);

        $this->assertGreaterThan($farScore, $closeScore, 'Closer dates must outscore distant dates');
    }

    public function test_unrelated_items_score_below_match_threshold(): void
    {
        $lost  = $this->makeLost([
            'item_name'     => 'Red Umbrella',
            'category'      => 'Accessories',
            'description'   => 'A small red umbrella with a wooden handle',
            'color'         => 'red',
            'brand'         => null,
            'location_lost' => 'Library reading room',
            'date_lost'     => '2026-01-01',
        ]);
        $found = $this->makeFound([
            'item_name'      => 'Calculator',
            'category'       => 'Electronics',
            'description'    => 'Casio scientific calculator, slightly scratched',
            'color'          => 'grey',
            'brand'          => 'Casio',
            'location_found' => 'Sports ground',
            'date_found'     => '2026-04-21',
        ]);

        $score = $this->score($lost, $found);

        $this->assertLessThan(55, $score, 'Unrelated items must fall below the 55-point threshold');
    }

    public function test_find_matches_creates_item_match_above_threshold(): void
    {
        $owner  = User::factory()->create();
        $finder = User::factory()->create();

        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'Black HP Laptop',
            'category'      => 'Electronics',
            'description'   => 'Black HP Pavilion 14 with red sticker',
            'color'         => 'black',
            'brand'         => 'HP',
            'location_lost' => 'CIT Block B',
            'date_lost'     => '2026-04-20',
            'status'        => 'active',
            'tracking_id'   => 'LOST-TEST0001',
        ]);

        FoundItem::create([
            'user_id'        => $finder->id,
            'item_name'      => 'HP Laptop',
            'category'       => 'Electronics',
            'description'    => 'HP Pavilion laptop, red sticker on lid',
            'color'          => 'black',
            'brand'          => 'HP',
            'location_found' => 'College of Computing',
            'date_found'     => '2026-04-21',
            'status'         => 'active',
            'tracking_id'    => 'FOUND-TEST0001',
        ]);

        $matches = $this->service->findMatchesForLostItem($lost);

        $this->assertCount(1, $matches);
        $this->assertGreaterThanOrEqual(55, $matches->first()->confidence_score);
        $this->assertLessThanOrEqual(99, $matches->first()->confidence_score);
        $this->assertDatabaseCount('item_matches', 1);
    }

    public function test_find_matches_does_not_create_for_unrelated_items(): void
    {
        $owner  = User::factory()->create();
        $finder = User::factory()->create();

        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'Red Umbrella',
            'category'      => 'Accessories',
            'description'   => 'Compact red umbrella with wooden handle',
            'color'         => 'red',
            'brand'         => null,
            'location_lost' => 'Library',
            'date_lost'     => '2026-01-01',
            'status'        => 'active',
            'tracking_id'   => 'LOST-TEST0002',
        ]);

        FoundItem::create([
            'user_id'        => $finder->id,
            'item_name'      => 'Casio Calculator',
            'category'       => 'Electronics',
            'description'    => 'Grey scientific calculator',
            'color'          => 'grey',
            'brand'          => 'Casio',
            'location_found' => 'Sports ground',
            'date_found'     => '2026-04-21',
            'status'         => 'active',
            'tracking_id'    => 'FOUND-TEST0002',
        ]);

        $matches = $this->service->findMatchesForLostItem($lost);

        $this->assertCount(0, $matches);
        $this->assertDatabaseCount('item_matches', 0);
    }

    public function test_duplicate_matches_are_not_recreated(): void
    {
        $owner  = User::factory()->create();
        $finder = User::factory()->create();

        $lost = LostItem::create([
            'user_id'       => $owner->id,
            'item_name'     => 'Black HP Laptop',
            'category'      => 'Electronics',
            'description'   => 'Black HP Pavilion 14',
            'color'         => 'black',
            'brand'         => 'HP',
            'location_lost' => 'CIT',
            'date_lost'     => '2026-04-20',
            'status'        => 'active',
            'tracking_id'   => 'LOST-TEST0003',
        ]);

        FoundItem::create([
            'user_id'        => $finder->id,
            'item_name'      => 'HP Laptop',
            'category'       => 'Electronics',
            'description'    => 'HP Pavilion 14 laptop',
            'color'          => 'black',
            'brand'          => 'HP',
            'location_found' => 'CIT',
            'date_found'     => '2026-04-21',
            'status'         => 'active',
            'tracking_id'    => 'FOUND-TEST0003',
        ]);

        $first  = $this->service->findMatchesForLostItem($lost);
        $second = $this->service->findMatchesForLostItem($lost);

        $this->assertCount(1, $first);
        $this->assertCount(0, $second, 'A second run must not duplicate the existing match');
        $this->assertEquals(1, ItemMatch::count());
    }
}
