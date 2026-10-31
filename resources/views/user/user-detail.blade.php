
{{-- 共通レイアウトを使用する --}}
@extends('layouts.app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
    @vite('resources/css/user/user-detail.css')
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
        {{-- ② 勤怠修正申請の送信先 --}}
        {{-- ========================================= --}}

        {{-- 勤怠IDをURLに含めてPOST送信する --}}
        {{-- UserAttendanceControllerのstore()で修正申請を処理する --}}
        <form class="form" action="{{ url('/attendance/detail/' . $data['id']) }}" method="post">

            {{-- CSRF対策のトークンを設定する --}}
            @csrf

            {{-- ========================================= --}}
            {{-- ③ 承認待ちの申請がない場合 --}}
            {{-- ========================================= --}}

            {{-- applicationがnullなら修正フォームを表示する --}}
            @if (is_null($data['application']))

                {{-- 承認待ちが無い場合：修正フォーム --}}
                <div class="form__content">

                    {{-- ユーザー名を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header" for="name">名前</label>
                        <div class="form__input-group">
                            <input class="form__input form__input--name" id="name" type="text" name="name" value="{{ $user->name }}"
                                readonly>
                        </div>
                    </div>

                    {{-- 勤怠の日付を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header">日付</label>
                        <div class="form__input-group">
                            {{-- 日付は表示のみ（修正不可）。名前欄と同様に枠線（border）を外す --}}
                            <input class="form__input form__input--date" type="text" value="{{ $data['year'] }}" readonly>
                            <input class="form__input form__input--date" type="text" name="new_date" value="{{ $data['date'] }}" readonly>
                        </div>
                    </div>

                    {{-- 出勤・退勤時刻の修正入力欄 --}}
                    {{-- new_clock_inとnew_clock_outをControllerに送信する --}}
                    <div class="form__group">
                        <label class="form__header" for="new_clock_in">出勤・退勤</label>
                        <div class="form__input-group">
                            <input class="form__input" id="new_clock_in" type="text" name="new_clock_in" value="{{ $data['clock_in'] }}">
                            <p>〜</p>
                            <input class="form__input" type="text" name="new_clock_out" value="{{ $data['clock_out'] }}">
                        </div>
                    </div>

                    {{-- 出勤・退勤時刻のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_clock_in') {{ $message }} @enderror
                            @error('new_clock_out') {{ $message }} @enderror
                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ④ 登録済みの休憩時間を表示・修正する --}}
                    {{-- ========================================= --}}

                    {{-- 休憩は「休憩」「休憩2」…とセクション分け表示。エラーは各行の入力欄の直下に個別表示する --}}

                    {{-- Controllerから受け取った休憩データを1件ずつ取り出す --}}
                    @foreach($data['breaks'] as $index => $break)

                        <div class="form__group">

                            {{-- 1件目は休憩、2件目以降は休憩2・休憩3と表示する --}}
                            <label class="form__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</label>

                            <div class="form__input-group">

                                {{-- 配列形式で休憩開始時刻を送信する --}}
                                {{-- 例：new_break_in[0]、new_break_in[1] --}}
                                <input class="form__input" type="text" name="new_break_in[{{ $index }}]" value="{{ $break['break_in'] }}">
                                <p>〜</p>

                                {{-- 配列形式で休憩終了時刻を送信する --}}
                                <input class="form__input" type="text" name="new_break_out[{{ $index }}]" value="{{ $break['break_out'] }}">

                            </div>
                        </div>

                        {{-- 休憩の各行に対応するバリデーションエラーを表示する --}}
                        <div class="error-message">
                            <div></div>
                            <div class="error-message__item">
                                @error('new_break_in.' . $index)<p>{{ $message }}</p>@enderror
                                @error('new_break_out.' . $index)<p>{{ $message }}</p>@enderror
                            </div>
                        </div>

                    @endforeach

                    {{-- ========================================= --}}
                    {{-- ⑤ 新しい休憩を追加するための空欄 --}}
                    {{-- ========================================= --}}

                    {{-- 新しい休憩を追加できるよう末尾に空欄を1つ用意 --}}

                    {{-- 登録済み休憩の件数を取得し、新しい入力欄の番号にする --}}
                    @php $newBreakIndex = count($data['breaks']); @endphp

                    <div class="form__group">

                        {{-- 新しく追加する休憩の見出しを表示する --}}
                        <label class="form__header">{{ $newBreakIndex === 0 ? '休憩' : '休憩' . ($newBreakIndex + 1) }}</label>

                        <div class="form__input-group">

                            {{-- 新しい休憩開始時刻の入力欄 --}}
                            <input class="form__input" type="text" name="new_break_in[{{ $newBreakIndex }}]" value="">
                            <p>〜</p>

                            {{-- 新しい休憩終了時刻の入力欄 --}}
                            <input class="form__input" type="text" name="new_break_out[{{ $newBreakIndex }}]" value="">

                        </div>
                    </div>

                    {{-- 追加した休憩入力欄のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('new_break_in.' . $newBreakIndex)<p>{{ $message }}</p>@enderror
                            @error('new_break_out.' . $newBreakIndex)<p>{{ $message }}</p>@enderror
                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ⑥ 修正理由を入力する --}}
                    {{-- ========================================= --}}

                    <div class="form__group">
                        <label class="form__header" for="comment">備考</label>
                        <div class="form__input-group">

                            {{-- 修正理由をcommentとしてControllerに送信する --}}
                            <input class="form__textarea" id="comment" name="comment" value="{{ $data['comment'] }}">

                        </div>
                    </div>

                    {{-- 修正理由のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">
                            @error('comment') {{ $message }} @enderror
                        </div>
                    </div>

                </div>

                {{-- 修正ボタンを押すとフォームの内容をPOST送信する --}}
                <div class="form__button">
                    <button class="form__button--submit" type="submit">修正</button>
                </div>

            {{-- ========================================= --}}
            {{-- ⑦ 承認待ちの申請がある場合 --}}
            {{-- ========================================= --}}

            @else

                {{-- 承認待ちあり：閲覧のみ --}}
                {{-- 修正申請中の内容を表示し、再申請できないようにする --}}
                <div class="form__content">

                    {{-- ユーザー名を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header">名前</label>
                        <div class="form__input-group">
                            <input class="form__input form__input--name readonly" type="text" value="{{ $user->name }}"
                                readonly>
                        </div>
                    </div>

                    {{-- 日付を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header">日付</label>
                        <div class="form__input-group">
                            <input class="form__input readonly" type="text" value="{{ $data['year'] }}" readonly>
                            <input class="form__input readonly" type="text" value="{{ $data['date'] }}" readonly>
                        </div>
                    </div>

                    {{-- 申請中の出勤・退勤時刻を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header">出勤・退勤</label>
                        <div class="form__input-group">
                            <input class="form__input readonly" type="text" value="{{ $data['clock_in'] }}" readonly>
                            <p>〜</p>
                            <input class="form__input readonly" type="text" value="{{ $data['clock_out'] }}" readonly>
                        </div>
                    </div>

                    {{-- 休憩は「休憩」「休憩2」「休憩3」…とセクションを分けて表示 --}}

                    {{-- 申請中の休憩データを繰り返し表示する --}}
                    @foreach($data['breaks'] as $index => $break)

                        <div class="form__group">

                            {{-- 休憩の番号を表示する --}}
                            <label class="form__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</label>

                            <div class="form__input-group">

                                {{-- readonlyにより入力内容を変更できないようにする --}}
                                <input class="form__input readonly" type="text" name="new_break_in[]"
                                    value="{{ $break['break_in'] }}" readonly>
                                <p>〜</p>
                                <input class="form__input readonly" type="text" name="new_break_out[]"
                                    value="{{ $break['break_out'] }}" readonly>

                            </div>
                        </div>

                    @endforeach

                    {{-- 申請時に入力された備考を表示する（変更不可） --}}
                    <div class="form__group">
                        <label class="form__header">備考</label>
                        <div class="form__input-group">
                            <input class="form__textarea readonly" name="comment" value="{{ $data['comment'] }}"
                                readonly></input>
                        </div>
                    </div>

                </div>

                {{-- 承認待ちの場合は修正ボタンを表示しない --}}
                <div class="form__button">
                    <p class="readonly-message">承認待ちのため修正できません</p>
                </div>

            @endif

        </form>
    </div>
@endsection
