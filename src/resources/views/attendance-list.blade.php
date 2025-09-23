@extends('layouts.app')

@section('title', '勤怠一覧')

@section('head')
<link rel="stylesheet" href="{{ asset('css/attendance-list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="attendance-list-container">
    <h1 class="attendance-list-title">勤怠一覧</h1>

    {{-- 月切替 --}}
    <div class="month-selector">
        <a href="{{ route('attendance.list', ['month' => $prevMonth]) }}" class="month-btn">
            <i class="fa-solid fa-chevron-left"></i> 前月
        </a>
        <span class="month-display">
            <i class="fa-regular fa-calendar"></i> {{ $currentMonth->format('Y/m') }}
        </span>
        <a href="{{ route('attendance.list', ['month' => $nextMonth]) }}" class="month-btn">
            翌月 <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>

    <table class="attendance-table">
        <thead>
            <tr>
                <th>日付</th>
                <th>出勤</th>
                <th>退勤</th>
                <th>休憩</th>
                <th>合計</th>
                <th>詳細</th>
            </tr>
        </thead>
        <tbody>
        @php
            $start = $currentMonth->copy()->startOfMonth();
            $end   = $currentMonth->copy()->endOfMonth();
        @endphp

        @for ($date = $start->copy(); $date->lte($end); $date->addDay())
            @php
                // $date は Carbon インスタンス
                $attendance = $attendances->firstWhere('work_date', $date->toDateString());
                $weekday = $date->locale('ja')->isoFormat('ddd'); // 月,火,水...
                // 初期表示値（空欄）
                $displayStart = '';
                $displayEnd   = '';
                $displayBreak = '';
                $displayTotal = '';
                $hasAttendance = (bool) $attendance;

                if ($hasAttendance) {
                    // 出勤・退勤
                    if (!empty($attendance->work_start)) {
                        $displayStart = \Carbon\Carbon::parse($attendance->work_start)->format('H:i');
                    }
                    if (!empty($attendance->work_end)) {
                        $displayEnd = \Carbon\Carbon::parse($attendance->work_end)->format('H:i');
                    }

                    // 休憩合計（分）
                    $breakMinutes = $attendance->breaks->sum(function($break) {
                        return $break->break_end
                            ? \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end)
                            : 0;
                    });

                    if ($breakMinutes > 0) {
                        $bh = floor($breakMinutes / 60);
                        $bm = $breakMinutes % 60;
                        $displayBreak = sprintf('%d:%02d', $bh, $bm); // 例: 1:00
                    }

                    // 合計（出勤-退勤が揃っている時のみ表示）
                    if (!empty($attendance->work_start) && !empty($attendance->work_end)) {
                        $workStart = \Carbon\Carbon::parse($attendance->work_start);
                        $workEnd   = \Carbon\Carbon::parse($attendance->work_end);
                        $totalMinutes = $workStart->diffInMinutes($workEnd);
                        $workingMinutes = $totalMinutes - $breakMinutes;
                        if ($workingMinutes > 0) {
                            $wh = floor($workingMinutes / 60);
                            $wm = $workingMinutes % 60;
                            $displayTotal = sprintf('%d:%02d', $wh, $wm); // 例: 8:00
                        }
                    }
                }
            @endphp

            <tr>
                {{-- 日付 --}}
                <td class="td-date">{{ $date->format('m/d') }}（{{ $weekday }}）</td>

                {{-- 出勤 --}}
                <td class="td-start">{{ $displayStart }}</td>

                {{-- 退勤 --}}
                <td class="td-end">{{ $displayEnd }}</td>

                {{-- 休憩 --}}
                <td class="td-break">{{ $displayBreak }}</td>

                {{-- 合計 --}}
                <td class="td-total">{{ $displayTotal }}</td>

                {{-- 詳細（常に表示） --}}
                <td class="td-detail">
                    <a href="{{ route('attendance.detail', ['date' => $date->toDateString()]) }}">詳細</a>
                </td>
            </tr>
        @endfor
        </tbody>
    </table>
</div>
@endsection