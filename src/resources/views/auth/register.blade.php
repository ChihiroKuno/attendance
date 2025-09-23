@extends('layouts.app')

@section('title', '会員登録')

@section('head')
    <link rel="stylesheet" href="{{ asset('css/register.css') }}">
@endsection

@section('content')
<div class="register-container">
    <h1 class="register-title">会員登録</h1>

    <form action="{{ route('register.store') }}" method="POST" class="register-form">
        @csrf
        <div class="form-group">
            <label for="name">名前</label>
            <input type="text" name="name" id="name" value="{{ old('name') }}">
            @error('name')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="email">メールアドレス</label>
            <input type="email" name="email" id="email" value="{{ old('email') }}">
            @error('email')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password">パスワード</label>
            <input type="password" name="password" id="password">
            @error('password')
                <p class="error-message">{{ $message }}</p>
            @enderror
        </div>

        <div class="form-group">
            <label for="password_confirmation">パスワード確認</label>
            <input type="password" name="password_confirmation" id="password_confirmation">
            @error('password') {{-- ← passwordにエラーが入る --}}
                @if(str_contains($message, '一致')) {{-- メッセージ内容でフィルタリング --}}
                    <p class="error-message">{{ $message }}</p>
                @endif
            @enderror
        </div>

        <button type="submit" class="btn-submit">登録する</button>
    </form>

    <p class="login-link"><a href="{{ route('login') }}">ログインはこちら</a></p>
</div>
@endsection