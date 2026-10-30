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
    {{-- ③ 管理者画面共通のヘッダー --}}
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
            {{-- ④ 管理者がログインしているか確認する --}}
            {{-- ========================================= --}}

            {{-- adminガードで管理者のログイン状態を確認する --}}
            {{-- ログイン済みの場合だけナビゲーションを表示する --}}
            @if(Auth::guard('admin')->check())

            {{-- ========================================= --}}
            {{-- ⑤ 管理者ログアウト用フォーム --}}
            {{-- ========================================= --}}

            {{-- ログアウトボタンを押すとPOST /admin/logoutに送信する --}}
            {{-- AdminAuthControllerのdestroy()につながる --}}
            <form action="/admin/logout" method="post">

                {{-- CSRF対策のトークンを設定する --}}
                @csrf

                {{-- ========================================= --}}
                {{-- ⑥ 管理者専用ナビゲーション --}}
                {{-- ========================================= --}}

                <nav class="inner__group">

                    {{-- 管理者用の日別勤怠一覧画面へ移動する --}}
                    <a class="inner__group--item" href="/admin/attendance/list">勤怠一覧</a>

                    {{-- スタッフ一覧画面へ移動する --}}
                    <a class="inner__group--item" href="/admin/staff/list">スタッフ一覧</a>

                    {{-- 修正申請一覧画面へ移動する --}}
                    <a class="inner__group--item" href="/stamp_correction_request/list/admin">申請一覧</a>

                    {{-- ボタンを押すとログアウト用フォームをPOST送信する --}}
                    <button class="inner__group--item logout-button">
                        ログアウト
                    </button>

                </nav>
            </form>

            {{-- 管理者が未ログインの場合はナビゲーションを表示しない --}}
            @endif

        </div>
    </header>

    {{-- ========================================= --}}
    {{-- ⑦ 各管理者画面の内容を表示する --}}
    {{-- ========================================= --}}

    <main>

        {{-- 子Bladeの@section('content')の内容をここに表示する --}}
        @yield('content')

    </main>

</body>
</html>
