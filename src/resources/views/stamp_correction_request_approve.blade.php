@extends('layouts.app')

@section('title', '修正申請承認')

@section('head')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_request_approve.css') }}">
@endsection

@section('content')
@php
    use Carbon\Carbon;

    $isApproved = optional($stampCorrectionRequest)->status === 'approved';
    $modeClass = $isApproved ? 'approved-mode' : 'pending-mode';
    $attendance = $stampCorrectionRequest->attendance ?? null;
    $breaks = $attendance && $attendance->relationLoaded('breaks') ? $attendance->breaks : collect();

    // 出勤・退勤
    $workStart = $stampCorrectionRequest->new_work_start
        ? Carbon::parse($stampCorrectionRequest->new_work_start)->format('H:i')
        : ( $attendance && $attendance->work_start
            ? Carbon::parse($attendance->work_start)->format('H:i')
            : '' );

    $workEnd = $stampCorrectionRequest->new_work_end
        ? Carbon::parse($stampCorrectionRequest->new_work_end)->format('H:i')
        : ( $attendance && $attendance->work_end
            ? Carbon::parse($attendance->work_end)->format('H:i')
            : '' );

    // 休憩1・休憩2（breaksがnullでも安全）
    $break1_start = $stampCorrectionRequest->new_break_start_1
        ? Carbon::parse($stampCorrectionRequest->new_break_start_1)->format('H:i')
        : ( isset($breaks[0]) && $breaks[0]->break_start
            ? Carbon::parse($breaks[0]->break_start)->format('H:i')
            : '' );

    $break1_end = $stampCorrectionRequest->new_break_end_1
        ? Carbon::parse($stampCorrectionRequest->new_break_end_1)->format('H:i')
        : ( isset($breaks[0]) && $breaks[0]->break_end
            ? Carbon::parse($breaks[0]->break_end)->format('H:i')
            : '' );

    $break2_start = $stampCorrectionRequest->new_break_start_2
        ? Carbon::parse($stampCorrectionRequest->new_break_start_2)->format('H:i')
        : ( isset($breaks[1]) && $breaks[1]->break_start
            ? Carbon::parse($breaks[1]->break_start)->format('H:i')
            : '' );

    $break2_end = $stampCorrectionRequest->new_break_end_2
        ? Carbon::parse($stampCorrectionRequest->new_break_end_2)->format('H:i')
        : ( isset($breaks[1]) && $breaks[1]->break_end
            ? Carbon::parse($breaks[1]->break_end)->format('H:i')
            : '' );
@endphp

<div class="approval-wrapper">
    <div class="approval-card {{ $modeClass }}">
        <h1 class="approval-title">勤怠詳細</h1>

        <div class="approval-content">
            <table class="approval-table">
                <tbody>
                    <tr>
                        <th>名前</th>
                        <td>{{ optional($stampCorrectionRequest->user)->name ?? '（不明）' }}</td>
                    </tr>

                    <tr>
                        <th>日付</th>
                        <td>{{ Carbon::parse($stampCorrectionRequest->work_date)->format('Y年n月j日') }}</td>
                    </tr>

                    {{-- 出勤・退勤 --}}
                    <tr>
                        <th>出勤・退勤</th>
                        <td class="td-time">
                            <input type="time" value="{{ $workStart }}" disabled>
                            <span class="tilde">〜</span>
                            <input type="time" value="{{ $workEnd }}" disabled>
                        </td>
                    </tr>

                    {{-- 休憩1 --}}
                    <tr>
                        <th>休憩</th>
                        <td class="td-time">
                            <input type="time" value="{{ $break1_start }}" disabled>
                            <span class="tilde">〜</span>
                            <input type="time" value="{{ $break1_end }}" disabled>
                        </td>
                    </tr>

                    {{-- 休憩2 --}}
                    <tr>
                        <th>休憩2</th>
                        <td class="td-time">
                            <input type="time" value="{{ $break2_start }}" disabled>
                            <span class="tilde">〜</span>
                            <input type="time" value="{{ $break2_end }}" disabled>
                        </td>
                    </tr>

                    {{-- 備考 --}}
                    <tr class="no-border">
                        <th>備考</th>
                        <td>
                            <textarea rows="3" disabled>{{ $stampCorrectionRequest->reason ?? '（なし）' }}</textarea>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        {{-- 承認ボタンまたはメッセージ --}}
        <div class="form-actions">
            @if(!$isApproved)
                <form method="POST" action="{{ route('stamp_correction_request.approve', $stampCorrectionRequest->id) }}">
                    @csrf
                    <button type="submit" class="btn-approve">承認</button>
                </form>
            @else
                <p class="approved-message">承認済み</p>
            @endif
        </div>
    </div>
</div>
@endsection