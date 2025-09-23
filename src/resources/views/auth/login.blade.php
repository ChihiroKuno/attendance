@extends('layouts.app')

@section('title', 'ログイン')

@section('head')
<link rel="stylesheet" href="{{ asset('css/login.css') }}">
@endsection

@section('content')
<div class="login-container">
    <h1>ログイン</h1>
    <form method="POST" action="{{ route('login.perform') }}">
        @csrf

        <!-- メールアドレス -->
        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <!-- パスワード -->
        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="btn-submit">ログインする</button>
    </form>

    <div class="register-link">
    <a href="{{ route('register.show') }}">会員登録はこちら</a>
    </div>
</div>
@endsection