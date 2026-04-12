# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Makerere University Lost & Found web app — final year project. Stack: Laravel 10, Blade, Tailwind CSS v3, Alpine.js, Vite, XAMPP (MySQL). Auth via Laravel Breeze.

## Common Commands

```bash
# Start dev asset pipeline
npm run dev

# Build assets for production
npm run build

# Run migrations
php artisan migrate

# Seed the admin account (admin@mak.ac.ug / Admin@1234)
php artisan db:seed --class=AdminSeeder

# Fresh migrate + seed
php artisan migrate:fresh --seed

# Clear caches after config/route changes
php artisan optimize:clear

# Run tests
php artisan test

# Run a single test file
php artisan test --filter=ExampleTest

# Code style (Laravel Pint)
./vendor/bin/pint
```

The app runs via XAMPP — start Apache and MySQL from XAMPP Control Panel, then access at `http://localhost/mak-lost-found/public`.

## Architecture Overview

### User Roles

The `users` table has a `role` column (`'user'` or `'admin'`). The `User` model exposes `isAdmin()`. Admin access is gated by `AdminMiddleware` (registered as `'admin'` alias in `Kernel.php`). Admin routes live under `/admin` prefix.

### Core Flow

1. A user reports a **LostItem** or **FoundItem**.
2. On save, `MatchingService` (`app/Services/MatchingService.php`) scores every active item of the opposite type against it. Scoring is semantic-aware (max 100): category (25), color (15, synonym-aware), brand (10, alias-aware), location (15, campus-aware), date (10, graduated), description (15, cross-field text analysis), item name (10, fuzzy + keyword). Threshold to create an `ItemMatch` is ≥55 points, capped at 99.
3. Matched items get an `ItemNotification` sent to the reporting user.
4. A user files a **Claim** against a match with verification details.
5. An admin approves or rejects the claim. Approval marks both items `returned`, awards 20 `reward_points` to the claimant, and creates a `Reward` record.
6. Users can redeem points via **Redemptions** (admin approves payout).

### Key Models & Relationships

| Model | Key relationships |
|---|---|
| `User` | hasMany LostItems, FoundItems, Claims (as claimant), ItemNotifications, Messages, Rewards, Redemptions |
| `LostItem` | belongsTo User; hasMany ItemMatches (via `lost_item_id`) |
| `FoundItem` | belongsTo User; hasMany ItemMatches (via `found_item_id`) |
| `ItemMatch` | belongsTo LostItem, FoundItem; hasOne Claim |
| `Claim` | belongsTo ItemMatch (as `match`), User (as `claimant`), User (as `admin`) |
| `ItemNotification` | belongsTo User; `is_read` bool flag |
| `Reward` | belongsTo User, Claim; records point transactions |
| `Redemption` | belongsTo User; tracks reward tier redemption requests |

### Policies

`LostItemPolicy` and `FoundItemPolicy` are registered in `AuthServiceProvider`. Both follow the same pattern: owners can view/update/delete their own items; admins can view/delete any item. Controllers call `$this->authorize(...)` — do not add middleware-level policy enforcement, keep it in the controller methods.

### Notifications

`ItemNotification` is the app's own notification model (not Laravel's built-in Notification system). Always create notifications directly via `ItemNotification::create([...])` with `channel => 'in-app'` and `is_read => false`.

### Views

Blade views use Tailwind utility classes and Alpine.js for interactivity. No separate JS framework. The layout is `resources/views/layouts/app.blade.php` (auth pages) and `navigation.blade.php` for the nav bar. Admin views are under `resources/views/admin/`.
