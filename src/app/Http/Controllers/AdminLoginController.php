<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminLoginRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

class AdminLoginController extends Controller
{
    /**
     * ログインフォーム表示
     */
    public function showLoginForm()
    {
        return view('admin_login');
    }

    /**
     * ログイン処理
     */
    public function login(AdminLoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // Fortifyのadminガードを使用
        if (Auth::guard('admin')->attempt($credentials)) {
            $request->session()->regenerate();
            return redirect('/admin/attendance/list'); // ✅ ログイン成功後の遷移先
        }

        // 入力情報が誤っている場合
        return back()
            ->withInput()
            ->with('error', 'ログイン情報が登録されていません');
    }

    /**
     * ログアウト処理
     */
    public function logout(Request $request)
    {
        Auth::guard('admin')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/admin/login');
    }
}