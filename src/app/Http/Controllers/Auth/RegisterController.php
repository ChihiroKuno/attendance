<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\RegisterRequest;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Auth\Events\Registered;

class RegisterController extends Controller
{
    public function show()
    {
        return view('auth.register');
    }

    public function store(RegisterRequest $request)
    {
        // ユーザー作成
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

       // 認証メール送信
        $user->sendEmailVerificationNotification();

        // 登録したユーザーを一時的にログインさせる
        Auth::login($user);

        // メール認証誘導画面へ遷移
        return redirect()->route('verification.notice')
            ->with('message', '登録ありがとうございます。メール認証を完了してください。');
    }
}