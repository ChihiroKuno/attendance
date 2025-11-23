<?php

namespace Database\Factories;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class AttendanceFactory extends Factory
{
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->date(),
            'work_start' => '2025-10-16 09:00:00',
            'work_end' => '2025-10-16 18:00:00',
            'note' => 'テスト勤務',
            'status' => '勤務外',
        ];
    }
}
