@extends('layouts.app')

@section('title', '勤怠登録')

@section('head')
<link rel="stylesheet" href="{{ asset('css/attendance.css') }}">
@endsection

@section('content')
<div class="attendance-container">
    <div class="status-label">{{ $attendance->status }}</div>
    <p class="date">{{ now()->isoFormat('YYYY年M月D日(ddd)') }}</p>
    <p class="time">{{ now()->format('H:i') }}</p>

    <div class="attendance-buttons">
        @if ($attendance->status === '勤務外')
            <form action="{{ route('attendance.start') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">出勤</button>
            </form>
        @elseif ($attendance->status === '出勤中')
            <form action="{{ route('attendance.end') }}" method="POST">
                @csrf
                <button type="submit" class="btn-primary">退勤</button>
            </form>
            <form action="{{ route('attendance.breakIn') }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary">休憩入</button>
            </form>
        @elseif ($attendance->status === '休憩中')
            <form action="{{ route('attendance.breakOut') }}" method="POST">
                @csrf
                <button type="submit" class="btn-secondary">休憩戻</button>
            </form>
        @elseif ($attendance->status === '退勤済')
            <p class="message">お疲れ様でした。</p>
        @endif
    </div>
</div>
@endsection