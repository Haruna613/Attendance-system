<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance-system</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/register.css') }}" />
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
                <h1 class="main__inner-item--title">会員登録</h1>
            </div>
            <div class="main__inner-item">
                <form class="main__inner-item--form" action="/register" method="POST">
                    @csrf
                    <div class="form__group">
                        <label class="form__group--label" for="name">名前</label>
                        <input class="form__group--input" type="text" id="name" name="name" value="{{ old('name') }}">
                    </div>
                    <div class="error-messages">
                        @error('name')
                            {{ $message }}
                        @enderror
                    </div>
                    <div class="form__group">
                        <label class="form__group--label" for="email">メールアドレス</label>
                        <input class="form__group--input" type="email" id="email" name="email" value="{{ old('email') }}">
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
                    <div class="form__group">
                        <label class="form__group--label" for="password_confirmation">パスワード確認</label>
                        <input class="form__group--input" type="password" id="password_confirmation" name="password_confirmation">
                    </div>
                    <div class="form__group">
                        <button class="form__group--button" type="submit">登録する</button>
                    </div>
                </form>
                <div class="main__inner-item--login">
                    <a class="main__inner-item--login-link" href="{{ route('login') }}">ログインはこちら</a>
                </div>
            </div>
        </div>
    </main>
</body>
</html>