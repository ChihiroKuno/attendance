<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date');
            $table->timestamp('work_start')->nullable();
            $table->timestamp('work_end')->nullable();
            $table->text('note')->nullable(); // ✅ 備考追加
            $table->enum('status', ['勤務外', '出勤中', '休憩中', '退勤済', 'pending'])->default('勤務外'); // ✅ 修正申請状態
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};