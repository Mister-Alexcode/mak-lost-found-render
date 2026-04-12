<?php
namespace App\Services;

use App\Models\LostItem;
use App\Models\FoundItem;
use App\Models\ItemMatch;
use Carbon\Carbon;

class MatchingService
{
    /**
     * Color synonym groups — colors in the same group are considered equivalent.
     */
    private static array $colorSynonyms = [
        'black'  => ['black', 'dark', 'charcoal', 'jet', 'ebony', 'onyx', 'midnight'],
        'white'  => ['white', 'ivory', 'cream', 'pearl', 'off-white', 'offwhite', 'snow', 'beige'],
        'red'    => ['red', 'crimson', 'scarlet', 'maroon', 'burgundy', 'cherry', 'wine', 'ruby', 'vermillion'],
        'blue'   => ['blue', 'navy', 'navy blue', 'dark blue', 'light blue', 'sky blue', 'royal blue', 'cobalt', 'teal', 'azure', 'indigo', 'cyan', 'turquoise', 'aqua'],
        'green'  => ['green', 'dark green', 'light green', 'lime', 'olive', 'emerald', 'forest', 'mint', 'sage', 'khaki'],
        'yellow' => ['yellow', 'gold', 'golden', 'amber', 'mustard', 'lemon', 'cream yellow'],
        'orange' => ['orange', 'tangerine', 'rust', 'copper', 'peach', 'coral', 'burnt orange'],
        'purple' => ['purple', 'violet', 'lavender', 'plum', 'mauve', 'magenta', 'lilac', 'indigo'],
        'pink'   => ['pink', 'rose', 'salmon', 'fuchsia', 'magenta', 'blush', 'hot pink'],
        'brown'  => ['brown', 'tan', 'chocolate', 'coffee', 'mocha', 'chestnut', 'bronze', 'sienna', 'caramel', 'mahogany', 'walnut'],
        'grey'   => ['grey', 'gray', 'silver', 'ash', 'slate', 'charcoal', 'gunmetal', 'smoke', 'steel'],
        'multi'  => ['multicolor', 'multicolored', 'multi-color', 'multi', 'mixed', 'colorful', 'patterned', 'striped'],
    ];

    /**
     * Common brand aliases and abbreviations.
     */
    private static array $brandAliases = [
        'hp'        => ['hp', 'hewlett packard', 'hewlett-packard'],
        'samsung'   => ['samsung', 'sammy'],
        'apple'     => ['apple', 'iphone', 'ipad', 'macbook', 'airpods', 'mac'],
        'dell'      => ['dell'],
        'lenovo'    => ['lenovo', 'thinkpad', 'ideapad'],
        'nokia'     => ['nokia'],
        'tecno'     => ['tecno'],
        'infinix'   => ['infinix'],
        'itel'      => ['itel'],
        'xiaomi'    => ['xiaomi', 'redmi', 'poco', 'mi'],
        'oppo'      => ['oppo', 'realme'],
        'huawei'    => ['huawei', 'honor'],
        'jbl'       => ['jbl'],
        'sony'      => ['sony', 'playstation', 'ps5', 'ps4'],
        'toshiba'   => ['toshiba'],
        'asus'      => ['asus', 'rog'],
        'acer'      => ['acer'],
        'nike'      => ['nike'],
        'adidas'    => ['adidas'],
        'puma'      => ['puma'],
    ];

    /**
     * Item type keywords — helps detect item similarity from descriptions.
     */
    private static array $itemTypeKeywords = [
        'phone'    => ['phone', 'smartphone', 'mobile', 'cell', 'handset', 'iphone', 'android', 'tecno', 'infinix', 'samsung', 'redmi'],
        'laptop'   => ['laptop', 'notebook', 'macbook', 'thinkpad', 'ideapad', 'computer', 'pc'],
        'charger'  => ['charger', 'charging cable', 'power adapter', 'cable', 'usb-c', 'lightning'],
        'bag'      => ['bag', 'backpack', 'rucksack', 'handbag', 'purse', 'satchel', 'tote', 'duffel', 'pouch'],
        'wallet'   => ['wallet', 'purse', 'billfold', 'card holder', 'money clip'],
        'keys'     => ['key', 'keys', 'keychain', 'key ring', 'car key', 'padlock'],
        'id_card'  => ['id', 'student id', 'staff id', 'identity card', 'national id', 'permit', 'license', 'licence'],
        'book'     => ['book', 'textbook', 'notebook', 'novel', 'journal', 'diary', 'notes'],
        'glasses'  => ['glasses', 'spectacles', 'sunglasses', 'shades', 'eyewear', 'specs'],
        'headphones' => ['headphones', 'earphones', 'earbuds', 'airpods', 'headset', 'buds'],
        'watch'    => ['watch', 'wristwatch', 'smartwatch', 'timepiece'],
        'umbrella' => ['umbrella', 'brolly', 'parasol'],
        'clothing' => ['shirt', 'jacket', 'hoodie', 'sweater', 'trouser', 'pants', 'jeans', 'dress', 'skirt', 'coat', 'cap', 'hat', 'beanie', 'scarf'],
        'flash'    => ['flash', 'flash drive', 'usb', 'thumb drive', 'pen drive', 'memory stick', 'pendrive'],
        'calculator' => ['calculator', 'calc', 'scientific calculator'],
        'bottle'   => ['bottle', 'water bottle', 'flask', 'thermos', 'tumbler'],
    ];

