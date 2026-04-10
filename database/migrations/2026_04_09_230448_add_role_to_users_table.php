<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email');
            $table->string('phone_number')->nullable()->after('role');
            $table->string('student_id')->nullable()->after('phone_number');
            $table->integer('reward_points')->default(0)->after('student_id');
        });
    }
    public function down(): void {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role','phone_number','student_id','reward_points']);
        });
    }
};