
{{-- 管理者専用の共通レイアウトを使用する --}}
@extends('layouts.admin-app')

{{-- この画面専用のCSSを読み込む --}}
@section('css')
    @vite('resources/css/admin/admin-detail.css')
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
        {{-- ② 勤怠修正データをControllerに送信する --}}
        {{-- ========================================= --}}

        {{-- 勤怠IDをURLに含めてPOST送信する --}}
        {{-- AdminAttendanceControllerのupdate()で修正処理を行う --}}
        <form class="form" action="{{ url('/admin/attendance/' . $attendanceRecord['id']) }}" method="post">

            {{-- CSRF対策のトークンを設定する --}}
            @csrf

                <div class="form__content">

                    {{-- ========================================= --}}
                    {{-- ③ ユーザー名を表示する --}}
                    {{-- ========================================= --}}

                    <div class="form__group">
                        <label class="form__header" for="name">名前</label>
                        <div class="form__input-group">

                            {{-- 修正対象のユーザー名を表示する --}}
                            {{-- readonlyにより名前は変更できない --}}
                            <input class="form__input form__input--name" id="name" type="text" name="name" value="{{ $user->name }}"
                                readonly>

                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ④ 勤怠の日付を表示する --}}
                    {{-- ========================================= --}}

                    <div class="form__group">
                        <label class="form__header">日付</label>
                        <div class="form__input-group">

                            {{-- 日付は表示のみ（修正不可）。名前欄と同様に枠線（border）を外す --}}
                            <input class="form__input form__input--date" type="text" value="{{ $attendanceRecord['year'] }}" readonly>

                            {{-- 日付をnew_dateとして送信するが、画面上では変更できない --}}
                            <input class="form__input form__input--date" type="text" name="new_date" value="{{ $attendanceRecord['date'] }}" readonly>

                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ⑤ 出勤・退勤時刻を修正する --}}
                    {{-- ========================================= --}}

                    <div class="form__group">
                        <label class="form__header" for="new_clock_in">出勤・退勤</label>
                        <div class="form__input-group">

                            {{-- 出勤時刻をnew_clock_inとして送信する --}}
                            <input class="form__input" id="new_clock_in" type="text" name="new_clock_in"
                                value="{{ $attendanceRecord['clock_in'] }}">

                            <p>〜</p>

                            {{-- 退勤時刻をnew_clock_outとして送信する --}}
                            <input class="form__input" type="text" name="new_clock_out"
                                value="{{ $attendanceRecord['clock_out'] }}">

                        </div>
                    </div>

                    {{-- 出勤・退勤時刻のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">

                            {{-- 出勤時刻にエラーがある場合 --}}
                            @error('new_clock_in')
                                {{ $message }}
                            @enderror

                            {{-- 退勤時刻にエラーがある場合 --}}
                            @error('new_clock_out')
                                {{ $message }}
                            @enderror

                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ⑥ 登録済みの休憩時間を表示・修正する --}}
                    {{-- ========================================= --}}

                    {{-- 休憩は「休憩」「休憩1」「休憩2」…とセクションを分けて表示 --}}

                    {{-- breaksが配列として存在すれば使用し、それ以外は空配列にする --}}
                    {{-- 休憩データがない場合でもforeachでエラーにならないようにする --}}
                    @php
                        $breaks = (isset($attendanceRecord['breaks']) && is_array($attendanceRecord['breaks']))
                            ? $attendanceRecord['breaks'] : [];
                    @endphp

                    {{-- 登録済みの休憩データを1件ずつ取り出す --}}
                    @foreach($breaks as $index => $break)

                        <div class="form__group">

                            {{-- 1件目は「休憩」、2件目以降は「休憩2」「休憩3」と表示する --}}
                            <label class="form__header">{{ $index === 0 ? '休憩' : '休憩' . ($index + 1) }}</label>

                            <div class="form__input-group">

                                {{-- 休憩開始時刻を配列形式で送信する --}}
                                {{-- 例：new_break_in[0]、new_break_in[1] --}}
                                {{-- 値がない場合は空欄を表示する --}}
                                <input class="form__input" type="text" name="new_break_in[{{ $index }}]"
                                    value="{{ $break['break_in'] ?? '' }}">

                                <p>〜</p>

                                {{-- 休憩終了時刻を配列形式で送信する --}}
                                <input class="form__input" type="text" name="new_break_out[{{ $index }}]"
                                    value="{{ $break['break_out'] ?? '' }}">

                            </div>
                        </div>

                        {{-- 各休憩行に対応するバリデーションエラーを表示する --}}
                        <div class="error-message">
                            <div></div>
                            <div class="error-message__item">

                                {{-- 配列の番号を指定して、その休憩行のエラーを表示する --}}
                                @error('new_break_in.' . $index)<p>{{ $message }}</p>@enderror
                                @error('new_break_out.' . $index)<p>{{ $message }}</p>@enderror

                            </div>
                        </div>

                    @endforeach

                    {{-- ========================================= --}}
                    {{-- ⑦ 新しい休憩を追加するための空欄 --}}
                    {{-- ========================================= --}}

                    {{-- 新しい休憩を追加できるよう末尾に空欄を1つ用意 --}}

                    {{-- 登録済み休憩の件数を、新しい入力欄の番号にする --}}
                    @php $newBreakIndex = count($breaks); @endphp

                    <div class="form__group">

                        {{-- 追加する休憩の番号を表示する --}}
                        <label class="form__header">{{ $newBreakIndex === 0 ? '休憩' : '休憩' . ($newBreakIndex + 1) }}</label>

                        <div class="form__input-group">

                            {{-- 新しい休憩開始時刻の入力欄 --}}
                            <input class="form__input" type="text" name="new_break_in[{{ $newBreakIndex }}]" value="">

                            <p>〜</p>

                            {{-- 新しい休憩終了時刻の入力欄 --}}
                            <input class="form__input" type="text" name="new_break_out[{{ $newBreakIndex }}]" value="">

                        </div>
                    </div>

                    {{-- 追加した休憩のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">

                            @error('new_break_in.' . $newBreakIndex)<p>{{ $message }}</p>@enderror
                            @error('new_break_out.' . $newBreakIndex)<p>{{ $message }}</p>@enderror

                        </div>
                    </div>

                    {{-- ========================================= --}}
                    {{-- ⑧ 備考を入力する --}}
                    {{-- ========================================= --}}

                    <div class="form__group">
                        <label class="form__header" for="comment">備考</label>
                        <div class="form__input-group">

                            {{-- 修正理由などをcommentとしてControllerに送信する --}}
                            <textarea class="form__textarea" name="comment" id="comment">{{ $attendanceRecord['comment'] }}</textarea>

                        </div>
                    </div>

                    {{-- 備考のバリデーションエラーを表示する --}}
                    <div class="error-message">
                        <div></div>
                        <div class="error-message__item">

                            @error ('comment')
                                {{ $message }}
                            @enderror

                        </div>
                    </div>

                </div>

                {{-- ========================================= --}}
                {{-- ⑨ 修正内容を送信する --}}
                {{-- ========================================= --}}

                <div class="form__button">

                    {{-- 修正ボタンを押すとフォームの内容をPOST送信する --}}
                    <button class="form__button--submit" type="submit">修正</button>

                </div>

        </form>
    </div>
@endsection
