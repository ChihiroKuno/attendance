@extends('layouts.app')

@section('title', 'スタッフ別勤怠一覧（管理者）')

@section('head')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_staff.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="attendance-list-container">
    <h1 class="attendance-list-title">{{ $user->name }}さんの勤怠一覧</h1>

    {{-- 月切替 --}}
    <div class="month-selector">
        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $prevMonth]) }}" class="month-btn">
            <i class="fa-solid fa-chevron-left"></i> 前月
        </a>

        <span class="month-display">
            <i class="fa-regular fa-calendar"></i> {{ $currentMonth->format('Y/m') }}
        </span>

        <a href="{{ route('admin.attendance.staff', ['id' => $user->id, 'month' => $nextMonth]) }}" class="month-btn">
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
                $attendance = $attendancesForMonth[$date->format('Y-m-d')] ?? null;
                $weekday = $date->locale('ja')->isoFormat('ddd'); // 月,火,水...

                $displayStart = $attendance && $attendance->work_start ? \Carbon\Carbon::parse($attendance->work_start)->format('H:i') : '';
                $displayEnd   = $attendance && $attendance->work_end ? \Carbon\Carbon::parse($attendance->work_end)->format('H:i') : '';

                // 休憩合計
                $displayBreak = '';
                $breakMinutes = 0;
                if ($attendance && $attendance->breaks) {
                    $breakMinutes = $attendance->breaks->sum(function($break) {
                        return $break->break_end
                            ? \Carbon\Carbon::parse($break->break_start)->diffInMinutes($break->break_end)
                            : 0;
                    });
                    if ($breakMinutes > 0) {
                        $bh = floor($breakMinutes / 60);
                        $bm = $breakMinutes % 60;
                        $displayBreak = sprintf('%d:%02d', $bh, $bm);
                    }
                }

                // 合計（実労働時間）
                $displayTotal = '';
                if ($attendance && $attendance->work_start && $attendance->work_end) {
                    $startTime = \Carbon\Carbon::parse($attendance->work_start);
                    $endTime   = \Carbon\Carbon::parse($attendance->work_end);
                    $workMinutes = $startTime->diffInMinutes($endTime) - $breakMinutes;
                    if ($workMinutes > 0) {
                        $wh = floor($workMinutes / 60);
                        $wm = $workMinutes % 60;
                        $displayTotal = sprintf('%d:%02d', $wh, $wm);
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

                {{-- 合計（実労時間） --}}
                <td class="td-total">{{ $displayTotal }}</td>

                {{-- 詳細 --}}
                <td class="td-detail">
                    @if ($attendance)
                        <a href="{{ route('admin.attendance.detail', $attendance->id) }}">詳細</a>
                    @else
                        ―
                    @endif
                </td>
            </tr>
        @endfor
        </tbody>
    </table>

    <div class="csv-btn-area">
        <form action="{{ route('admin.attendance.export', ['id' => $user->id, 'month' => $currentMonth->format('Y-m')]) }}" method="GET">
            <button type="submit" class="csv-btn">CSV出力</button>
        </form>
    </div>
</div>
@endsection