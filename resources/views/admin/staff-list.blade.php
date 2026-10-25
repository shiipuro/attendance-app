{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- スタッフ一覧画面専用のCSSを読み込む --}}
@section('css')
@vite('resources/css/admin/staff-list.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')
<div class="staff-list__content">

    {{-- ========================================= --}}
    {{-- ① ページの見出し --}}
    {{-- ========================================= --}}

    <div class="content__header">
        <h1 class="content__header--item">スタッフ一覧</h1>
    </div>

    {{-- ========================================= --}}
    {{-- ② スタッフ一覧の表を表示する --}}
    {{-- ========================================= --}}

    <table class="table">

        {{-- 表の見出し行 --}}
        <tr class="table__row">
            <th class="table__header">
                <p class="table__header--item">名前</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">メールアドレス</p>
            </th>
            <th class="table__header">
                <p class="table__header--item">月次勤怠</p>
            </th>
        </tr>

        {{-- ========================================= --}}
        {{-- ③ Controllerから受け取ったスタッフを繰り返し表示 --}}
        {{-- ========================================= --}}

        {{-- $usersにはControllerで取得したユーザー情報が入っている --}}
        {{-- @foreachでユーザーを1人ずつ取り出す --}}
        @foreach ($users as $user)

        <tr class="table__row">

            {{-- スタッフの名前を表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $user->name }}</p>
            </td>

            {{-- スタッフのメールアドレスを表示する --}}
            <td class="table__description">
                <p class="table__description--item">{{ $user->email }}</p>
            </td>

            {{-- ========================================= --}}
            {{-- ④ スタッフごとの月次勤怠画面へ移動する --}}
            {{-- ========================================= --}}

            <td class="table__description">

                {{-- ユーザーIDをURLに含めて詳細画面へ移動する --}}
                {{-- AdminAttendanceControllerのstaffAttendanceIndex()につながる --}}
                <a class="table__item--detail-link" href="{{ url('/admin/attendance/staff/' . $user['id']) }}">詳細</a>

            </td>
        </tr>

        {{-- すべてのスタッフを表示するまで繰り返す --}}
        @endforeach

    </table>
</div>
@endsection
