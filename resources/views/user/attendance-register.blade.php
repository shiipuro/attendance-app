{{-- 共通レイアウト（layouts/app.blade.php）を使用する --}}
@extends('layouts.app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
@vite('resources/css/user/attendance-register.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')

<div class="attendance__content">

    {{-- ページの見出し --}}
    {{-- Figmaに表示タイトルがないため、CSSで視覚的に非表示にしている --}}
    <h1 class="attendance__heading">勤怠登録</h1>

    {{-- ========================================= --}}
    {{-- ① 現在の勤務状態を表示する --}}
    {{-- ========================================= --}}

    <div class="attendance__status">

        {{-- Controllerから渡された勤務状態を表示する --}}
        {{-- 勤務外・出勤中・休憩中・退勤済のいずれか --}}
        <p class="attendance__status--item">{{ $user->attendance_status }}</p>

    </div>

    {{-- ========================================= --}}
    {{-- ② 打刻データをControllerに送信する --}}
    {{-- ========================================= --}}

    {{-- POST送信先は /attendance --}}
    {{-- AttendanceControllerのstore()で処理する --}}
    <form class="attendance__form" action="/attendance" method="post">

        {{-- CSRF対策のためのトークンを設定する --}}
        @csrf

        {{-- Controllerから受け取った現在の日付を表示する --}}
        <div class="current-date">
            <input class="current-date__item" type="text" value="{{ $formattedDate }}" readonly>
        </div>

        {{-- Controllerから受け取った現在時刻を表示する --}}
        {{-- JavaScriptで時刻を更新するためidを設定している --}}
        <div class="current-time">
            <input class="current-time__item" type="text" id="currentTime" value="{{ $formattedTime }}" readonly>
        </div>

        {{-- ========================================= --}}
        {{-- ③ 勤務状態に応じてボタンを切り替える --}}
        {{-- ========================================= --}}

        <div class="attendance__button">

            {{-- 勤務外の場合：出勤ボタンを表示する --}}
            @if($user->attendance_status === '勤務外')

                {{-- action=clock_in をControllerに送信する --}}
                <button class="attendance__button--submit--clock-in" type="submit" name="action" value="clock_in">出勤</button>

            {{-- 出勤中の場合：退勤・休憩入ボタンを表示する --}}
            @elseif($user->attendance_status === '出勤中')

                {{-- action=clock_out をControllerに送信する --}}
                <button class="attendance__button--submit--clock-out" type="submit" name="action" value="clock_out">退勤</button>

                {{-- action=break_in をControllerに送信する --}}
                <button class="attendance__button--submit--break-in" type="submit" name="action" value="break_in">休憩入</button>

            {{-- 休憩中の場合：休憩戻ボタンを表示する --}}
            @elseif($user->attendance_status === '休憩中')

                {{-- action=break_out をControllerに送信する --}}
                <button class="attendance__button--submit--break-out" type="submit" name="action" value="break_out">休憩戻</button>

            {{-- 退勤済の場合：メッセージを表示する --}}
            @elseif($user->attendance_status === '退勤済')

                <p class="attendance__message">お疲れ様でした。</p>

            @endif

        </div>
    </form>
</div>

{{-- ========================================= --}}
{{-- ④ JavaScriptで現在時刻を更新する --}}
{{-- ========================================= --}}

<script>
    // 現在時刻を取得して画面に表示する関数
    function updateTime() {

        // 現在の日付・時刻を取得する
        const now = new Date();

        // 時刻を24時間表記の「時:分」に設定する
        const options = { hour: '2-digit', minute: '2-digit', hour12: false };

        // id="currentTime"の入力欄に現在時刻を表示する
        document.getElementById('currentTime').value = now.toLocaleTimeString('ja-JP', options);
    }

    // 1000ミリ秒（1秒）ごとに現在時刻を更新する
    setInterval(updateTime, 1000);

    // ページを開いた直後にも現在時刻を表示する
    updateTime();
</script>

@endsection
