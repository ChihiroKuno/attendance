<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showLoginForm()
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');

        // if (Auth::attempt($credentials, $request->filled('remember'))) {
        //     $request->session()->regenerate();
        //     return redirect()->intended('/attendance'); // ← ここを変更
        // }

        // return back()->withErrors([
        //     'email' => 'ログイン情報が登録されていません',
        // ])->withInput();
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            $request->session()->regenerate();
    
            // ✅ メール未認証の場合はログアウトして誘導
            if (!Auth::user()->hasVerifiedEmail()) {
                Auth::logout();
                return redirect()->route('verification.notice')
                    ->with('message', 'メール認証が完了していません。メールを確認してください。');
            }
    
            return redirect()->intended('/attendance');
        }
    
        return back()->withErrors([
            'email' => 'ログイン情報が登録されていません',
        ])->withInput();
    }

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}