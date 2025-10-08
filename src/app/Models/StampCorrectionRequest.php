<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StampCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'attendance_id', // ← 将来的に追加するなら
        'work_date',
        'reason',
        'status',
    ];

    protected $dates = ['work_date'];

    /**
     * ユーザー情報とのリレーション
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}