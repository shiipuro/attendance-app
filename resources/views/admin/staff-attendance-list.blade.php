{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- スタッフ別の月次勤怠一覧画面専用CSSを読み込む --}}
@section('css')
@vite('resources/css/admin/staff-attendance-list.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')
<div class="attendance-list__content">

    {{-- ========================================= --}}
    {{-- ① スタッフ名を見出しに表示する --}}
    {{-- ========================================= --}}

    <div class="content__header">

        {{-- Controllerから受け取った対象スタッフの名前を表示する --}}
        <h1 class="content__header--item">{{ $user->name }}さんの勤怠</h1>

    </div>

    {{-- ========================================= --}}
    {{-- ② 表示する月を切り替える --}}
    {{-- ========================================= --}}

    <div class="content__menu">

        {{-- 前月の年月をクエリパラメータで送信する --}}
        {{-- 例：?date=2026-09 --}}
        <a class="previous-month" href="?date={{ $previousMonth }}">前月</a>

        {{-- 現在表示している年月を「年/月」で表示する --}}
        <p class="current-month">{{ $date->format('Y/m') }}</p>

        {{-- 翌月の年月をクエリパラメータで送信する --}}
        <a class="next-month" href="?date={{ $nextMonth }}">翌月</a>

    </div>

    {{-- ========================================= --}}
    {{-- ③ 対象スタッフの月次勤怠一覧を表示する --}}
    {{-- ========================================= --}}

    <table class="table">

        {{-- 表の見出し行 --}}
        <tr class="table__row">
            <th class="table__header">
                <p class="table__header--item">日付</p>
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
        {{-- ④ 勤怠データを1日ずつ繰り返し表示する --}}
        {{-- ========================================= --}}

        {{-- Controllerで整形された月次勤怠データを受け取る --}}
        {{-- @foreachで1日分ずつ取り出して表に表示する --}}
        @foreach ($formattedAttendanceRecords as $attendanceRecords)

        <tr class="table__row">

            {{-- 勤怠の日付を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecords['date'] }}</p>
            </td>

            {{-- 出勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecords['clock_in'] }}</p>
            </td>

            {{-- 退勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecords['clock_out'] }}</p>
            </td>

            {{-- 休憩時間の合計を表示する --}}
            {{-- 計算済みの値をControllerから受け取っている --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecords['total_break_time'] }}</p>
            </td>

            {{-- 勤務時間の合計を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecords['total_time'] }}</p>
            </td>

            {{-- ========================================= --}}
            {{-- ⑤ 勤怠詳細画面へのリンク --}}
            {{-- ========================================= --}}

            <td class="table__description">

                {{-- 勤怠IDが存在する場合だけ詳細リンクを表示する --}}
                {{-- 打刻データがない日にはリンクを表示しない --}}
                @if (!empty($attendanceRecords['id']))

                {{-- 勤怠IDをURLに含めて管理者用の詳細画面へ移動する --}}
                {{-- AdminAttendanceControllerのshow()につながる --}}
                <a class="table__item--detail-link" href="{{ url('/admin/attendance/' . $attendanceRecords['id']) }}">詳細</a>

                @endif
            </td>

        </tr>

        @endforeach

    </table>

    {{-- ========================================= --}}
    {{-- ⑥ CSV出力用のフォーム --}}
    {{-- ========================================= --}}

    <div class="csv-button">

        {{-- CSV出力ボタンを押すとPOSTで/exportに送信する --}}
        {{-- AdminAttendanceControllerのexportCsv()につながる --}}
        <form action="/export" method="post">

        {{-- CSRF対策のトークンを設定する --}}
        @csrf

            {{-- ========================================= --}}
            {{-- ⑦ CSV出力対象のスタッフIDを送信する --}}
            {{-- ========================================= --}}

            {{-- hiddenは画面には表示されない入力欄 --}}
            {{-- どのスタッフの勤怠を出力するかControllerに伝える --}}
            <input type="hidden" name="user_id" value="{{ $user->id }}">

            {{-- ========================================= --}}
            {{-- ⑧ CSV出力対象の年月を送信する --}}
            {{-- ========================================= --}}

            {{-- 表示中の年月を「YYYY-MM」形式で送信する --}}
            {{-- 例：2026-10 --}}
            <input type="hidden" name="year_month" value="{{ $date->format('Y-m') }}">

            {{-- ========================================= --}}
            {{-- ⑨ CSV出力ボタン --}}
            {{-- ========================================= --}}

            {{-- ボタンを押すとuser_idとyear_monthをControllerへ送信する --}}
            <input class="csv-button__submit" type="submit" value="CSV出力">

        </form>
    </div>
</div>
@endsection
