<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('lost_items', function (Blueprint $table) {
            $table->string('reward_offer')->nullable()->after('tracking_id');
        });
    }
    public function down(): void {
        Schema::table('lost_items', function (Blueprint $table) {
            $table->dropColumn('reward_offer');
        });
    }
};
