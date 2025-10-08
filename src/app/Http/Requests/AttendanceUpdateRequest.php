<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class AttendanceUpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'work_start' => ['required', 'date_format:H:i'],
            'work_end'   => ['required', 'date_format:H:i', 'after:work_start'],
            'breaks.*.break_start' => ['nullable', 'date_format:H:i'],
            'breaks.*.break_end'   => ['nullable', 'date_format:H:i', 'after:breaks.*.break_start'],
            'note' => ['required', 'string', 'max:255'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $workStart = $this->input('work_start');
            $workEnd   = $this->input('work_end');

            // 出勤・退勤の整合性
            if ($workStart && $workEnd && $workStart >= $workEnd) {
                $validator->errors()->add('work_start', '出勤時間もしくは退勤時間が不適切な値です');
            }

            // 休憩時間の整合性
            if ($this->has('breaks')) {
                foreach ($this->input('breaks') as $index => $break) {
                    $start = $break['break_start'] ?? null;
                    $end   = $break['break_end'] ?? null;

                    if ($start && $end && $start >= $end) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }

                    if ($start && $workStart && $start < $workStart) {
                        $validator->errors()->add("breaks.$index.break_start", '休憩時間が不適切な値です');
                    }

                    if ($end && $workEnd && $end > $workEnd) {
                        $validator->errors()->add("breaks.$index.break_end", '休憩時間もしくは退勤時間が不適切な値です');
                    }
                }
            }
        });
    }

    public function messages(): array
    {
        return [
            'work_start.required' => '出勤時間を入力してください。',
            'work_start.date_format' => '出勤時間は「HH:MM」形式で入力してください。',
            'work_end.required' => '退勤時間を入力してください。',
            'work_end.date_format' => '退勤時間は「HH:MM」形式で入力してください。',
            'work_end.after' => '退勤時間は出勤時間より後にしてください。',
            'breaks.*.break_start.date_format' => '休憩時間は「HH:MM」形式で入力してください。',
            'breaks.*.break_end.date_format' => '休憩時間は「HH:MM」形式で入力してください。',
            'breaks.*.break_end.after' => '休憩終了は休憩開始より後にしてください。',
            'note.required' => '備考を入力してください。',
            'note.max' => '備考は255文字以内で入力してください。',
        ];
    }
}