<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Attendance;
use Carbon\Carbon;
use App\Models\User;
use App\Models\BreakTime;
use App\Http\Requests\AdminAttendanceUpdateRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\MessageBag;


class AdminAttendanceController extends Controller
{
    public function list(Request $request)
    {
        $date = $request->input('date')
            ? \Carbon\Carbon::parse($request->input('date'))
            : \Carbon\Carbon::today();

        // 🔹 前日・翌日の計算
        $prevDate = $date->copy()->subDay();
        $nextDate = $date->copy()->addDay();

        // 🔹 当日出勤している人だけを抽出
        // 「work_start が null でない」＝出勤打刻済み
        $attendances = \App\Models\Attendance::with('user')
            ->whereDate('work_date', $date)
            ->whereNotNull('work_start') // ✅ 出勤している人だけ
            ->get();

        return view('admin_attendance_list', [
            'attendances' => $attendances,
            'currentDate' => $date,
            'prevDate' => $prevDate->toDateString(),
            'nextDate' => $nextDate->toDateString(),
        ]);
    }

    public function show($id)
    {
        // 必要なリレーションだけ読み込む（breaks と user）
        $attendance = Attendance::with(['breaks', 'user'])->findOrFail($id);

        // Blade の $date 変数に必要
        $date = $attendance->work_date;

        // ★ GETでも $errors を必ず渡す（未定義エラーを防ぐ）
        $errors = session('errors', new MessageBag());

        return view('admin_attendance_detail', [
            'attendance' => $attendance,
            'date' => $date,
            'errors' => $errors,
        ]);
    }

    public function update(AdminAttendanceUpdateRequest $request, $id)
    {
        DB::transaction(function () use ($request, $id) {
            $attendance = Attendance::with('breaks')->findOrFail($id);

            $workDate = $attendance->work_date; // 同じ日付を利用

            // "09:00" → "2025-10-15 09:00:00" に変換
            $workStart = Carbon::parse("{$workDate} {$request->work_start}");
            $workEnd   = Carbon::parse("{$workDate} {$request->work_end}");

            $attendance->update([
                'work_start' => $workStart,
                'work_end'   => $workEnd,
                'note'       => $request->note,
            ]);

            // 既存の休憩削除 → 再登録
            $attendance->breaks()->delete();

            if ($request->breaks) {
                foreach ($request->breaks as $break) {
                    if (!empty($break['break_start']) && !empty($break['break_end'])) {
                        BreakTime::create([
                            'attendance_id' => $attendance->id,
                            'break_start' => Carbon::parse("{$workDate} {$break['break_start']}"),
                            'break_end'   => Carbon::parse("{$workDate} {$break['break_end']}"),
                        ]);
                    }
                }
            }
        });

        return redirect()
            ->route('admin.attendance.detail', $id)
            ->with('success', '勤怠情報を修正しました。');
    }

    public function staffList($id, Request $request)
    {
        $user = User::findOrFail($id);

        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        $currentMonth = $month->copy()->startOfMonth();
        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        // その月の出勤データを取得
        $attendances = Attendance::where('user_id', $id)
            ->whereMonth('work_date', $currentMonth->month)
            ->whereYear('work_date', $currentMonth->year)
            ->get()
            ->keyBy(function ($item) {
                return Carbon::parse($item->work_date)->format('Y-m-d');
            });

        // 月の全日付リストを作成
        $daysInMonth = $currentMonth->daysInMonth;
        $attendancesForMonth = [];

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonth->copy()->day($day)->format('Y-m-d');
            $attendancesForMonth[$date] = $attendances[$date] ?? null; // 勤怠がなければnull
        }

        return view('admin_attendance_staff', [
            'user' => $user,
            'attendancesForMonth' => $attendancesForMonth,
            'currentMonth' => $currentMonth,
            'prevMonth' => $prevMonth,
            'nextMonth' => $nextMonth,
        ]);
    }

    /**
     * CSV出力機能
     * 該当ユーザーの指定月の勤怠一覧をCSV形式でダウンロード
     */
    public function export($id, Request $request)
    {
        $user = User::findOrFail($id);

        // 📅 月を取得（指定がなければ今月）
        $month = $request->query('month')
            ? Carbon::parse($request->query('month'))
            : Carbon::now();

        // 📅 指定月の出勤データ取得
        $attendances = Attendance::where('user_id', $id)
            ->whereMonth('work_date', $month->month)
            ->whereYear('work_date', $month->year)
            ->with('breaks')
            ->orderBy('work_date', 'asc')
            ->get();

        // 📁 CSV用データ整形
        $csvHeader = [
            '日付',
            '出勤時刻',
            '退勤時刻',
            '休憩時間',
            '実働時間',
            '備考',
        ];

        $csvData = [];
        foreach ($attendances as $attendance) {
            $csvData[] = [
                $attendance->work_date,
                $attendance->work_start ? Carbon::parse($attendance->work_start)->format('H:i') : '',
                $attendance->work_end ? Carbon::parse($attendance->work_end)->format('H:i') : '',
                $attendance->formatted_break_time ?? '',
                $attendance->formatted_work_time ?? '',
                $attendance->note ?? '',
            ];
        }

        //  CSV内容生成
        $fileName = "{$user->name}_{$month->format('Y-m')}_attendance.csv";

        $handle = fopen('php://temp', 'r+');
        fputcsv($handle, $csvHeader);

        foreach ($csvData as $row) {
            fputcsv($handle, $row);
        }

        rewind($handle);
        $csvContent = stream_get_contents($handle);
        fclose($handle);

        //  CSVをダウンロードレスポンスとして返却
        return Response::make($csvContent, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"{$fileName}\"",
        ]);
    }
}