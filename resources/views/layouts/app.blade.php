<!DOCTYPE html>

{{-- 日本語のWebページであることを指定する --}}
<html lang="ja">

<head>
    {{-- ========================================= --}}
    {{-- ① ページの基本設定 --}}
    {{-- ========================================= --}}

    {{-- 文字コードをUTF-8に設定する --}}
    <meta charset="UTF-8">

    {{-- スマートフォンなどの画面幅に合わせて表示する --}}
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    {{-- ブラウザのタブに表示するタイトル --}}
    <title>勤怠管理システム</title>

    {{-- ========================================= --}}
    {{-- ② 共通CSSとフォントを読み込む --}}
    {{-- ========================================= --}}

    {{-- sanitize.css：ブラウザごとの表示の違いを整える --}}
    {{-- common.css：各画面で使用する共通デザイン --}}
    @vite(['resources/css/sanitize.css', 'resources/css/common.css'])

    {{-- Google Fontsへの接続を事前に準備する --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    {{-- Interフォントを読み込む --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,100..900;1,14..32,100..900&display=swap" rel="stylesheet">

    {{-- 子Bladeの@section('css')で指定されたCSSをここに表示する --}}
    @yield('css')
</head>

<body>

    {{-- ========================================= --}}
    {{-- ③ 一般ユーザー画面共通のヘッダー --}}
    {{-- ========================================= --}}

    <header class="header">
        <div class="header__inner">

            {{-- ロゴをクリックするとトップページへ移動する --}}
            <a class="header__logo" href="/">

                {{-- public/images/logo.svgを表示する --}}
                {{-- asset()はpublicフォルダ内のファイルURLを生成する --}}
                <img class="header__logo--img" src="{{ asset('images/logo.svg') }}" alt="logo">

            </a>

            {{-- ========================================= --}}
            {{-- ④ 一般ユーザーのログイン状態を確認する --}}
            {{-- ========================================= --}}

            {{-- Auth::check()で一般ユーザーがログインしているか確認する --}}
            {{-- ログイン済みの場合だけナビゲーションを表示する --}}
            @if(Auth::check())

            {{-- ========================================= --}}
            {{-- ⑤ 一般ユーザーのログアウト用フォーム --}}
            {{-- ========================================= --}}

            {{-- ログアウトボタンを押すとPOST /logoutへ送信する --}}
            {{-- 一般ユーザーのログアウト処理につながる --}}
            <form action="/logout" method="post">

                {{-- CSRF対策のトークンを設定する --}}
                @csrf

                {{-- ========================================= --}}
                {{-- ⑥ 一般ユーザー用ナビゲーション --}}
                {{-- ========================================= --}}

                <nav class="inner__group">

                    {{-- 打刻画面へ移動する --}}
                    <a class="inner__group--item" href="/attendance">勤怠</a>

                    {{-- 自分の月次勤怠一覧画面へ移動する --}}
                    <a class="inner__group--item" href="/attendance/list">勤怠一覧</a>

                    {{-- 自分が提出した勤怠修正申請の一覧へ移動する --}}
                    <a class="inner__group--item" href="/stamp_correction_request/list">申請</a>

                    {{-- 勤怠レポート画面へのリンク --}}
                    <a class="inner__group--item" href="/attendance/report">レポート</a>

                    {{-- ボタンを押すとログアウト用フォームをPOST送信する --}}
                    <button class="inner__group--item logout-button">
                        ログアウト
                    </button>

                </nav>
            </form>

            {{-- 未ログインの場合はナビゲーションを表示しない --}}
            @endif

        </div>
    </header>

    {{-- ========================================= --}}
    {{-- ⑦ 各一般ユーザー画面の内容を表示する --}}
    {{-- ========================================= --}}

    <main>

        {{-- 子Bladeの@section('content')の内容をここに表示する --}}
        @yield('content')

    </main>

</body>
</html>
