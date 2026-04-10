<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('found_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('item_name');
            $table->string('category');
            $table->text('description');
            $table->string('color');
            $table->string('brand')->nullable();
            $table->string('location_found');
            $table->date('date_found');
            $table->string('photo')->nullable();
            $table->string('status')->default('active');
            $table->string('tracking_id')->unique();
            $table->timestamps();
        });
    }
    public function down(): void {
        Schema::dropIfExists('found_items');
    }
};