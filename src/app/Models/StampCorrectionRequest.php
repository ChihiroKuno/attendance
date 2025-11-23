<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'stamp_correction_requests';

    protected $fillable = [
        'user_id',
        'work_date',
        'new_work_start',
        'new_work_end',
        'new_break_start_1',
        'new_break_end_1',
        'new_break_start_2',
        'new_break_end_2',
        'reason',
        'status',
    ];

    /**
     * 申請を行ったユーザーとのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getAttendanceAttribute()
    {
        return \App\Models\Attendance::where('user_id', $this->user_id)
            ->where('work_date', $this->work_date)
            ->with('breaks') // 休憩情報も一緒に取得
            ->first();
    }
}