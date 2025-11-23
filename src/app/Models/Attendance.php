<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Attendance extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'work_start',
        'work_end',
        'note',
        'status',
    ];

    /**
     * リレーション: ユーザー
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * リレーション: 休憩時間
     */
    public function breaks()
    {
        return $this->hasMany(BreakTime::class);
    }

    /**
     * アクセサ: 休憩合計時間（分）
     */
    public function getBreakMinutesAttribute()
    {
        // breaks テーブルが存在していれば集計
        if ($this->relationLoaded('breaks') || $this->breaks()->exists()) {
            $totalBreak = 0;

            foreach ($this->breaks as $break) {
                if ($break->break_start && $break->break_end) {
                    $start = Carbon::parse($break->break_start);
                    $end = Carbon::parse($break->break_end);
                    $totalBreak += $end->diffInMinutes($start);
                }
            }

            // return $totalBreak > 0 ? $totalBreak : null;
            return $totalBreak ?: null;
        }

        // 旧カラム対応
        return $this->attributes['break_time'] ?? null;
    }

    /**
     * アクセサ: 実働時間（分）
     */
    public function getTotalMinutesAttribute()
    {
        if ($this->work_start && $this->work_end) {
            $start = Carbon::parse($this->work_start);
            $end = Carbon::parse($this->work_end);
            $total = $end->diffInMinutes($start);
            $break = $this->break_minutes ?? 0;

            return max($total - $break, 0);
        }

        return null;
    }

    /**
     * hh:mm形式に変換（例：480分 → 08:00）
     */
    private function convertMinutesToTime(?int $minutes)
    {
        if ($minutes === null) {
            return '';
        }

        $hours = floor($minutes / 60);
        $mins = $minutes % 60;
        return sprintf('%02d:%02d', $hours, $mins);
    }

    /**
     * 休憩時間（hh:mm表示）
     */
    public function getFormattedBreakTimeAttribute()
    {
        return $this->convertMinutesToTime($this->break_minutes);
    }

    /**
     * アクセサ: 実働時間（h:mm 形式）
     */
    public function getFormattedWorkTimeAttribute()
    {
        return $this->convertMinutesToTime($this->total_minutes);
    }
}