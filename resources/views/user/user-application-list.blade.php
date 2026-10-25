{{-- 共通レイアウトを使用する --}}
@extends('layouts.app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
@vite('resources/css/user/user-application-list.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')
<div class="application-list__content">

    {{-- ========================================= --}}
    {{-- ① ページの見出し --}}
    {{-- ========================================= --}}

    <div class="content__header">
        <h1 class="content__header--item">申請一覧</h1>
    </div>

    {{-- ========================================= --}}
    {{-- ② 承認待ち・承認済みのタブ切り替え --}}
    {{-- ========================================= --}}

    <div class="application__tab">

        {{-- 承認待ちタブ。checkedにより初期表示で選択される --}}
        <input class="application__tab--input" id="tab1" type="radio" name="tab_item" checked>
        <label class="application__tab--label" for="tab1">承認待ち</label>

        {{-- 承認済みタブ。同じnameを指定して片方だけ選択できるようにする --}}
        <input class="application__tab--input" id="tab2" type="radio" name="tab_item">
        <label class="application__tab--label" for="tab2">承認済み</label>

        {{-- ========================================= --}}
        {{-- ③ 承認待ちの申請一覧 --}}
        {{-- ========================================= --}}

        {{-- タブの表示切り替えはCSSで制御する --}}
        <div class="tab__content" id="content1">
            <table class="table">

                {{-- 表の見出し行 --}}
                <tr class="table__row">
                    <th class="table__header">
                        <p class="table__header--item">状態</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">名前</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">対象日時</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">申請理由</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">申請日時</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">詳細</p>
                    </th>
                </tr>

                {{-- Controllerから受け取った申請データを1件ずつ取り出す --}}
                @foreach ($formattedApplications as $application)

                {{-- 承認状態が「承認待ち」の申請だけ表示する --}}
                @if ($application['approval_status'] === '承認待ち')

                <tr class="table__row">

                    {{-- 申請の承認状態 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['approval_status'] }}</p>
                    </td>

                    {{-- ログイン中のユーザー名 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $user->name }}</p>
                    </td>

                    {{-- 修正対象の勤怠日付 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['date'] }}</p>
                    </td>

                    {{-- 申請理由。値がnullなら「なし」と表示する --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['comment'] ?? 'なし' }}</p>
                    </td>

                    {{-- 修正申請を登録した日時 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['application_date'] }}</p>
                    </td>

                    {{-- 申請IDをURLに付けて詳細画面へ移動する --}}
                    <td class="table__description">
                        <a class="table__item--detail-link" href="{{ url('/application/' . $application['id']) }}">詳細</a>
                    </td>
                </tr>

                @endif
                @endforeach

            </table>
        </div>

        {{-- ========================================= --}}
        {{-- ④ 承認済みの申請一覧 --}}
        {{-- ========================================= --}}

        <div class="tab__content" id="content2">
            <table class="table">

                {{-- 表の見出し行 --}}
                <tr class="table__row">
                    <th class="table__header">
                        <p class="table__header--item">状態</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">名前</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">対象日時</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">申請理由</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">申請日時</p>
                    </th>
                    <th class="table__header">
                        <p class="table__header--item">詳細</p>
                    </th>
                </tr>

                {{-- Controllerから受け取った申請データを1件ずつ取り出す --}}
                @foreach ($formattedApplications as $application)

                {{-- 承認状態が「承認済み」の申請だけ表示する --}}
                @if ($application['approval_status'] === '承認済み')

                <tr class="table__row">

                    {{-- 承認済みの状態を表示する --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['approval_status'] }}</p>
                    </td>

                    {{-- ログイン中のユーザー名 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $user->name }}</p>
                    </td>

                    {{-- 修正対象の勤怠日付 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['date'] }}</p>
                    </td>

                    {{-- 申請理由。値がnullなら「なし」と表示する --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['comment'] ?? 'なし' }}</p>
                    </td>

                    {{-- 修正申請を登録した日時 --}}
                    <td class="table__description">
                        <p class="table__description--item">{{ $application['application_date'] }}</p>
                    </td>

                    {{-- 申請IDをURLに付けて詳細画面へ移動する --}}
                    <td class="table__description">
                        <a class="table__item--detail-link" href="{{ url('/application/' . $application['id']) }}">詳細</a>
                    </td>
                </tr>

                @endif
                @endforeach

            </table>
        </div>
    </div>
</div>
@endsection
