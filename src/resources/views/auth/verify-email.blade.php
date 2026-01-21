<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Attendance-system</title>
    <link rel="stylesheet" href="{{ asset('css/sanitize.css') }}" />
    <link rel="stylesheet" href="{{ asset('css/verify-email.css') }}" />
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
                <p class="main__inner-item--message">登録していただいたメールアドレスに認証メールを送付しました。</p>
                <p class="main__inner-item--message">メール認証を完了してください。</p>
            </div>
            <div class="main__inner-item">
                <a class="main__inner-item--link" href="http://localhost:8027">認証はこちらから</a>
            </div>
            <div class="main__inner-item">
                <form class="main__inner-item--form" method="POST" action="{{ route('verification.send') }}">
                @csrf
                    <button class="main__inner-item--form-button" type="submit">認証メールを再送する</button>
                </form>
            </div>
        </div>
        </div>
    </main>
</body>
</html>