<?php

namespace App\Providers;

use App\Actions\Fortify\CreateNewUser;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use App\Actions\Fortify\UpdateUserProfileInformation;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Laravel\Fortify\Fortify;
use Laravel\Fortify\Contracts\LoginResponse;
use Illuminate\Support\Facades\Auth;

class FortifyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // // 新規登録時のユーザー作成処理
        // Fortify::createUsersUsing(CreateNewUser::class);

        // // 登録画面の表示
        // Fortify::registerView(function () {
        //     return view('register');
        // });

        // ログイン画面の表示
        Fortify::loginView(function () {
            return view('login');
        });

        // ログイン試行回数制限（セキュリティ）
        // RateLimiter::for('login', function (Request $request) {
        //     $email = (string) $request->email;
        //     return Limit::perMinute(10)->by($email . $request->ip());
        // });

        // ログイン後のリダイレクト先指定
        $this->app->singleton(LoginResponse::class, function () {
            return new class implements LoginResponse {
                public function toResponse($request)
                {
                    return redirect()->route('attendance.show');
                }
            };
        });

        // ログイン認証処理
        Fortify::authenticateUsing(function (Request $request) {
            $credentials = $request->only('email', 'password');

            // 管理者ログイン（/admin/* からのリクエスト時）
            if ($request->is('admin/*')) {
                if (Auth::guard('admin')->attempt($credentials)) {
                    return Auth::guard('admin')->user();
                }
                return null;
            }

            // 一般ユーザーログイン（メール認証済ユーザーのみ）
            $user = User::where('email', $request->email)->first();

            if (
                $user &&
                Hash::check($request->password, $user->password) &&
                $user->hasVerifiedEmail()
            ) {
                return $user;
            }

            return null;
        });
    }
}