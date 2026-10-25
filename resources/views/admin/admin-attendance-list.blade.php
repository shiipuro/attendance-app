{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- 管理者の勤怠一覧画面専用CSSを読み込む --}}
@section('css')
@vite('resources/css/admin/admin-attendance-list.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')
<div class="attendance-list__content">

    {{-- ========================================= --}}
    {{-- ① ページの見出し --}}
    {{-- ========================================= --}}

    <div class="content__header">

        {{-- Controllerから受け取った日付を「年月日」の形式で表示する --}}
        <h1 class="content__header--item">{{ $date->format('Y年m月d日') }}の勤怠</h1>

    </div>

    {{-- ========================================= --}}
    {{-- ② 前日・翌日の切り替え --}}
    {{-- ========================================= --}}

    <div class="content__menu">

        {{-- 前日の日付をクエリパラメータとして送信する --}}
        {{-- 例：?date=2026-10-21 --}}
        <a class="previous-day" href="?date={{ $previousDay }}">前日</a>

        {{-- 現在表示している日付を表示する --}}
        <p class="current-day">{{ $date->format('Y/m/d') }}</p>

        {{-- 翌日の日付をクエリパラメータとして送信する --}}
        <a class="next-day" href="?date={{ $nextDay }}">翌日</a>

    </div>

    {{-- ========================================= --}}
    {{-- ③ 全ユーザーの勤怠を表示する表 --}}
    {{-- ========================================= --}}

    <table class="table">

        {{-- 表の見出し行 --}}
        <tr class="table__row">
            <th class="table__header">
                <p class="table__header--item">名前</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">出勤</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">退勤</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">休憩</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">合計</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">詳細</p>
            </th>
        </tr>

        {{-- ========================================= --}}
        {{-- ④ Controllerから受け取った勤怠データを繰り返し表示 --}}
        {{-- ========================================= --}}

        {{-- 1人分の勤怠データを取り出して表の1行に表示する --}}
        @foreach ($attendanceRecords as $attendance)

        <tr class="table__row">

            {{-- ユーザー名を表示する --}}
            <td class="table__description">
                <p class="table__description--item">
                    {{ $attendance['name'] }}
                </p>
            </td>

            {{-- 出勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">
                    {{ $attendance['clock_in'] }}
                </p>
            </td>

            {{-- 退勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">
                    {{ $attendance['clock_out'] }}
                </p>
            </td>

            {{-- 休憩時間の合計を表示する --}}
            {{-- 合計時間の計算はController側で行っている --}}
            <td class="table__description">
                <p class="table__description--item">
                    {{ $attendance['total_break_time'] }}
                </p>
            </td>

            {{-- 勤務時間の合計を表示する --}}
            {{-- 勤務時間は出勤から退勤までの時間から休憩時間を引いたもの --}}
            <td class="table__description">
                <p class="table__description--item">
                    {{ $attendance['total_time'] }}
                </p>
            </td>

            {{-- ========================================= --}}
            {{-- ⑤ 勤怠詳細画面へのリンク --}}
            {{-- ========================================= --}}

            <td class="table__description">

                {{-- 勤怠IDがある場合だけ詳細リンクを表示する --}}
                {{-- 勤怠データが未登録のユーザーにはリンクを表示しない --}}
                @if ($attendance['id'])

                    {{-- 勤怠IDをURLに付けて管理者用の詳細画面へ移動する --}}
                    <a class="table__item--detail-link"
                       href="{{ url('/admin/attendance/' . $attendance['id']) }}">
                        詳細
                    </a>

                @endif
            </td>

        </tr>

        @endforeach

    </table>
</div>
@endsection
