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
            <img src="{{ asset('img/logo.svg') }}" alt="COACHTECH Logo">
        </div>

        {{-- =====================
             管理者用ヘッダー
        ===================== --}}
        @if (Auth::guard('admin')->check())
            {{-- 管理者ログイン画面では非表示 --}}
            @if (!in_array(Route::currentRouteName(), ['admin.login.show']))
                <nav class="nav">
                    <ul>
                        <li><a href="{{ route('admin.attendance.list') }}">勤怠一覧</a></li>
                        <li><a href="{{ route('admin.staff.list') }}">スタッフ一覧</a></li>
                        <li><a href="{{ route('admin.request.list') }}">申請一覧</a></li>
                        <li>
                            <form method="POST" action="{{ route('admin.logout') }}">
                                @csrf
                                <button type="submit" class="logout-btn">ログアウト</button>
                            </form>
                        </li>
                    </ul>
                </nav>
            @endif

        {{-- =====================
             一般ユーザー用ヘッダー
        ===================== --}}
        @elseif (Auth::check())
            {{-- ログイン・登録画面ではナビ非表示 --}}
            @if (!in_array(Route::currentRouteName(), ['login', 'register']))
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
            @endif
        @endif
    </header>

    <main>
        @yield('content')
    </main>
</body>
</html>