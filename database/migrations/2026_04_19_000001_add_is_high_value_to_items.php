<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasColumn('lost_items', 'is_high_value')) {
            Schema::table('lost_items', function (Blueprint $table) {
                $table->boolean('is_high_value')->default(false)->after('status');
            });
        }

        if (!Schema::hasColumn('found_items', 'is_high_value')) {
            Schema::table('found_items', function (Blueprint $table) {
                $table->boolean('is_high_value')->default(false)->after('status');
            });
        }
    }

    public function down(): void
    {
        Schema::table('lost_items', function (Blueprint $table) {
            if (Schema::hasColumn('lost_items', 'is_high_value')) {
                $table->dropColumn('is_high_value');
            }
        });
        Schema::table('found_items', function (Blueprint $table) {
            if (Schema::hasColumn('found_items', 'is_high_value')) {
                $table->dropColumn('is_high_value');
            }
        });
    }
};
