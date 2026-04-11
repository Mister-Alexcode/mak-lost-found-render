<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // User notification preferences
        if (!Schema::hasColumn('users', 'notification_preferences')) {
            Schema::table('users', function (Blueprint $table) {
                $table->json('notification_preferences')->nullable()->after('reward_points');
            });
        }

        // Make claim_id nullable so messages can exist without a claim (direct/admin messaging)
        // Also add match_id for match-based conversations without a claim
        Schema::table('messages', function (Blueprint $table) {
            $table->foreignId('claim_id')->nullable()->change();
        });

        if (!Schema::hasColumn('messages', 'match_id')) {
            Schema::table('messages', function (Blueprint $table) {
                $table->foreignId('match_id')->nullable()->after('claim_id')
                      ->constrained('item_matches')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('notification_preferences');
        });
        Schema::table('messages', function (Blueprint $table) {
            $table->dropColumn('match_id');
        });
    }
};
