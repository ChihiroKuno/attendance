@extends('layouts.app')

@section('content')
    <link rel="stylesheet" href="{{ asset('css/verify_email.css') }}">

    <div class="verify-wrapper">
        <p class="verify-message">
            登録していただいたメールアドレスに認証メールを送付しました。<br>
            メール認証を完了してください。
        </p>

        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button type="submit" class="verify-button">認証はこちらから</button>

            <a href="{{ route('verification.send') }}" class="resend-link"
                onclick="event.preventDefault(); this.closest('form').submit();">
                認証メールを再送する
            </a>
        </form>
    </div>
@endsection