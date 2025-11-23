@extends('layouts.app')

@section('title', '管理者ログイン')

@section('head')
<link rel="stylesheet" href="{{ asset('css/admin_login.css') }}">
@endsection

@section('content')
<div class="admin-login-container">
    <h1>管理者ログイン</h1>

    <form action="{{ route('admin.login.perform') }}" method="POST" class="login-form">
        @csrf

        {{-- メールアドレス --}}
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- パスワード --}}
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
                <div class="error-message">{{ $message }}</div>
            @enderror
        </div>

        {{-- ログイン情報が誤っていた場合 --}}
        @if ($errors->has('login'))
            <div class="error-message">{{ $errors->first('login') }}</div>
        @endif

        <button type="submit" class="login-btn">ログイン</button>
    </form>
</div>
@endsection