@extends('layouts.app')

@section('title', '勤怠詳細')

@section('head')
<link rel="stylesheet" href="{{ asset('css/attendance-detail.css') }}">
@endsection

@section('content')
@php
    $isPending = optional($attendance)->status === 'pending';
    $modeClass = $isPending ? 'view-mode' : 'edit-mode'; // 修正前後で切替
@endphp

<div class="attendance-detail-wrapper">
    <div class="attendance-detail-card {{ $modeClass }}">
        <h1 class="attendance-detail-title">勤怠詳細</h1>

        @php
            $workDate = optional($attendance)->work_date ?? $date;
            $carbonDate = \Carbon\Carbon::parse($workDate);
            $breaks = optional($attendance)->breaks ?? collect();
        @endphp

        <form method="POST" action="{{ route('attendance.update', ['date' => $workDate]) }}" id="attendanceForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="work_date" value="{{ $carbonDate->format('Y-m-d') }}">

            <div class="attendance-detail-content">
                <table class="attendance-detail-table">
                    <tbody>
                        <tr>
                            <th>名前</th>
                            <td>{{ Auth::user()->name }}</td>
                        </tr>

                        <tr>
                            <th>日付</th>
                            <td>
                                <span>{{ $carbonDate->format('Y年n月j日') }}</span>
                            </td>
                        </tr>

                        <tr>
                            <th>出勤・退勤</th>
                            <td class="td-time">
                                <input type="time" name="work_start"
                                    value="{{ old('work_start', optional($attendance)->work_start ? \Carbon\Carbon::parse($attendance->work_start)->format('H:i') : '') }}">
                                <span class="tilde">〜</span>
                                <input type="time" name="work_end"
                                    value="{{ old('work_end', optional($attendance)->work_end ? \Carbon\Carbon::parse($attendance->work_end)->format('H:i') : '') }}">
                                @error('work_start')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                @error('work_end')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <th>休憩</th>
                            <td class="td-time">
                                <input type="time" name="breaks[0][break_start]"
                                    value="{{ old('breaks.0.break_start', optional($breaks->get(0))->break_start ? \Carbon\Carbon::parse($breaks->get(0)->break_start)->format('H:i') : '') }}">
                                <span class="tilde">〜</span>
                                <input type="time" name="breaks[0][break_end]"
                                    value="{{ old('breaks.0.break_end', optional($breaks->get(0))->break_end ? \Carbon\Carbon::parse($breaks->get(0)->break_end)->format('H:i') : '') }}">
                                @error('breaks.0.break_start')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                @error('breaks.0.break_end')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>

                        <tr>
                            <th>休憩2</th>
                            <td class="td-time">
                                <input type="time" name="breaks[1][break_start]"
                                    value="{{ old('breaks.1.break_start', optional($breaks->get(1))->break_start ? \Carbon\Carbon::parse($breaks->get(1)->break_start)->format('H:i') : '') }}">
                                <span class="tilde">〜</span>
                                <input type="time" name="breaks[1][break_end]"
                                    value="{{ old('breaks.1.break_end', optional($breaks->get(1))->break_end ? \Carbon\Carbon::parse($breaks->get(1)->break_end)->format('H:i') : '') }}">
                                @error('breaks.1.break_start')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                                @error('breaks.1.break_end')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>

                        <tr class="no-border">
                            <th>備考</th>
                            <td>
                                <textarea name="note" rows="3">{{ old('note', optional($attendance)->note ?? '') }}</textarea>
                                @error('note')
                                    <div class="error-message">{{ $message }}</div>
                                @enderror
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                @if($isPending)
                    <p class="pending-message">※ 承認待ちのため修正はできません。</p>
                @else
                    <button type="submit" class="btn-submit">修正</button>
                @endif
            </div>
        </form>
    </div>
</div>
@endsection