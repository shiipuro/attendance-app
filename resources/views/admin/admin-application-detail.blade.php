{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
@vite('resources/css/admin/admin-application-detail.css')
@endsection

{{-- 画面のメインコンテンツ --}}
@section('content')
<div class="detail__content">

    {{-- ========================================= --}}
    {{-- ① ページの見出し --}}
    {{-- ========================================= --}}

    <div class="detail__header">
        <h1 class="content__header--item">勤怠詳細</h1>
    </div>

    {{-- ========================================= --}}
    {{-- ② 修正申請の承認処理を送信するフォーム --}}
    {{-- ========================================= --}}

    {{-- 申請IDをURLに含めてPOST送信する --}}
    {{-- AdminAttendanceControllerのapplicationApprove()で承認処理を行う --}}
    <form class="applied-form"
          action="{{ url('/stamp_correction_request/approve/' . $application->id) }}"
          method="post">

        {{-- CSRF対策のトークンを設定する --}}
        @csrf

        <div class="applied-form__content">

            {{-- ========================================= --}}
            {{-- ③ 申請したユーザーの名前を表示する --}}
            {{-- ========================================= --}}

            <div class="applied-form__group">
                <label class="applied-form__header">名前</label>
                <div class="applied-form__input-group">

                    {{-- Controllerから受け取ったユーザー名を表示する --}}
                    {{-- readonlyのため画面上では変更できない --}}
                    <input class="applied-form__input" type="text"
                           value="{{ $user->name }}" readonly>

                </div>
            </div>

            {{-- ========================================= --}}
            {{-- ④ 修正対象の勤怠日付を表示する --}}
            {{-- ========================================= --}}

            <div class="applied-form__group">
                <label class="applied-form__header">日付</label>
                <div class="applied-form__input-group">

                    {{-- 申請に紐づく勤怠データの日付から「年」を取得する --}}
                    {{-- attendanceはAttendanceモデルとのリレーション --}}
                    <input class="applied-form__input" type="text"
                           value="{{ \Carbon\Carbon::parse($application->attendance->date)->format('Y年') }}"
                           readonly>

                    {{-- 同じ日付から「月日」を取得して表示する --}}
                    <input class="applied-form__input" type="text"
                           value="{{ \Carbon\Carbon::parse($application->attendance->date)->format('n月j日') }}"
                           readonly>

                </div>
            </div>

            {{-- ========================================= --}}
            {{-- ⑤ 申請された出勤・退勤時刻を表示する --}}
            {{-- ========================================= --}}

            <div class="applied-form__group">
                <label class="applied-form__header">出勤・退勤</label>
                <div class="applied-form__input-group">

                    {{-- 修正申請に保存された新しい出勤時刻を表示する --}}
                    {{-- Carbonで「時:分」の形式に整える --}}
                    <input class="applied-form__input" type="text"
                           value="{{ \Carbon\Carbon::parse($application->new_clock_in)->format('H:i') }}"
                           readonly>

                    <p class="wavy-line">〜</p>

                    {{-- 修正申請に保存された新しい退勤時刻を表示する --}}
                    <input class="applied-form__input" type="text"
                           value="{{ \Carbon\Carbon::parse($application->new_clock_out)->format('H:i') }}"
                           readonly>

                </div>
            </div>

            {{-- ========================================= --}}
            {{-- ⑥ 申請された休憩時間を表示する --}}
            {{-- ========================================= --}}

            {{-- correctionBreaksは修正申請に紐づく休憩データ --}}
            {{-- 休憩が複数ある場合は1件ずつ表示する --}}
            {{-- 申請された休憩時間 --}}
            @foreach ($application->correctionBreaks as $index => $break)

                <div class="applied-form__group">

                    {{-- 1件目は「休憩」、2件目以降は「休憩2」「休憩3」と表示する --}}
                    <label class="applied-form__header">
                        {{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}
                    </label>

                    <div class="applied-form__input-group">

                        {{-- 申請された休憩開始時刻を表示する --}}
                        <input class="applied-form__input readonly" type="text"
                               value="{{ \Carbon\Carbon::parse($break->new_break_in)->format('H:i') }}"
                               readonly>

                        <p>〜</p>

                        {{-- 申請された休憩終了時刻を表示する --}}
                        <input class="applied-form__input readonly" type="text"
                               value="{{ \Carbon\Carbon::parse($break->new_break_out)->format('H:i') }}"
                               readonly>

                    </div>
                </div>

            @endforeach

            {{-- ========================================= --}}
            {{-- ⑦ 休憩欄の末尾に空欄を表示する --}}
            {{-- ========================================= --}}

            {{-- 空欄の休憩入力欄 --}}
            {{-- 申請された休憩件数に応じて見出しを付ける --}}
            {{-- この画面ではreadonlyなので新しい休憩は入力できない --}}
            <div class="applied-form__group">

                <label class="applied-form__header">

                    {{-- 休憩が0件なら「休憩」、1件以上なら次の番号を表示する --}}
                    {{ $application->correctionBreaks->count() === 0
                        ? '休憩'
                        : '休憩' . ($application->correctionBreaks->count() + 1) }}

                </label>

                <div class="applied-form__input-group">

                    {{-- 表示用の空欄（編集不可） --}}
                    <input class="applied-form__input readonly"
                           type="text" value="" readonly>

                    <p>〜</p>

                    <input class="applied-form__input readonly"
                           type="text" value="" readonly>

                </div>
            </div>

            {{-- ========================================= --}}
            {{-- ⑧ 申請理由を表示する --}}
            {{-- ========================================= --}}

            <div class="applied-form__group">
                <label class="applied-form__header">備考</label>
                <div class="applied-form__input-group">

                    {{-- ユーザーが入力した修正理由を表示する --}}
                    {{-- readonlyなので管理者はここでは編集しない --}}
                    <textarea class="applied-form__textarea"
                              readonly>{{ $application->comment }}</textarea>

                </div>
            </div>
        </div>

        {{-- ========================================= --}}
        {{-- ⑨ 承認状態に応じてボタン・メッセージを切り替える --}}
        {{-- ========================================= --}}

        <div class="applied-form__button">

            {{-- approval_statusが0なら承認待ち --}}
            @if ($application->approval_status == 0)

                {{-- 承認ボタンを押すとPOST送信される --}}
                <button class="applied-form__button--submit"
                        type="submit">承認</button>

            {{-- approval_statusが0以外なら承認済みとして表示する --}}
            @else

                {{-- 承認済みの場合は承認ボタンを表示しない --}}
                <p class="applied-form__item">承認済み</p>

            @endif

        </div>
    </form>
</div>
@endsection
