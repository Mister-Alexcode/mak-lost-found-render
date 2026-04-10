<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('item_notifications', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('type');
            $table->text('message');
            $table->string('channel')->default('email');
            $table->boolean('is_read')->default(false);
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('item_notifications');
    }
};