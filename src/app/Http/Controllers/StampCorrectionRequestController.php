<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;
use Illuminate\Support\Facades\Auth;

class StampCorrectionRequestController extends Controller
{
    /**
     * 一覧画面表示（承認待ち／承認済み）
     */
    public function list(Request $request)
    {
        $user = Auth::user();

        // クエリパラメータで状態切り替え（例: /stamp_correction_request/list?status=approved）
        $status = $request->query('status', 'pending');

        // 承認待ち or 承認済みを取得
        $requests = StampCorrectionRequest::with('user')
            ->where('user_id', $user->id)
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_requestlist', compact('requests', 'status'));
    }
}