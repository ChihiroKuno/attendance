<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $table = 'attendance_correction_requests';

    protected $fillable = [
        'attendance_id',
        'user_id',
        'work_date',
        'new_work_start',
        'new_work_end',
        'reason',
        'status',
    ];

    // ユーザーとのリレーション
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // 勤怠とのリレーション
    public function attendance()
    {
        return $this->belongsTo(Attendance::class);
    }
}