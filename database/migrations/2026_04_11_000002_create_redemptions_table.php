<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('redemptions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('points_used');
            $table->string('reward_tier'); // e.g. 'certificate', 'voucher', 'trophy'
            $table->string('status')->default('pending'); // pending, approved, claimed
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('redemptions');
    }
};
