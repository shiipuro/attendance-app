<!DOCTYPE html>
{{-- 日本語のWebページであることを指定する --}}
<html lang="ja">

<head>
    {{-- 文字コードをUTF-8に設定する --}}
    <meta charset="UTF-8">

    {{-- スマートフォンなどでも画面幅に合わせて表示する --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ブラウザのタブに表示されるタイトル --}}
    <title>勤怠管理システム</title>

    {{-- ========================================= --}}
    {{-- ① CSSファイルを読み込む --}}
    {{-- ========================================= --}}

    {{-- sanitize.css：ブラウザ標準の表示差を整える --}}
    {{-- common.css：共通のデザインを適用する --}}
    {{-- admin-login.css：管理者ログイン画面専用のデザイン --}}
    @vite(['resources/css/sanitize.css', 'resources/css/common.css', 'resources/css/admin/admin-login.css'])

    {{-- Google Fontsへの接続を事前に準備する --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Interフォントを読み込む --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    {{-- 子ビューなどで定義されたcssセクションを表示する場所 --}}
    @yield('css')
</head>

<body>

    {{-- ========================================= --}}
    {{-- ② ヘッダー・ロゴを表示する --}}
    {{-- ========================================= --}}

    <header class="header">
        <div class="header__inner">

            {{-- ロゴをクリックするとトップページへ移動する --}}
            <a class="header__logo" href="/">

                {{-- public/images/logo.svgの画像を表示する --}}
                {{-- asset()はpublicフォルダ内のファイルURLを生成する --}}
                <img class="header__logo--img" src="{{ asset('images/logo.svg') }}" alt="logo">

            </a>
        </div>
    </header>

    {{-- ========================================= --}}
    {{-- ③ 管理者ログイン画面のメイン部分 --}}
    {{-- ========================================= --}}

    <main>
        <div class="login__content">

            {{-- 画面の見出し --}}
            <div class="login__heading">
                <h1 class="login__heading--item">管理者ログイン</h1>
            </div>

            {{-- ========================================= --}}
            {{-- ④ ログイン情報をControllerに送信する --}}
            {{-- ========================================= --}}

            {{-- POST /admin/loginへメールアドレスとパスワードを送信する --}}
            {{-- AdminAuthControllerのstore()につながる --}}
            {{-- novalidateによりブラウザ標準の入力チェックを無効にする --}}
            {{-- Laravel側のバリデーション結果を表示する構成 --}}
            <form class="form" action="/admin/login" method="post" novalidate>

                {{-- CSRF対策のトークンを設定する --}}
                @csrf

                {{-- ========================================= --}}
                {{-- ⑤ メールアドレス入力欄 --}}
                {{-- ========================================= --}}

                <div class="form__group">

                    {{-- メールアドレス入力欄のラベル --}}
                    <label class="form__label" for="email">メールアドレス</label>

                    {{-- 入力されたメールアドレスをemailという名前で送信する --}}
                    {{-- old('email')はエラー後に前回の入力値を再表示する --}}
                    <input class="form__input" id="email" type="email" name="email" value="{{ old('email') }}">

                    {{-- メールアドレスのバリデーションエラーを表示する --}}
                    <div class="form__error">

                        @error ('email')
                        {{ $message }}
                        @enderror

                    </div>
                </div>

                {{-- ========================================= --}}
                {{-- ⑥ パスワード入力欄 --}}
                {{-- ========================================= --}}

                <div class="form__group">

                    {{-- パスワード入力欄のラベル --}}
                    <label class="form__label" for="password">パスワード</label>

                    {{-- type="password"で入力文字を伏せて表示する --}}
                    {{-- 入力されたパスワードをpasswordという名前で送信する --}}
                    {{-- セキュリティ上、パスワードはold()で再表示しない --}}
                    <input class="form__input" id="password" type="password" name="password">

                    {{-- パスワードのバリデーションエラーを表示する --}}
                    <div class="form__error">

                        @error ('password')
                        {{ $message }}
                        @enderror

                    </div>
                </div>

                {{-- ========================================= --}}
                {{-- ⑦ 管理者ログインボタン --}}
                {{-- ========================================= --}}

                <div class="form__button">

                    {{-- ボタンを押すとフォームの内容をPOST送信する --}}
                    <button class="form__button--submit">管理者ログインする</button>

                </div>

            </form>
        </div>
    </main>
</body>
</html>
