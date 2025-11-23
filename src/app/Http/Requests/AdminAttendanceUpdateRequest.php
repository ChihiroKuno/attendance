<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;
use Carbon\Carbon;

class AdminAttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        // 管理者または一般ユーザーのいずれかがログインしていればOK
        return auth('admin')->check() || auth('web')->check();
    }

    public function rules(): array
    {
        return [
            // time input対応: HH:MM 形式で受け取る
            'work_start' => ['required', 'date_format:H:i'],
            'work_end'   => ['required', 'date_format:H:i'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end'   => ['nullable', 'date_format:H:i'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $workStart = $this->input('work_start');
            $workEnd   = $this->input('work_end');
            $breaks    = $this->input('breaks', []);

            // --- 1. 出勤・退勤の整合性 ---
            if ($workStart && $workEnd) {
                try {
                    $ws = Carbon::createFromFormat('H:i', $workStart);
                    $we = Carbon::createFromFormat('H:i', $workEnd);

                    if ($ws->gte($we)) {
                        $validator->errors()->add('work_start', '出勤時間もしくは退勤時間が不適切な値です');
                    }
                } catch (\Exception $e) {
                    // date_formatエラーに任せる
                }
            }

            // --- 2 & 3. 休憩時間の整合性 ---
            foreach ($breaks as $index => $break) {
                $breakStart = $break['break_start'] ?? null;
                $breakEnd   = $break['break_end'] ?? null;

                try {
                    if ($breakStart) {
                        $bs = Carbon::createFromFormat('H:i', $breakStart);

                        if ($workStart) {
                            $ws = Carbon::createFromFormat('H:i', $workStart);
                            if ($bs->lt($ws)) {
                                // 休憩開始が出勤より前
                                $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                            }
                        }

                        if ($workEnd) {
                            $we = Carbon::createFromFormat('H:i', $workEnd);
                            if ($bs->gt($we)) {
                                // 休憩開始が退勤より後
                                $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                            }
                        }
                    }

                    if ($breakEnd) {
                        $be = Carbon::createFromFormat('H:i', $breakEnd);

                        if ($workEnd) {
                            $we = Carbon::createFromFormat('H:i', $workEnd);
                            if ($be->gt($we)) {
                                // 休憩終了が退勤より後
                                $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
                            }
                        }

                        if ($breakStart) {
                            $bs = Carbon::createFromFormat('H:i', $breakStart);
                            if ($be->lte($bs)) {
                                // 休憩終了が開始より前または同時刻
                                $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // date_formatエラーに任せる
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'work_end.after' => '出勤時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_start.after_or_equal' => '休憩時間が不適切な値です',
            'breaks.*.break_start.before' => '休憩時間が不適切な値です',
            'breaks.*.break_end.after' => '休憩時間もしくは退勤時間が不適切な値です',
            'breaks.*.break_end.before_or_equal' => '休憩時間もしくは退勤時間が不適切な値です',
            'note.required' => '備考を記入してください',

            'work_start.required' => '出勤時間を入力してください。',
            'work_end.required' => '退勤時間を入力してください。',
        ];
    }
}