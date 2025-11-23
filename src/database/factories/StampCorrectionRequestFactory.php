<?php

namespace Database\Factories;

use App\Models\StampCorrectionRequest;
use App\Models\User;
use App\Models\Attendance;
use Illuminate\Database\Eloquent\Factories\Factory;

class StampCorrectionRequestFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'user_id' => User::factory(),
            'work_date' => $this->faker->date(),
            'new_work_start' => '09:00:00',
            'new_work_end' => '18:00:00',
            'reason' => 'テスト修正依頼',
            'status' => 'pending',
        ];
    }
}
