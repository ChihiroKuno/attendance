@extends('layouts.app')

@section('content')
<div class="attendance-detail-container">
<h1>
    勤怠詳細 （{{ $attendance->work_date ? \Carbon\Carbon::parse($attendance->work_date)->format('Y/m/d') : $date }}）
</h1>

    <form method="POST" action="{{ route('attendance.update', ['date' => $attendance->work_date ? \Carbon\Carbon::parse($attendance->work_date)->toDateString() : $date]) }}">
        @csrf
        @method('PUT')

        <table class="attendance-detail-table">
            <tr>
                <th>名前</th>
                <td>{{ Auth::user()->name }}</td>
            </tr>
            <tr>
                <th>日付</th>
                <td>
                    {{ $attendance->work_date ? \Carbon\Carbon::parse($attendance->work_date)->format('Y年n月j日') : \Carbon\Carbon::parse($date)->format('Y年n月j日') }}
                </td>
            </tr>
            <tr>
                <th>出勤・退勤</th>
                <td>
                    <input type="time" name="clock_in" value="{{ old('clock_in', $attendance->clock_in) }}">
                    〜
                    <input type="time" name="clock_out" value="{{ old('clock_out', $attendance->clock_out) }}">
                    @error('clock_in')
                        <div class="error">{{ $message }}</div>
                    @enderror
                    @error('clock_out')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
            @foreach($attendance->breaks ?? [] as $i => $break)
            <tr>
                <th>休憩{{ $i+1 }}</th>
                <td>
                    <input type="time" name="breaks[{{ $i }}][start]" value="{{ old("breaks.$i.start", $break->start ?? '') }}">
                    〜
                    <input type="time" name="breaks[{{ $i }}][end]" value="{{ old("breaks.$i.end", $break->end ?? '') }}">
                </td>
            </tr>
            @endforeach
            {{-- 追加用の空フィールド --}}
            <tr>
                <th>休憩追加</th>
                <td>
                    <input type="time" name="breaks[new][start]" value="">
                    〜
                    <input type="time" name="breaks[new][end]" value="">
                </td>
            </tr>
            <tr>
                <th>備考</th>
                <td>
                    <textarea name="note">{{ old('note', $attendance->note) }}</textarea>
                    @error('note')
                        <div class="error">{{ $message }}</div>
                    @enderror
                </td>
            </tr>
        </table>

        <div class="form-actions">
            <button type="submit" class="btn-submit">修正</button>
        </div>
    </form>
</div>
@endsection