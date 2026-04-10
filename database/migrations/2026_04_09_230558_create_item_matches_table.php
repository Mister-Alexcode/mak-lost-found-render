<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_matches', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lost_item_id')->constrained('lost_items')->onDelete('cascade');
            $table->foreignId('found_item_id')->constrained('found_items')->onDelete('cascade');
            $table->float('confidence_score');
            $table->string('match_status')->default('pending');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_matches');
    }
};