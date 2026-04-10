<?php
namespace App\Services;

use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\ItemMatch;

class MatchingService
{
    public function findMatchesForLostItem(LostItem $lostItem)
    {
        $foundItems = FoundItem::where('status', 'active')->get();
        $matches = collect();

        foreach ($foundItems as $foundItem) {
            $score = $this->calculateScore($lostItem, $foundItem);
            if ($score >= 75) {
                $existing = ItemMatch::where('lost_item_id', $lostItem->id)
                    ->where('found_item_id', $foundItem->id)->first();
                if (!$existing) {
                    $match = ItemMatch::create([
                        'lost_item_id'    => $lostItem->id,
                        'found_item_id'   => $foundItem->id,
                        'confidence_score'=> $score,
                        'match_status'    => 'pending',
                    ]);
                    $matches->push($match);
                }
            }
        }
        return $matches->sortByDesc('confidence_score');
    }

    public function findMatchesForFoundItem(FoundItem $foundItem)
    {
        $lostItems = LostItem::where('status', 'active')->get();
        $matches = collect();

        foreach ($lostItems as $lostItem) {
            $score = $this->calculateScore($lostItem, $foundItem);
            if ($score >= 75) {
                $existing = ItemMatch::where('lost_item_id', $lostItem->id)
                    ->where('found_item_id', $foundItem->id)->first();
                if (!$existing) {
                    $match = ItemMatch::create([
                        'lost_item_id'    => $lostItem->id,
                        'found_item_id'   => $foundItem->id,
                        'confidence_score'=> $score,
                        'match_status'    => 'pending',
                    ]);
                    $matches->push($match);
                }
            }
        }
        return $matches->sortByDesc('confidence_score');
    }

    private function calculateScore(LostItem $lost, FoundItem $found): float
    {
        $score = 0;

        // Category match — 30 points
        if (strtolower($lost->category) === strtolower($found->category)) {
            $score += 30;
        }

        // Color match — 20 points
        if (strtolower($lost->color) === strtolower($found->color)) {
            $score += 20;
        }

        // Brand match — 15 points
        if ($lost->brand && $found->brand &&
            strtolower($lost->brand) === strtolower($found->brand)) {
            $score += 15;
        }

        // Location match — 20 points
        similar_text(
            strtolower($lost->location_lost),
            strtolower($found->location_found),
            $locationPercent
        );
        if ($locationPercent >= 50) {
            $score += 20;
        }

        // Date within 7 days — 15 points
        $dateLost  = \Carbon\Carbon::parse($lost->date_lost);
        $dateFound = \Carbon\Carbon::parse($found->date_found);
        if (abs($dateLost->diffInDays($dateFound)) <= 7) {
            $score += 15;
        }

        return $score;
    }
}