@extends('layouts.app')

@section('title', '申請一覧')

@section('head')
<link rel="stylesheet" href="{{ asset('css/stamp_correction_requestlist.css') }}">
@endsection

@section('content')
<div class="request-list-wrapper">
    <div class="request-list-card">

        <h1 class="request-list-title">申請一覧</h1>

        {{-- フィルターボタン --}}
        <div class="request-filter">
            {{-- ✅ 一般ユーザー用と管理者用でリンクを切り替え --}}
            @php
                $baseRoute = !empty($isAdmin) ? 'admin.request.list' : 'request.list';
            @endphp

            <a href="{{ route($baseRoute, ['status' => 'pending']) }}"
               class="filter-btn {{ $status === 'pending' ? 'active' : '' }}">
               承認待ち
            </a>
            <a href="{{ route($baseRoute, ['status' => 'approved']) }}"
               class="filter-btn {{ $status === 'approved' ? 'active' : '' }}">
               承認済み
            </a>
        </div>

        {{-- テーブル --}}
        <div class="request-table-wrapper">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>

                        {{-- ✅ 管理者用はユーザー名を表示 --}}
                        @if (!empty($isAdmin))
                            <th>名前</th>
                        @endif

                        <th>対象日付</th>
                        <th>申請理由</th>
                        <th>申請日時</th>
                        <th>詳細</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $req)
                        <tr>
                            <td>
                                @if($req->status === 'pending')
                                    <span class="status pending">承認待ち</span>
                                @else
                                    <span class="status approved">承認済み</span>
                                @endif
                            </td>

                            {{-- ✅ 管理者用はユーザー名を出す --}}
                            @if (!empty($isAdmin))
                                <td>{{ $req->user->name ?? '不明' }}</td>
                            @endif

                            <td>{{ \Carbon\Carbon::parse($req->work_date)->format('Y/m/d') }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>{{ \Carbon\Carbon::parse($req->created_at)->format('Y/m/d') }}</td>

                            {{-- ✅ 管理者と一般で遷移先を分ける --}}
                            <td>
                            @if (!empty($isAdmin))
                                <a href="{{ route('stamp_correction_request.show', ['id' => $req->id]) }}" class="detail-link">
                                    詳細
                                </a>
                            @else
                                <a href="{{ url('/attendance/detail/' . $req->work_date) }}" class="detail-link">
                                    詳細
                                </a>
                            @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="{{ !empty($isAdmin) ? 6 : 5 }}" class="no-data">
                                {{ $status === 'pending' ? '承認待ちの申請はありません。' : '承認済みの申請はありません。' }}
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection