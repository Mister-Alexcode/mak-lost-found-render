<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('rewards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('claim_id')->nullable()->constrained()->onDelete('set null');
            $table->string('action_type');
            $table->integer('points_awarded');
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('rewards');
    }
};