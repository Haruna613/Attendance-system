<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance-system</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/common.css') }}" />
    @yield('css')
</head>
<body class="body">
    <header class="header">
        <div class="header__inner">
            <div class="header__inner-item">
                <img class="header__inner-item--logo" src="{{ asset('images/COACHTECH.png') }}" alt="ロゴ">
            </div>
            <div class="header__inner-item">
                <div class="header__inner-item--nav">
                    <a class="header__inner-item--nav-link" href="{{ route('attendance.register') }}">勤怠</a>
                </div>
                <div class="header__inner-item--nav">
                    <a class="header__inner-item--nav-link" href="{{ route('attendance.list') }}">勤怠一覧</a>
                </div>
                <div class="header__inner-item--nav">
                    <a class="header__inner-item--nav-link" href="{{ route('attendance.correction.list') }}">申請</a>
                </div>
                <div class="header__inner-item--nav">
                    <form class="header__inner-item--nav-form" action="/logout" method="post">
                        @csrf
                        <button class="header__inner-item--nav-form-button" type="submit">ログアウト</button>
                    </form>
                </div>
            </div>
        </div>
    </header>
    <main class="main">
        @yield('content')
    </main>
</body>
</html>