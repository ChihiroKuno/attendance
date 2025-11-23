@extends('layouts.app')

@section('title', '勤怠一覧（管理者）')

@section('head')
<link rel="stylesheet" href="{{ asset('css/admin_attendance_list.css') }}">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endsection

@section('content')
<div class="admin-attendance-wrapper">

    {{-- 日付タイトル --}}
    <div class="attendance-header">
        <h1 class="attendance-title">
            {{ $currentDate->format('Y年n月j日') }}の勤怠
        </h1>
    </div>

    {{-- 日付ナビ（白背景・左右配置） --}}
    <div class="date-nav">
        <a href="{{ route('admin.attendance.list', ['date' => $prevDate]) }}" class="date-btn">
            <i class="fa-solid fa-chevron-left"></i> 前日
        </a>

        <div class="date-display">
            <i class="fa-regular fa-calendar"></i> {{ $currentDate->format('Y/m/d') }}
        </div>

        <a href="{{ route('admin.attendance.list', ['date' => $nextDate]) }}" class="date-btn">
            翌日 <i class="fa-solid fa-chevron-right"></i>
        </a>
    </div>

    {{-- グレーのスペース --}}
    <div class="gray-space"></div>

    {{-- 勤怠一覧テーブル --}}
    <div class="attendance-table-wrapper">
        <table class="attendance-table">
            <thead>
                <tr>
                    <th>名前</th>
                    <th>出勤</th>
                    <th>退勤</th>
                    <th>休憩</th>
                    <th>合計</th>
                    <th>詳細</th>
                </tr>
            </thead>
            <tbody>
                @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $attendance->user->name }}</td>

                        {{-- 出勤 --}}
                        <td>
                            {{ $attendance->work_start ? \Carbon\Carbon::parse($attendance->work_start)->format('H:i') : '' }}
                        </td>

                        {{-- 退勤 --}}
                        <td>
                            {{ $attendance->work_end ? \Carbon\Carbon::parse($attendance->work_end)->format('H:i') : '' }}
                        </td>

                        {{-- 休憩（hh:mm形式） --}}
                        <td>
                            {{ $attendance->formatted_break_time }}
                        </td>

                        {{-- 合計勤務時間（hh:mm形式） --}}
                        <td>
                            {{ $attendance->formatted_work_time }}
                        </td>

                        {{-- 詳細リンク --}}
                        <td>
                            <a href="{{ route('admin.attendance.detail', ['id' => $attendance->id]) }}" class="detail-link">詳細</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="no-data">この日の勤怠データはありません。</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@endsection