<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', function () {
    $user = auth()->user();
    $lostCount     = $user->lostItems()->count();
    $foundCount    = $user->foundItems()->count();
    $claimsCount   = $user->claims()->count();
    $matchesCount  = \App\Models\ItemMatch::where(function ($query) use ($user) {
                            $query->whereHas('lostItem', fn($q) => $q->where('user_id', $user->id))
                                  ->orWhereHas('foundItem', fn($q) => $q->where('user_id', $user->id));
                        })->count();
    $unreadNotifs  = $user->notifications()->where('is_read', false)->count();
    $pendingClaims = $user->isAdmin() ? \App\Models\Claim::where('claim_status', 'pending')->count() : 0;
    $recentMatches = \App\Models\ItemMatch::whereHas('lostItem', fn($q) => $q->where('user_id', $user->id))
                        ->with(['lostItem', 'foundItem'])
                        ->latest()->take(3)->get();

    // User's own recent items for the dashboard
    $myLostItems  = $user->lostItems()->where('status', 'active')->latest()->take(5)->get();
    $myFoundItems = $user->foundItems()->where('status', 'active')->latest()->take(5)->get();

    // Items returned to this user (lost items they reported that were matched and returned)
    $myReturnedItems = $user->lostItems()->where('status', 'returned')->latest()->take(5)->get();

    return view('dashboard', compact(
        'lostCount', 'foundCount', 'claimsCount', 'matchesCount', 'unreadNotifs', 'recentMatches', 'pendingClaims',
        'myLostItems', 'myFoundItems', 'myReturnedItems'
    ));
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';

use App\Http\Controllers\NotificationSettingsController;
use App\Http\Controllers\LostItemController;
use App\Http\Controllers\FoundItemController;
use App\Http\Controllers\ClaimController;
use App\Http\Controllers\MessageController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\LeaderboardController;
use App\Http\Controllers\RedemptionController;
use App\Http\Controllers\Admin\AdminController;

Route::resource('lost-items', LostItemController::class)->middleware('auth');
Route::resource('found-items', FoundItemController::class)->middleware('auth');

// Search & browse (public)
Route::get('/search', [SearchController::class, 'index'])->name('search.index');

// Authenticated user features
Route::middleware('auth')->group(function () {
    // Claims — specific routes MUST come before the {claim} wildcard
    Route::get('/claims', [ClaimController::class, 'index'])->name('claims.index');
    Route::get('/claims/create/{match}', [ClaimController::class, 'create'])->name('claims.create');
    Route::post('/claims', [ClaimController::class, 'store'])->name('claims.store');
    Route::post('/claims/confirm-return/{match}', [ClaimController::class, 'confirmReturn'])->name('claims.confirm-return');
    Route::get('/claims/claim-found/{foundItem}', [ClaimController::class, 'claimFoundItem'])->name('claims.claim-found');
    Route::post('/claims/claim-found/{foundItem}', [ClaimController::class, 'storeClaimFoundItem'])->name('claims.claim-found.store');
    Route::get('/claims/{claim}', [ClaimController::class, 'show'])->name('claims.show');

    // Messaging (match-based)
    Route::get('/messages', [MessageController::class, 'index'])->name('messages.index');
    Route::get('/messages/direct/{user}', [MessageController::class, 'directShow'])->name('messages.direct');
    Route::post('/messages/direct/{user}', [MessageController::class, 'directStore'])->name('messages.direct.store');
    Route::get('/messages/{match}', [MessageController::class, 'show'])->name('messages.show');
    Route::post('/messages/{match}', [MessageController::class, 'store'])->name('messages.store');

    // Notification preferences
    Route::get('/notification-settings', [NotificationSettingsController::class, 'edit'])->name('notification-settings.edit');
    Route::patch('/notification-settings', [NotificationSettingsController::class, 'update'])->name('notification-settings.update');

    // Notifications (read-all must come before the {notification} wildcard route)
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllRead'])->name('notifications.read-all');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markRead'])->name('notifications.read');
    Route::get('/notifications/{notification}/visit', [NotificationController::class, 'visit'])->name('notifications.visit');

    // Leaderboard
    Route::get('/leaderboard', [LeaderboardController::class, 'index'])->name('leaderboard.index');

    // Redemptions
    Route::get('/redemptions', [RedemptionController::class, 'index'])->name('redemptions.index');
    Route::post('/redemptions', [RedemptionController::class, 'store'])->name('redemptions.store');
    Route::get('/redemptions/{redemption}/certificate', [RedemptionController::class, 'certificate'])->name('redemptions.certificate');
});

// User search (for admin messaging)
Route::middleware(['auth', 'admin'])->get('/api/users/search', function (\Illuminate\Http\Request $request) {
    $q = $request->get('q', '');
    if (strlen($q) < 2) return response()->json([]);
    return \App\Models\User::where('role', 'user')
        ->where(function ($query) use ($q) {
            $query->where('name', 'like', "%{$q}%")
                  ->orWhere('email', 'like', "%{$q}%");
        })
        ->take(10)
        ->get(['id', 'name', 'email']);
});

// Admin routes
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
    Route::get('/lost-items', [AdminController::class, 'lostItems'])->name('lost-items');
    Route::get('/found-items', [AdminController::class, 'foundItems'])->name('found-items');
    Route::get('/claims', [AdminController::class, 'claims'])->name('claims');
    Route::post('/claims/{claim}/approve', [AdminController::class, 'approveClaim'])->name('claims.approve');
    Route::post('/claims/{claim}/reject', [AdminController::class, 'rejectClaim'])->name('claims.reject');
    Route::get('/users', [AdminController::class, 'users'])->name('users');
    Route::get('/users/{user}/edit', [AdminController::class, 'editUser'])->name('users.edit');
    Route::put('/users/{user}', [AdminController::class, 'updateUser'])->name('users.update');
    Route::post('/users/{user}/block', [AdminController::class, 'blockUser'])->name('users.block');
    Route::post('/users/{user}/unblock', [AdminController::class, 'unblockUser'])->name('users.unblock');
    Route::delete('/users/{user}', [AdminController::class, 'destroyUser'])->name('users.destroy');
    Route::post('/items/{type}/{id}/status', [AdminController::class, 'updateItemStatus'])->name('items.status');
    Route::get('/redemptions', [AdminController::class, 'redemptions'])->name('redemptions');
    Route::post('/redemptions/{redemption}/approve', [AdminController::class, 'approveRedemption'])->name('redemptions.approve');
});