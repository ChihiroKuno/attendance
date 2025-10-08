<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>@yield('title') | COACHTECH</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}">
    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
    @yield('head')
</head>
<body>
    <header class="header">
        <div class="logo">
            <!-- <a href="{{ url('/') }}"> -->
                <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo">
            </a>
        </div>

        @auth
        <nav class="nav">
            <ul>
                <li><a href="{{ route('attendance.index') }}">勤怠</a></li>
                <li><a href="{{ route('attendance.list') }}">勤怠一覧</a></li>
                <li><a href="{{ route('request.list') }}">申請</a></li>

                <li>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="logout-btn">ログアウト</button>
                    </form>
                </li>
            </ul>
        </nav>
        @endauth
    </header>
    <main>
        @yield('content')
    </main>
</body>
</html>