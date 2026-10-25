<?php

namespace App\Http\Controllers;

// フォームから送信されたデータやセッション情報を扱う
use Illuminate\Http\Request;

// 管理者ログイン時の入力チェックを担当するFormRequest
use App\Http\Requests\AdminLoginRequest;

// ログイン認証・ログアウトを行うための機能
use Illuminate\Support\Facades\Auth;

class AdminAuthController extends Controller
{
    // =========================================
    // ① 管理者ログイン画面の表示
    // =========================================
    public function create()
    {
        // 管理者ログイン用のBladeを表示する
        return view('admin.admin-login');
    }


    // =========================================
    // ② 管理者のログイン処理
    // =========================================
    public function store(AdminLoginRequest $request)
    {
        // 入力されたメールアドレスとパスワードだけを取得する
        // 入力形式のチェックはAdminLoginRequestで行う
        $credentials = $request->only('email', 'password');

        // adminガードを使って管理者としてログイン認証する
        // attempt()は認証に成功するとtrueを返す
        if (Auth::guard('admin')->attempt($credentials)) {

            // ログイン成功時にセッションIDを再生成する
            // セッション固定攻撃を防ぐための処理
            $request->session()->regenerate();

            // 管理者の勤怠一覧画面へ移動する
            return redirect('/admin/attendance/list');
        }

        // 認証に失敗した場合はログイン画面へ戻る
        // withErrors()でエラーメッセージを渡す
        // onlyInput('email')でメールアドレスだけ入力内容を保持する
        // パスワードは保持しない
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->onlyInput('email');
    }


    // =========================================
    // ③ 管理者のログアウト処理
    // =========================================
    public function destroy(Request $request)
    {
        // adminガードからログアウトする
        Auth::guard('admin')->logout();

        // 現在のセッション情報を無効にする
        $request->session()->invalidate();

        // CSRFトークンを再生成する
        $request->session()->regenerateToken();

        // 管理者ログイン画面へ戻る
        return redirect('/admin/login');
    }
}
