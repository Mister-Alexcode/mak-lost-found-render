<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('item_matches', function (Blueprint $table) {
            $table->unique(['lost_item_id', 'found_item_id'], 'item_matches_lost_found_unique');
        });
    }

    public function down(): void
    {
        Schema::table('item_matches', function (Blueprint $table) {
            $table->dropUnique('item_matches_lost_found_unique');
        });
    }
};
