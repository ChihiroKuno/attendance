@extends('layouts.app')

@section('title', 'スタッフ一覧')

@section('head')
<link rel="stylesheet" href="{{ asset('css/admin_staff_list.css') }}">
@endsection

@section('content')
<div class="staff-list-container">
    <h2 class="staff-list-title">スタッフ一覧</h2>

    <table class="staff-table">
        <thead>
            <tr>
                <th>名前</th>
                <th>メールアドレス</th>
                <th>月次勤怠</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td>{{ $user->name }}</td>
                <td>{{ $user->email }}</td>
                <td>
                <a href="{{ route('admin.attendance.staff', ['id' => $user->id]) }}" class="detail-btn">詳細</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection