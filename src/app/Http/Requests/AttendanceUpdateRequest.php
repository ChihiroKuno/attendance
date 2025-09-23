<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // 自分の勤怠のみ更新可能（Controllerで制御）
    }

    public function rules(): array
    {
        return [
            'clock_in'   => ['required', 'date_format:H:i'],
            'clock_out'  => ['required', 'date_format:H:i'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end'   => ['nullable', 'date_format:H:i'],
            'note'       => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $clockIn  = $this->input('clock_in');
            $clockOut = $this->input('clock_out');

            // 出勤・退勤の整合性チェック
            if ($clockIn && $clockOut && $clockIn >= $clockOut) {
                $validator->errors()->add('clock_in', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間の整合性チェック
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $break) {
                    $start = $break['break_start'] ?? null;
                    $end   = $break['break_end'] ?? null;

                    if ($start && $clockIn && $start < $clockIn) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }
                    if ($start && $clockOut && $start > $clockOut) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }
                    if ($end && $clockOut && $end > $clockOut) {
                        $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'clock_in.required'  => '出勤時間を入力してください',
            'clock_out.required' => '退勤時間を入力してください',
            'clock_in.date_format'  => '出勤時間は「HH:MM」形式で入力してください',
            'clock_out.date_format' => '退勤時間は「HH:MM」形式で入力してください',
            'breaks.*.date_format'  => '休憩時間は「HH:MM」形式で入力してください',
            'note.required' => '備考を記入してください',
        ];
    }
}