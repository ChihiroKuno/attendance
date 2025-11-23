<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Attendance;
use App\Models\BreakTime; // 休憩テーブルがある場合
use Carbon\Carbon;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $users = User::all();
        $today = Carbon::today();

        //  今月と先月の2ヶ月分
        $months = [
            $today->copy()->startOfMonth(),
            $today->copy()->subMonth()->startOfMonth(),
        ];

        foreach ($users as $user) {
            foreach ($months as $monthStart) {
                $monthEnd = $monthStart->copy()->endOfMonth();

                for ($date = $monthStart->copy(); $date->lte($monthEnd); $date->addDay()) {

                    //  ランダム休日（約20〜30％）
                    if (rand(1, 10) <= 3) {
                        Attendance::create([
                            'user_id' => $user->id,
                            'work_date' => $date->toDateString(),
                            'work_start' => null,
                            'work_end' => null,
                            'status' => '勤務外',
                            'note' => '休日',
                        ]);
                        continue;
                    }

                    //  出勤時間：8:00〜10:00 のランダム
                    $workStart = $date->copy()->setTime(rand(8, 10), rand(0, 59));

                    //  勤務時間：7〜10時間（分単位ランダム）
                    $workDuration = rand(7 * 60, 10 * 60); // 分
                    $workEnd = $workStart->copy()->addMinutes($workDuration);

                    //  休憩時間（ランダム30〜90分）
                    $breakMinutes = rand(30, 90);

                    $attendance = Attendance::create([
                        'user_id' => $user->id,
                        'work_date' => $date->toDateString(),
                        'work_start' => $workStart,
                        'work_end' => $workEnd,
                        'status' => '退勤済',
                        'note' => "休憩 {$breakMinutes}分",
                    ]);

                    //  休憩テーブルを使っている場合のみ登録
                    if (class_exists('App\\Models\\BreakTime')) {
                        $breakStart = $workStart->copy()->addHours(3); // 出勤3時間後に休憩開始
                        $breakEnd = $breakStart->copy()->addMinutes($breakMinutes);

                        \App\Models\BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => $breakStart,
                            'break_end' => $breakEnd,
                        ]);
                    }
                }
            }
        }
    }
}