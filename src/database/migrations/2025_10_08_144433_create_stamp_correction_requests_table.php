<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('stamp_correction_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('work_date');
            $table->time('new_work_start')->nullable();
            $table->time('new_work_end')->nullable();
            $table->time('new_break_start_1')->nullable();
            $table->time('new_break_end_1')->nullable();
            $table->time('new_break_start_2')->nullable();
            $table->time('new_break_end_2')->nullable();
            $table->text('reason')->nullable();
            $table->enum('status', ['pending', 'approved'])->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('stamp_correction_requests');
    }
};