    /**
     * Location synonyms for Makerere University campus.
     */
    private static array $locationSynonyms = [
        'library'    => ['library', 'main library', 'africana library', 'lib', 'reading room'],
        'cit'        => ['cit', 'college of computing', 'cocis', 'block b', 'computer lab'],
        'senate'     => ['senate', 'senate building', 'admin block', 'administration'],
        'freedom'    => ['freedom square', 'freedom', 'main square', 'square'],
        'mary_stuart'=> ['mary stuart', 'mary stuart hall', 'msh'],
        'lumumba'    => ['lumumba', 'lumumba hall'],
        'mitchell'   => ['mitchell', 'mitchell hall'],
        'nsibirwa'   => ['nsibirwa', 'nsibirwa hall'],
        'complex'    => ['complex', 'the complex', 'new complex'],
        'wandegeya'  => ['wandegeya', 'wandegeya gate', 'main gate'],
        'cees'       => ['cees', 'school of education', 'education'],
        'cedat'      => ['cedat', 'engineering', 'college of engineering'],
        'canteen'    => ['canteen', 'cafeteria', 'dining', 'mess', 'food court'],
        'parking'    => ['parking', 'parking lot', 'car park'],
        'sports'     => ['sports', 'sports ground', 'field', 'pitch', 'gymnasium', 'gym'],
        'chs'        => ['chs', 'college of health', 'medical school', 'mulago'],
        'cobams'     => ['cobams', 'business school', 'commerce'],
        'chuss'      => ['chuss', 'humanities', 'arts'],
        'law'        => ['law', 'school of law', 'law faculty'],
    ];

