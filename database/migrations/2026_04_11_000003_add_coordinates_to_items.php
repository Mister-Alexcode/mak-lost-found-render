<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('lost_items', 'latitude')) {
            Schema::table('lost_items', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location_lost');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }

        if (!Schema::hasColumn('found_items', 'latitude')) {
            Schema::table('found_items', function (Blueprint $table) {
                $table->decimal('latitude', 10, 7)->nullable()->after('location_found');
                $table->decimal('longitude', 10, 7)->nullable()->after('latitude');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lost_items', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
        Schema::table('found_items', function (Blueprint $table) {
            $table->dropColumn(['latitude', 'longitude']);
        });
    }
};
