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
            <a href="{{ route('request.list', ['status' => 'pending']) }}"
                class="filter-btn {{ $status === 'pending' ? 'active' : '' }}">
                承認待ち
            </a>
            <a href="{{ route('request.list', ['status' => 'approved']) }}"
                class="filter-btn {{ $status === 'approved' ? 'active' : 'disabled' }}">
                承認済み
            </a>
        </div>

        {{-- テーブル --}}
        <div class="request-table-wrapper">
            <table class="request-table">
                <thead>
                    <tr>
                        <th>状態</th>
                        <th>名前</th>
                        <th>対象日時</th>
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
                            <td>{{ $req->user->name }}</td>
                            <td>{{ $req->work_date->format('Y年n月j日') }}</td>
                            <td>{{ $req->reason }}</td>
                            <td>{{ $req->created_at->format('Y年n月j日 H:i') }}</td>
                            <td>
                                <a href="{{ url('/attendance/detail/' . $req->work_date->format('Y-m-d')) }}" class="detail-link">詳細</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" style="text-align:center; padding:20px; color:#666;">
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