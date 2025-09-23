<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Attendance;
use App\Models\BreakTime;
use Carbon\Carbon;
use App\Http\Requests\AttendanceUpdateRequest;

class AttendanceController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        $today = Carbon::today();
        $attendance = Attendance::firstOrCreate(
            ['user_id' => $user->id, 'work_date' => $today],
            ['status' => '勤務外']
        );

        return view('auth.attendance', compact('attendance'));

        Carbon::setLocale('ja');
    }

    public function workStart(Request $request)
    {
        $attendance = $this->getTodayAttendance();
        if ($attendance->status === '勤務外') {
            $attendance->update([
                'work_start' => now(),
                'status' => '出勤中'
            ]);
        }
        return redirect()->route('attendance.index');
    }

    public function breakStart(Request $request)
    {
        $attendance = $this->getTodayAttendance();
        if ($attendance->status === '出勤中') {
            BreakTime::create([
                'attendance_id' => $attendance->id,
                'break_start' => now()
            ]);
            $attendance->update(['status' => '休憩中']);
        }
        return redirect()->route('attendance.index');
    }

    public function breakEnd(Request $request)
    {
        $attendance = $this->getTodayAttendance();
        if ($attendance->status === '休憩中') {
            $break = BreakTime::where('attendance_id', $attendance->id)
                ->whereNull('break_end')
                ->latest()
                ->first();
            if ($break) {
                $break->update(['break_end' => now()]);
            }
            $attendance->update(['status' => '出勤中']);
        }
        return redirect()->route('attendance.index');
    }

    public function workEnd(Request $request)
    {
        $attendance = $this->getTodayAttendance();
        if ($attendance->status === '出勤中') {
            $attendance->update([
                'work_end' => now(),
                'status' => '退勤済'
            ]);
        }
        return redirect()->route('attendance.index');
    }

    private function getTodayAttendance()
    {
        return Attendance::where('user_id', Auth::id())
            ->where('work_date', Carbon::today())
            ->first();
    }

    // 勤怠一覧
    public function list(Request $request)
    {
        $user = Auth::user();
        $month = $request->query('month', Carbon::now()->format('Y-m'));
        $currentMonth = Carbon::createFromFormat('Y-m', $month)->startOfMonth();

        $prevMonth = $currentMonth->copy()->subMonth()->format('Y-m');
        $nextMonth = $currentMonth->copy()->addMonth()->format('Y-m');

        $attendances = Attendance::where('user_id', $user->id)
            ->whereMonth('work_date', $currentMonth->month)
            ->whereYear('work_date', $currentMonth->year)
            ->orderBy('work_date', 'asc')
            ->get();

        return view('attendance-list', compact('attendances', 'currentMonth', 'prevMonth', 'nextMonth'));
    }

    // 勤怠詳細表示
    public function detail($date)
    {
        $user = Auth::user();

        $attendance = Attendance::with('breaks')
            ->where('user_id', $user->id)
            ->whereDate('work_date', $date)
            ->first();

        if (!$attendance) {
            // 新規用ダミー
            $attendance = new Attendance([
                'user_id'   => $user->id,
                'work_date' => $date,
                'status'    => '未登録',
            ]);
            $attendance->exists = false; // 新規だと明示
        }

        // Blade 側で使いやすいように Carbon に変換
        $attendance->work_date = \Carbon\Carbon::parse($attendance->work_date);

        return view('attendance-detail', compact('attendance'));
    }

    // 勤怠修正申請（新規作成にも対応）
    public function update(AttendanceUpdateRequest $request, $date)
    {
        $attendance = Attendance::firstOrNew([
            'user_id'   => Auth::id(),
            'work_date' => $date,
        ]);

        $attendance->clock_in  = $request->clock_in;
        $attendance->clock_out = $request->clock_out;
        $attendance->note      = $request->note;
        $attendance->status    = 'pending'; // 承認待ち
        $attendance->save();

        // 休憩時間の更新
        $attendance->breaks()->delete();
        if ($request->has('breaks')) {
            foreach ($request->breaks as $break) {
                if (!empty($break['start']) && !empty($break['end'])) {
                    $attendance->breaks()->create([
                        'start' => $break['start'],
                        'end'   => $break['end'],
                    ]);
                }
            }
        }

        return redirect()->route('attendance.detail', ['date' => $date])
            ->with('success', '修正申請が完了しました（承認待ち）');
    }
}