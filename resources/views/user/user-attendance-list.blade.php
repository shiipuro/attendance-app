{{-- 共通レイアウトを使用する --}}
@extends('layouts.app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
@vite('resources/css/user/user-attendance-list.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')

<div class="attendance-list__content">

    {{-- ========================================= --}}
    {{-- ① ページの見出し --}}
    {{-- ========================================= --}}

    <div class="content__header">
        <h1 class="content__header--item">勤怠一覧</h1>
    </div>

    {{-- ========================================= --}}
    {{-- ② 表示する月を切り替える --}}
    {{-- ========================================= --}}

    <div class="content__menu">

        {{-- Controllerから渡された前月の年月をURLに付けて送信する --}}
        {{-- 例：?date=2026-09 --}}
        <a class="previous-month" href="?date={{ $previousMonth }}">前月</a>

        {{-- 現在表示している年月を「年/月」の形式で表示する --}}
        <p class="current-month">{{ $date->format('Y/m') }}</p>

        {{-- Controllerから渡された翌月の年月をURLに付けて送信する --}}
        <a class="next-month" href="?date={{ $nextMonth }}">翌月</a>

    </div>

    {{-- ========================================= --}}
    {{-- ③ 勤怠一覧の表を表示する --}}
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
        {{-- ④ Controllerから受け取った勤怠データを繰り返し表示 --}}
        {{-- ========================================= --}}

        {{-- 1日分の勤怠データを取り出して表の1行として表示する --}}
        @foreach($formattedAttendanceRecords as $attendanceRecord)

        <tr class="table__row">

            {{-- 勤怠の日付を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecord['date'] }}</p>
            </td>

            {{-- 出勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecord['clock_in'] }}
                </p>
            </td>

            {{-- 退勤時刻を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecord['clock_out'] }}</p>
            </td>

            {{-- 休憩時間の合計を表示する --}}
            {{-- 値がある場合はCarbonで「時:分」に整形する --}}
            {{-- 値がない場合は空欄にする --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecord['total_break_time'] ? \Carbon\Carbon::parse($attendanceRecord['total_break_time'])->format('G:i') : '' }}</p>
            </td>

            {{-- 勤務時間の合計を表示する --}}
            {{-- 値がある場合はCarbonで「時:分」に整形する --}}
            {{-- 値がない場合は空欄にする --}}
            <td class="table__description">
                <p class="table__description--item">{{ $attendanceRecord['total_time'] ? \Carbon\Carbon::parse($attendanceRecord['total_time'])->format('G:i') : '' }}</p>
            </td>

            {{-- ========================================= --}}
            {{-- ⑤ 勤怠詳細画面へのリンク --}}
            {{-- ========================================= --}}

            <td class="table__description">

                {{-- 勤怠IDが存在する日だけ詳細リンクを表示する --}}
                {{-- 打刻データがない日はリンクを表示しない --}}
                @if (!empty($attendanceRecord['id']))

                {{-- 仕様書PG05に合わせて、勤怠IDを付けて詳細画面へ移動する --}}
                <a class="table__item--detail-link" href="{{ url('/attendance/detail/' . $attendanceRecord['id']) }}">詳細</a>

                @endif
            </td>
        </tr>

        {{-- 繰り返し処理を終了する --}}
        @endforeach

    </table>
</div>

@endsection
