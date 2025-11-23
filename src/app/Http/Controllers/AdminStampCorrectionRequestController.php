<?php

namespace App\Http\Controllers;

use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\DB;

class AdminStampCorrectionRequestController extends Controller
{
    public function show($id)
    {
        $stampCorrectionRequest = StampCorrectionRequest::with('user')->findOrFail($id);

        return view('stamp_correction_request_approve', compact('stampCorrectionRequest'));
    }

    public function approve($id)
    {
        $requestModel = StampCorrectionRequest::findOrFail($id);

        DB::transaction(function () use ($requestModel) {
            $attendance = $requestModel->attendance;

            if ($attendance) {
                $attendance->update([
                    'work_start' => $requestModel->new_work_start ?? $attendance->work_start,
                    'work_end'   => $requestModel->new_work_end ?? $attendance->work_end,
                ]);
            }

            $requestModel->update(['status' => 'approved']);
        });

        return redirect()
            ->route('stamp_correction_request.show', $id)
            ->with('success', '承認しました。');
    }
}