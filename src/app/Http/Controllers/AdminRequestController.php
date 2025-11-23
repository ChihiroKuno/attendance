<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\StampCorrectionRequest;

class AdminRequestController extends Controller
{
    /**
     * 管理者用 申請一覧表示
     */
    public function list(Request $request)
    {
        // クエリパラメータで状態を切り替え（承認待ち／承認済み）
        $status = $request->query('status', 'pending');

        // 全ユーザー分を取得（一般ユーザーと違うポイント）
        $requests = StampCorrectionRequest::with('user')
            ->where('status', $status)
            ->orderByDesc('created_at')
            ->get();

        return view('stamp_correction_requestlist', [
            'requests' => $requests,
            'status' => $status,
            'isAdmin' => true,
        ]);
    }
}