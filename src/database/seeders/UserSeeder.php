<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Faker\Factory as FakerFactory;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $faker = FakerFactory::create('ja_JP');

        // 10人分の一般ユーザーを作成
        foreach (range(1, 10) as $userNumber) {
            User::create([
                'name' => $faker->name(),
                'email' => "user{$userNumber}@example.com",
                'password' => bcrypt('password'),
                'role' => 'user',
                'email_verified_at' => now(),
            ]);
        }
    }
}