    public function findMatchesForLostItem(LostItem $lostItem)
    {
        $foundItems = FoundItem::where('status', 'active')->get();
        $matches = collect();

        foreach ($foundItems as $foundItem) {
            $score = $this->calculateScore($lostItem, $foundItem);
            if ($score >= 55) {
                $existing = ItemMatch::where('lost_item_id', $lostItem->id)
                    ->where('found_item_id', $foundItem->id)->first();
                if (!$existing) {
                    $match = ItemMatch::create([
                        'lost_item_id'    => $lostItem->id,
                        'found_item_id'   => $foundItem->id,
                        'confidence_score'=> min(99, $score),
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
        // Exclude lost items auto-created by the claim-found-item flow — they are
        // scoped to a single found item and should not generate further matches.
        $lostItems = LostItem::where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('description')
                  ->orWhere('description', 'not like', 'Claimed from found item report:%');
            })
            ->get();
        $matches = collect();

        foreach ($lostItems as $lostItem) {
            $score = $this->calculateScore($lostItem, $foundItem);
            if ($score >= 55) {
                $existing = ItemMatch::where('lost_item_id', $lostItem->id)
                    ->where('found_item_id', $foundItem->id)->first();
                if (!$existing) {
                    $match = ItemMatch::create([
                        'lost_item_id'    => $lostItem->id,
                        'found_item_id'   => $foundItem->id,
                        'confidence_score'=> min(99, $score),
                        'match_status'    => 'pending',
                    ]);
                    $matches->push($match);
                }
            }
        }
        return $matches->sortByDesc('confidence_score');
    }

    /**
     * Calculate a match score between a lost item and a found item.
     *
     * Scoring breakdown (max 100):
     *   Category match:       25 pts
     *   Color similarity:     15 pts (with synonym awareness)
     *   Brand similarity:     10 pts (with alias matching)
     *   Location similarity:  15 pts (with campus synonym matching)
     *   Date proximity:       10 pts
     *   Description analysis: 15 pts (cross-field text similarity)
     *   Item name analysis:   10 pts (fuzzy + keyword matching)
     */
    private function calculateScore(LostItem $lost, FoundItem $found): float
    {
        $score = 0;

        // ── Category match (25 pts) ──
        if (strtolower($lost->category) === strtolower($found->category)) {
            $score += 25;
        }

        // ── Color similarity (15 pts) ──
        $score += $this->scoreColor($lost->color, $found->color) * 15;

        // ── Brand similarity (10 pts) ──
        $score += $this->scoreBrand($lost->brand, $found->brand) * 10;

        // ── Location similarity (15 pts) ──
        $score += $this->scoreLocation($lost->location_lost, $found->location_found) * 15;

        // ── Date proximity (10 pts) ──
        $score += $this->scoreDate($lost->date_lost, $found->date_found) * 10;

        // ── Description analysis (15 pts) ──
        $score += $this->scoreDescription($lost, $found) * 15;

        // ── Item name analysis (10 pts) ──
        $score += $this->scoreItemName($lost->item_name, $found->item_name) * 10;

        return round($score);
    }

    /**
     * Score color similarity with synonym awareness.
     * Returns 0.0 to 1.0
     */
    private function scoreColor(?string $lostColor, ?string $foundColor): float
    {
        if (!$lostColor || !$foundColor) return 0;

        $lost  = strtolower(trim($lostColor));
        $found = strtolower(trim($foundColor));

        // Exact match
        if ($lost === $found) return 1.0;

        // Check if both colors belong to the same synonym group
        $lostGroup  = $this->getColorGroup($lost);
        $foundGroup = $this->getColorGroup($found);

        if ($lostGroup && $foundGroup && $lostGroup === $foundGroup) {
            return 0.85;
        }

        // Check if one color string contains words from the other's group
        if ($lostGroup) {
            foreach (self::$colorSynonyms[$lostGroup] as $syn) {
                if (str_contains($found, $syn) || str_contains($syn, $found)) return 0.75;
            }
        }
        if ($foundGroup) {
            foreach (self::$colorSynonyms[$foundGroup] as $syn) {
                if (str_contains($lost, $syn) || str_contains($syn, $lost)) return 0.75;
            }
        }

        // Fuzzy text similarity as fallback
        similar_text($lost, $found, $pct);
        if ($pct >= 60) return $pct / 100 * 0.7;

        return 0;
    }

    /**
     * Find which color group a color string belongs to.
     */
    private function getColorGroup(string $color): ?string
    {
        foreach (self::$colorSynonyms as $group => $synonyms) {
            foreach ($synonyms as $syn) {
                if ($color === $syn || str_contains($color, $syn) || str_contains($syn, $color)) {
                    return $group;
                }
            }
        }
        return null;
    }

    /**
     * Score brand similarity with alias awareness.
     * Returns 0.0 to 1.0
     */
    private function scoreBrand(?string $lostBrand, ?string $foundBrand): float
    {
        if (!$lostBrand || !$foundBrand) return 0;

        $lost  = strtolower(trim($lostBrand));
        $found = strtolower(trim($foundBrand));

        if ($lost === $found) return 1.0;

        // Check aliases
        $lostCanonical  = $this->getCanonicalBrand($lost);
        $foundCanonical = $this->getCanonicalBrand($found);

        if ($lostCanonical && $foundCanonical && $lostCanonical === $foundCanonical) {
            return 0.9;
        }

        // One brand mentioned inside the other (e.g., "HP Pavilion" vs "HP")
        if (str_contains($lost, $found) || str_contains($found, $lost)) {
            return 0.8;
        }

        // Fuzzy match
        similar_text($lost, $found, $pct);
        if ($pct >= 70) return $pct / 100 * 0.7;

        return 0;
    }

    private function getCanonicalBrand(string $brand): ?string
    {
        foreach (self::$brandAliases as $canonical => $aliases) {
            foreach ($aliases as $alias) {
                if ($brand === $alias || str_contains($brand, $alias)) {
                    return $canonical;
                }
            }
        }
        return null;
    }

    /**
     * Score location similarity with campus-aware synonym matching.
     * Returns 0.0 to 1.0
     */
    private function scoreLocation(?string $lostLocation, ?string $foundLocation): float
    {
        if (!$lostLocation || !$foundLocation) return 0;

        $lost  = strtolower(trim($lostLocation));
        $found = strtolower(trim($foundLocation));

        if ($lost === $found) return 1.0;

        // Check campus location synonyms
        $lostPlace  = $this->getCanonicalLocation($lost);
        $foundPlace = $this->getCanonicalLocation($found);

        if ($lostPlace && $foundPlace && $lostPlace === $foundPlace) {
            return 0.9;
        }

        // Word-level overlap (words 3+ chars)
        $lostWords  = array_filter(preg_split('/[\s,\-\/]+/', $lost), fn($w) => strlen($w) >= 3);
        $foundWords = array_filter(preg_split('/[\s,\-\/]+/', $found), fn($w) => strlen($w) >= 3);

        if (!empty($lostWords) && !empty($foundWords)) {
            $common = count(array_intersect($lostWords, $foundWords));
            $total  = count(array_unique(array_merge($lostWords, $foundWords)));
            $overlap = $common / max(1, $total);

            if ($overlap >= 0.5) return 0.8;
            if ($overlap >= 0.25) return 0.5;
            if ($common >= 1) return 0.4;
        }

        // General text similarity
        similar_text($lost, $found, $pct);
        if ($pct >= 50) return $pct / 100 * 0.6;

        return 0;
    }

    private function getCanonicalLocation(string $location): ?string
    {
        foreach (self::$locationSynonyms as $canonical => $synonyms) {
            foreach ($synonyms as $syn) {
                if (str_contains($location, $syn)) {
                    return $canonical;
                }
            }
        }
        return null;
    }

    /**
     * Score date proximity.
     * Returns 0.0 to 1.0
     */
    private function scoreDate(?string $dateLost, ?string $dateFound): float
    {
        if (!$dateLost || !$dateFound) return 0;

        $lost  = Carbon::parse($dateLost);
        $found = Carbon::parse($dateFound);
        $days  = abs($lost->diffInDays($found));

        // Found date should ideally be on or after the lost date
        if ($days <= 1) return 1.0;
        if ($days <= 3) return 0.9;
        if ($days <= 7) return 0.7;
        if ($days <= 14) return 0.4;
        if ($days <= 30) return 0.2;

        return 0;
    }

    /**
     * Cross-field text analysis on descriptions, names, and all text fields.
     * Looks for semantic overlap, keyword matches, and identifying details.
     * Returns 0.0 to 1.0
     */
    private function scoreDescription(LostItem $lost, FoundItem $found): float
    {
        // Combine all text fields for each item
        $lostText = strtolower(implode(' ', array_filter([
            $lost->item_name,
            $lost->description,
            $lost->color,
            $lost->brand,
            $lost->location_lost,
        ])));

        $foundText = strtolower(implode(' ', array_filter([
            $found->item_name,
            $found->description,
            $found->color,
            $found->brand,
            $found->location_found,
        ])));

        $score = 0.0;
        $checks = 0;

        // 1. Check if both describe the same type of item via keywords
        $lostType  = $this->detectItemType($lostText);
        $foundType = $this->detectItemType($foundText);
        if ($lostType && $foundType && $lostType === $foundType) {
            $score += 0.3;
        }
        $checks++;

        // 2. Extract significant words (4+ chars, excluding stopwords) and measure overlap
        $lostWords  = $this->extractSignificantWords($lostText);
        $foundWords = $this->extractSignificantWords($foundText);

        if (!empty($lostWords) && !empty($foundWords)) {
            $common = count(array_intersect($lostWords, $foundWords));
            $total  = count(array_unique(array_merge($lostWords, $foundWords)));
            $wordOverlap = $common / max(1, $total);
            $score += $wordOverlap * 0.3;
        }
        $checks++;

        // 3. Check for identifying details (serial numbers, unique markings)
        $lostIdentifiers  = $this->extractIdentifiers($lostText);
        $foundIdentifiers = $this->extractIdentifiers($foundText);
        if (!empty($lostIdentifiers) && !empty($foundIdentifiers)) {
            $idMatch = count(array_intersect($lostIdentifiers, $foundIdentifiers));
            if ($idMatch > 0) {
                $score += 0.3; // Strong signal — shared serial/model numbers
            }
        }
        $checks++;

        // 4. Color mentioned in description cross-check
        $descColorScore = $this->crossCheckColorInText(
            $lost->description ?? '',
            $found->description ?? '',
            $lost->color,
            $found->color
        );
        $score += $descColorScore * 0.1;

        return min(1.0, $score);
    }

    /**
     * Score item name similarity with fuzzy matching and keyword awareness.
     * Returns 0.0 to 1.0
     */
    private function scoreItemName(?string $lostName, ?string $foundName): float
    {
        if (!$lostName || !$foundName) return 0;

        $lost  = strtolower(trim($lostName));
        $found = strtolower(trim($foundName));

        if ($lost === $found) return 1.0;

        // Check if they refer to the same item type
        $lostType  = $this->detectItemType($lost);
        $foundType = $this->detectItemType($found);
        if ($lostType && $foundType && $lostType === $foundType) {
            return 0.8;
        }

        // One contains the other
        if (str_contains($lost, $found) || str_contains($found, $lost)) {
            return 0.85;
        }

        // Word overlap
        $lostWords  = array_filter(explode(' ', $lost), fn($w) => strlen($w) >= 3);
        $foundWords = array_filter(explode(' ', $found), fn($w) => strlen($w) >= 3);
        if (!empty($lostWords) && !empty($foundWords)) {
            $common = count(array_intersect($lostWords, $foundWords));
            if ($common > 0) {
                return min(0.8, 0.4 * $common);
            }
        }

        // Fuzzy
        similar_text($lost, $found, $pct);
        if ($pct >= 60) return $pct / 100 * 0.7;

        return 0;
    }

    /**
     * Detect what type of item is described in the text.
     */
    private function detectItemType(string $text): ?string
    {
        foreach (self::$itemTypeKeywords as $type => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    return $type;
                }
            }
        }
        return null;
    }

    /**
     * Extract significant words from text (excluding common stopwords).
     */
    private function extractSignificantWords(string $text): array
    {
        $stopwords = [
            'the', 'and', 'for', 'that', 'this', 'with', 'was', 'are', 'has', 'have',
            'had', 'been', 'from', 'will', 'but', 'not', 'they', 'were', 'its', 'also',
            'can', 'did', 'just', 'very', 'about', 'some', 'any', 'all', 'each', 'every',
            'near', 'around', 'lost', 'found', 'item', 'report', 'reported', 'please',
            'help', 'think', 'think', 'maybe', 'might', 'could', 'would', 'should',
            'which', 'where', 'when', 'what', 'there', 'their', 'your', 'mine',
        ];

        $words = preg_split('/[\s,.\-\/;:!?()]+/', $text);
        $words = array_filter($words, fn($w) => strlen($w) >= 3 && !in_array($w, $stopwords));
        return array_values(array_unique($words));
    }

    /**
     * Extract potential identifiers (serial numbers, model numbers) from text.
     */
    private function extractIdentifiers(string $text): array
    {
        $identifiers = [];

        // Alphanumeric codes (serial numbers, model numbers) — e.g., "ABC123", "SN-12345"
        preg_match_all('/\b[A-Za-z]{1,4}[\-]?\d{3,}\b/', $text, $matches);
        if (!empty($matches[0])) {
            $identifiers = array_merge($identifiers, array_map('strtolower', $matches[0]));
        }

        // Pure numeric sequences of 4+ digits (could be student IDs, phone numbers)
        preg_match_all('/\b\d{4,}\b/', $text, $matches);
        if (!empty($matches[0])) {
            $identifiers = array_merge($identifiers, $matches[0]);
        }

        return array_unique($identifiers);
    }

    /**
     * Cross-check color references between descriptions.
     * E.g., lost says "dark blue" in description, found says "navy" in color field.
     * Returns 0.0 to 1.0
     */
    private function crossCheckColorInText(string $lostDesc, string $foundDesc, ?string $lostColor, ?string $foundColor): float
    {
        $allLostText  = strtolower($lostDesc . ' ' . ($lostColor ?? ''));
        $allFoundText = strtolower($foundDesc . ' ' . ($foundColor ?? ''));

        // Find all color groups mentioned in each text
        $lostGroups  = $this->findColorGroupsInText($allLostText);
        $foundGroups = $this->findColorGroupsInText($allFoundText);

        if (empty($lostGroups) || empty($foundGroups)) return 0;

        $common = array_intersect($lostGroups, $foundGroups);
        return !empty($common) ? 1.0 : 0;
    }

    private function findColorGroupsInText(string $text): array
    {
        $groups = [];
        foreach (self::$colorSynonyms as $group => $synonyms) {
            foreach ($synonyms as $syn) {
                if (str_contains($text, $syn)) {
                    $groups[] = $group;
                    break;
                }
            }
        }
        return array_unique($groups);
    }
}
