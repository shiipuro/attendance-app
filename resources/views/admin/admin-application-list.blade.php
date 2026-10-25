{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- 管理者の申請一覧画面専用CSSを読み込む --}}
@section('css')
@vite('resources/css/admin/admin-application-list.css')
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

        {{-- 承認待ちタブ --}}
        {{-- checkedにより最初は承認待ちを選択する --}}
        <input class="application__tab--input"
               id="tab1" type="radio" name="tab_item" checked>
        <label class="application__tab--label" for="tab1">承認待ち</label>

        {{-- 承認済みタブ --}}
        {{-- 同じnameを指定して、どちらか一方だけ選択できるようにする --}}
        <input class="application__tab--input"
               id="tab2" type="radio" name="tab_item">
        <label class="application__tab--label" for="tab2">承認済み</label>

        {{-- ========================================= --}}
        {{-- ③ 承認待ちの申請一覧 --}}
        {{-- ========================================= --}}

        {{-- 承認待ち --}}
        {{-- タブの表示切り替えはCSSで制御する --}}
        <div class="tab__content" id="content1">
            <table class="table">

                {{-- 表の見出し行 --}}
                <tr class="table__row">
                    <th class="table__header">状態</th>
                    <th class="table__header">名前</th>
                    <th class="table__header">対象日時</th>
                    <th class="table__header">申請理由</th>
                    <th class="table__header">申請日時</th>
                    <th class="table__header">詳細</th>
                </tr>

                {{-- Controllerから受け取った申請データを1件ずつ取り出す --}}
                @foreach ($applications as $application)

                    {{-- 承認状態が「承認待ち」の申請だけ表示する --}}
                    @if ($application['approval_status'] === '承認待ち')

                        <tr class="table__row">

                            {{-- 申請の承認状態を表示する --}}
                            <td class="table__description">
                                {{ $application['approval_status'] }}
                            </td>

                            {{-- 申請したユーザーの名前を表示する --}}
                            <td class="table__description">
                                {{ $application['name'] }}
                            </td>

                            {{-- 修正対象の勤怠日付を表示する --}}
                            <td class="table__description">
                                {{ $application['date'] }}
                            </td>

                            {{-- ユーザーが入力した申請理由を表示する --}}
                            <td class="table__description">
                                {{ $application['comment'] }}
                            </td>

                            {{-- 修正申請が登録された日時を表示する --}}
                            <td class="table__description">
                                {{ $application['application_date'] }}
                            </td>

                            {{-- ========================================= --}}
                            {{-- ④ 修正申請の詳細画面へ移動する --}}
                            {{-- ========================================= --}}

                            <td class="table__description">

                                {{-- 申請IDをURLに含めて詳細画面へ移動する --}}
                                {{-- AdminAttendanceControllerのapplicationShow()につながる --}}
                                <a class="table__item--detail-link"
                                   href="{{ url('/stamp_correction_request/approve/' . $application['id']) }}">
                                    詳細
                                </a>

                            </td>
                        </tr>

                    @endif
                @endforeach
            </table>
        </div>

        {{-- ========================================= --}}
        {{-- ⑤ 承認済みの申請一覧 --}}
        {{-- ========================================= --}}

        {{-- 承認済み --}}
        <div class="tab__content" id="content2">
            <table class="table">

                {{-- 表の見出し行 --}}
                <tr class="table__row">
                    <th class="table__header">状態</th>
                    <th class="table__header">名前</th>
                    <th class="table__header">対象日時</th>
                    <th class="table__header">申請理由</th>
                    <th class="table__header">申請日時</th>
                    <th class="table__header">詳細</th>
                </tr>

                {{-- Controllerから受け取った申請データを1件ずつ取り出す --}}
                @foreach ($applications as $application)

                    {{-- 承認状態が「承認済み」の申請だけ表示する --}}
                    @if ($application['approval_status'] === '承認済み')

                        <tr class="table__row">

                            {{-- 承認済みの状態を表示する --}}
                            <td class="table__description">
                                {{ $application['approval_status'] }}
                            </td>

                            {{-- 申請したユーザーの名前を表示する --}}
                            <td class="table__description">
                                {{ $application['name'] }}
                            </td>

                            {{-- 修正対象の勤怠日付を表示する --}}
                            <td class="table__description">
                                {{ $application['date'] }}
                            </td>

                            {{-- ユーザーが入力した申請理由を表示する --}}
                            <td class="table__description">
                                {{ $application['comment'] }}
                            </td>

                            {{-- 修正申請が登録された日時を表示する --}}
                            <td class="table__description">
                                {{ $application['application_date'] }}
                            </td>

                            {{-- 承認済みの申請も詳細画面で確認できる --}}
                            <td class="table__description">

                                {{-- 申請IDをURLに含めて詳細画面へ移動する --}}
                                <a class="table__item--detail-link"
                                   href="{{ url('/stamp_correction_request/approve/' . $application['id']) }}">
                                    詳細
                                </a>

                            </td>
                        </tr>

                    @endif
                @endforeach
            </table>
        </div>
    </div>
</div>
@endsection
