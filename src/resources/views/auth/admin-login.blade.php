<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance-system</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/login.css') }}" />
</head>
<body>
    <header class="header">
        <div class="header__inner">
            <div class="header__inner-item">
                <img class="header__inner-item--logo" src="{{ asset('images/COACHTECH.png') }}" alt="ロゴ">
            </div>
        </div>
    </header>
    <main class="main">
        <div class="main__inner">
            <div class="main__inner-item">
                <h1 class="main__inner-item--title">管理者ログイン</h1>
            </div>
            <div class="main__inner-item">
                <form class="main__inner-item--form" action="/login" method="POST">
                    @csrf
                    <div class="form__group">
                        <label class="form__group--label" for="email">メールアドレス</label>
                        <input class="form__group--input" type="text" id="email" name="email" value="{{ old('email') }}">
                    </div>
                    <div class="error-messages">
                        @error('email')
                            {{ $message }}
                        @enderror
                    </div>
                    <div class="form__group">
                        <label class="form__group--label" for="password">パスワード</label>
                        <input class="form__group--input" type="password" id="password" name="password">
                    </div>
                    <div class="error-messages">
                        @error('password')
                            {{ $message }}
                        @enderror
                    </div>
                    <input type="hidden" name="login_type" value="admin">
                    <div class="form__group">
                        <button class="form__group--button" type="submit">管理者ログインする</button>
                    </div>
                </form>
            </div>
        </div>
    </main>
</body>
</html>