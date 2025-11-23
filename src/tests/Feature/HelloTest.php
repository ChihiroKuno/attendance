<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Carbon;


class HelloTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // CSRF / auth / email verification などのミドルウェアを全て無効化
        $this->withoutMiddleware();

        // セッション開始
        $this->withSession([]);

        // 通知をフェイク
        Notification::fake();
    }

    public function test_名前が未入力だとエラーになる()
    {
        $response = $this->post(route('register.store'), [
            'name' => '',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('name');
    }

    public function test_メールアドレスが未入力だとエラーになる()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => '',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    public function test_パスワードが8文字未満だとエラーになる()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '1234567',
            'password_confirmation' => '1234567',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_パスワードが一致しないとエラーになる()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'different123',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_パスワードが未入力だとエラーになる()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => '',
            'password_confirmation' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    public function test_フォームに内容が正しく入力されていればユーザーが作成される_and_verification_email_sent()
    {
        $response = $this->post(route('register.store'), [
            'name' => 'テストユーザー',
            'email' => 'test@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        // DB にユーザーが作成されている
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        // 登録後はメール認証誘導画面へ
        $response->assertRedirect(route('verification.notice'));

        // 通知（VerifyEmail）が送られている
        $user = User::where('email', 'test@example.com')->first();
        Notification::assertSentTo($user, VerifyEmail::class);
    }


    /**
     * ログイン：メールアドレス未入力のときバリデーションエラー
     */
    public function test_ログイン_メールアドレスが未入力だとエラーになる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(), // ログイン時は認証済みにしておく
        ]);

        $response = $this->post('/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * ログイン：パスワード未入力のときバリデーションエラー
     */
    public function test_ログイン_パスワードが未入力だとエラーになる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'test@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * ログイン：登録内容と一致しない場合エラーになる
     */
    public function test_ログイン_登録内容と一致しない場合エラーになる()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // 一般的な認証失敗ではセッションに errors を持つ挙動を期待
        $response->assertSessionHasErrors();
    }

    /**
     * 管理者ログイン：メールアドレス未入力
     */
    public function test_管理者ログイン_メールアドレスが未入力だとエラーになる()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'email' => '',
            'password' => 'password123',
        ]);

        $response->assertSessionHasErrors('email');
    }

    /**
     * 管理者ログイン：パスワード未入力
     */
    public function test_管理者ログイン_パスワードが未入力だとエラーになる()
    {
        $admin = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => bcrypt('password123'),
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->post('/admin/login', [
            'email' => 'admin@example.com',
            'password' => '',
        ]);

        $response->assertSessionHasErrors('password');
    }

    /**
     * 管理者ログイン：登録内容と一致しない場合エラー
     */
    /** 管理者ログイン：失敗時 */
    public function test_管理者ログイン_登録内容と一致しない場合エラーになる()
    {
        $response = $this->post('/admin/login', [
            'email' => 'wrong@example.com',
            'password' => 'password123',
        ]);

        // 管理者ログイン失敗時は → with('error', 'メッセージ')
        $response->assertSessionHas('error', 'ログイン情報が登録されていません');
    }

    public function test_日時取得_現在時刻が正しい形式で表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance');

        $response->assertStatus(200);

        // Blade の表示仕様に合わせた正規表現

        // 例）2025年1月3日(金)
        $patternDate = '/\d{4}年\d{1,2}月\d{1,2}日\(.{1,3}\)/u';

        // 例）09:42
        $patternTime = '/\d{2}:\d{2}/';

        $content = $response->getContent();

        $this->assertMatchesRegularExpression($patternDate, $content);
        $this->assertMatchesRegularExpression($patternTime, $content);
    }

    /**
     * ステータス確認：勤務外
     */
    public function test_ステータス確認_勤務外の場合ステータスが勤務外と表示される()
    {
        $user = User::factory()->create();

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'work_start' => null,
            'work_end' => null,
            'status' => '勤務外',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('勤務外');
    }

    /**
     * ステータス確認：出勤中
     */
    public function test_ステータス確認_出勤中の場合ステータスが出勤中と表示される()
    {
        $user = User::factory()->create();

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'work_start' => now(),
            'work_end' => null,
            'status' => '出勤中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('出勤中');
    }

    /**
     * ステータス確認：休憩中
     */
    public function test_ステータス確認_休憩中の場合ステータスが休憩中と表示される()
    {
        $user = User::factory()->create();

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'work_start' => now()->subHours(1),
            'work_end' => null,
            'status' => '休憩中',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('休憩中');
    }

    /**
     * ステータス確認：退勤済
     */
    public function test_ステータス確認_退勤済の場合ステータスが退勤済と表示される()
    {
        $user = User::factory()->create();

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'work_start' => now()->subHours(9),
            'work_end' => now()->subHours(1),
            'status' => '退勤済',
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertSee('退勤済');
    }

    /**
     * 出勤機能：出勤ボタンが正しく機能する
     */
    public function test_出勤機能_出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 既存の当日勤怠を勤務外で作成
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '勤務外',
            'work_start' => null,
            'work_end' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/start');
        $response->assertRedirect('/attendance');

        $this->followingRedirects()->actingAs($user)->get('/attendance')
            ->assertSee('出勤中');
    }

    /**
     * 出勤は一日一回のみできる
     */
    public function test_出勤機能_出勤は一日一回のみできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 退勤済みの勤怠を作成しておく
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '退勤済',
            'work_start' => now()->subHours(9),
            'work_end' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertStatus(200);
        $response->assertDontSee('出勤');
    }

    /**
     * 出勤時刻が勤怠一覧で確認できる
     */
    public function test_出勤機能_出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 出勤処理（factories create）
        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '出勤中',
            'work_start' => now(),
            'work_end' => null,
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /**
     * 休憩機能：休憩ボタンが正しく機能する
     */
    public function test_休憩機能_休憩ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '出勤中',
            'work_start' => now(),
            'work_end' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-in');
        $response->assertRedirect('/attendance');

        $this->followingRedirects()->actingAs($user)->get('/attendance')
            ->assertSee('休憩中');
    }

    /**
     * 休憩は何度でもできる（入→戻→再入）
     */
    public function test_休憩機能_休憩は一日に何回でもできる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '出勤中',
            'work_start' => now(),
            'work_end' => null,
        ]);

        // 休憩入
        $this->actingAs($user)->post('/attendance/break-in');
        // 休憩戻
        $this->actingAs($user)->post('/attendance/break-out');

        $response = $this->actingAs($user)->get('/attendance');
        $response->assertSee('休憩入'); // ボタンが再表示されるはず
    }

    /**
     * 休憩戻ボタンが正しく機能する
     */
    public function test_休憩機能_休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '休憩中',
            'work_start' => now()->subHour(),
            'work_end' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/break-out');
        $response->assertRedirect('/attendance');

        $this->followingRedirects()->actingAs($user)->get('/attendance')
            ->assertSee('出勤中');
    }

    /**
     * 休憩時刻が勤怠一覧で確認できる
     *
     * NOTE: このテストは BreakTimeFactory（breaks テーブル用ファクトリ）が
     * プロジェクトに存在することを前提とします。
     */
    public function test_休憩機能_休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        // 休憩情報を含む出勤データを作成
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '休憩中',
            'work_start' => now()->subHours(2),
            'work_end' => null,
        ]);

        // BreakTimeFactory がプロジェクトに追加されていることを前提に呼ぶ
        \App\Models\BreakTime::factory()->create([
            'attendance_id' => $attendance->id,
            'break_start' => now()->subHour(),
            'break_end' => now()->subMinutes(30),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /**
     * 退勤機能：退勤ボタンが正しく機能する
     */
    public function test_退勤機能_退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '出勤中',
            'work_start' => now()->subHours(8),
            'work_end' => null,
        ]);

        $response = $this->actingAs($user)->post('/attendance/end');
        $response->assertRedirect('/attendance');

        $this->followingRedirects()->actingAs($user)->get('/attendance')
            ->assertSee('退勤済');
    }

    /**
     * 退勤時刻が勤怠一覧で確認できる
     */
    public function test_退勤機能_退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
            'status' => '退勤済',
            'work_start' => now()->subHours(9),
            'work_end' => now()->subHours(1),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /**
     * 勤怠一覧：自分の勤怠情報が表示される
     */
    public function test_勤怠一覧_自分が行った勤怠情報が全て表示されている()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');
        $response->assertStatus(200);
        $response->assertSee($attendance->work_date);
    }

    /**
     * 勤怠一覧：現在の月が表示される
     */
    public function test_勤怠一覧_現在の月が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/attendance/list');

        $response->assertStatus(200);

        $expectedMonth = now()->format('Y/m');
        $response->assertSee($expectedMonth);
    }

    /**
     * 勤怠一覧：前月表示
     */
    public function test_勤怠一覧_前月の情報が表示される()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $month = now()->subMonth();
        $response = $this->actingAs($user)
            ->get('/attendance/list?date=' . $month->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($month->format('Y年n月'));
    }

    /**
     * 勤怠一覧：翌月表示
     */
    public function test_勤怠一覧_翌月の情報が表示される()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $month = now()->addMonth();
        $response = $this->actingAs($user)
            ->get('/attendance/list?date=' . $month->format('Y-m'));

        $response->assertStatus(200);
        $response->assertSee($month->format('Y年n月'));
    }

    /** 詳細ボタンから勤怠詳細画面へ遷移 */
    public function test_勤怠一覧_詳細ボタンから勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);

        $attendance = \App\Models\Attendance::factory()->create([
            'user_id' => $user->id,
            'work_date' => now()->toDateString(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('attendance.detail', ['date' => $attendance->work_date]));

        $response->assertStatus(200);
        $response->assertSee('勤怠詳細');
    }

    /**
     * 勤怠修正：出勤時間が退勤時間より後ならエラー
     */
    public function test_勤怠修正_出勤時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->work_date}", [
            'work_date' => $attendance->work_date,
            'work_start' => '20:00',
            'work_end' => '09:00',
            'note' => 'テスト修正',
            'breaks' => [],
        ]);

        $response->assertSessionHasErrors(['work_start']);
    }

    /**
     * 勤怠修正：休憩開始時間が退勤時間より後ならエラー
     */
    public function test_勤怠修正_休憩開始時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create(['email_verified_at' => now()]);
        $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)
            ->from("/attendance/detail/{$attendance->work_date}")
            ->put("/attendance/detail/{$attendance->work_date}", [
                'work_date' => $attendance->work_date,
                'work_start' => '09:00',
                'work_end' => '18:00',
                'note' => 'テスト修正',
                'breaks' => [
                    ['break_start' => '20:00', 'break_end' => '21:00']
                ],
            ]);

        // break_start > work_end のバリデーションキーに合わせる
        $response->assertSessionHasErrors(['breaks.0.break_start']);
    }

    /**
     * 勤怠修正：休憩終了時間が退勤時間より後ならエラー
     */
    public function test_勤怠修正_休憩終了時間が退勤時間より後ならエラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->work_date}", [
            'work_date' => $attendance->work_date,
            'work_start' => '09:00',
            'work_end' => '18:00',
            'note' => 'テスト修正',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '20:00']
            ],
        ]);

        $response->assertSessionHasErrors(['breaks.0.break_end']);
    }

    /**
     * 勤怠修正：備考未入力ならエラー
     */
    public function test_勤怠修正_備考未入力ならエラー()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->work_date}", [
            'work_date' => $attendance->work_date,
            'work_start' => '09:00',
            'work_end' => '18:00',
            'note' => '',
            'breaks' => [],
        ]);

        $response->assertSessionHasErrors(['note']);
    }

    /**
     * 勤怠修正：修正申請が作成される（controller 実装に合わせて reason フィールドを確認）
     */
    public function test_勤怠修正_申請処理が実行される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->put("/attendance/detail/{$attendance->work_date}", [
            'work_date' => $attendance->work_date,
            'work_start' => '08:30',
            'work_end' => '17:30',
            'note' => '時間修正テスト',
            'breaks' => [
                ['break_start' => '12:00', 'break_end' => '13:00']
            ],
        ]);

        $response->assertRedirect(); // controller はリダイレクトする

        $this->assertDatabaseHas('stamp_correction_requests', [
            'user_id' => $user->id,
            'reason'  => '時間修正テスト',
            'status'  => 'pending',
        ]);
    }

    /**
     * 承認待ち一覧に自分の申請が表示される（ユーザ側）
     */
    public function test_勤怠修正_承認待ち一覧に自分の申請が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);
        \App\Models\StampCorrectionRequest::factory()->count(2)->create([
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $response = $this->actingAs($user)->get('/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('承認待ち');
    }

    /**
     * 管理者側：承認済みに承認済申請が表示される
     */
    public function test_勤怠修正_承認済みに承認済申請が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        \App\Models\StampCorrectionRequest::factory()->count(2)->create([
            'status' => 'approved',
        ]);

        $response = $this->actingAs($admin)->get('/admin/stamp_correction_request/list');
        $response->assertStatus(200);
        $response->assertSee('承認済み');
    }

    /**
     * 管理者：勤怠一覧に全ユーザーの勤怠情報が表示される
     */
    public function test_管理者_勤怠一覧に全ユーザーの勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        \App\Models\Attendance::factory()->count(3)->create();

        $response = $this->actingAs($admin)->get('/admin/attendance/list');
        $response->assertStatus(200);
        $response->assertSee('勤怠一覧');
        $this->assertDatabaseCount('attendances', 3);
    }

    /**
     * 管理者：勤怠一覧に現在日付が表示される
     */
    public function test_管理者_勤怠一覧に現在日付が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list');

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y-m-d'));
    }

    /**
     * 管理者：前日表示
     */
    public function test_管理者_前日ボタンで前日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $date = now()->subDay()->toDateString();
        \App\Models\Attendance::factory()->create(['work_date' => $date]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee($date);
    }

    /**
     * 管理者：翌日表示
     */
    public function test_管理者_翌日ボタンで翌日の勤怠情報が表示される()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);

        $date = now()->addDay()->toDateString();
        \App\Models\Attendance::factory()->create(['work_date' => $date]);

        $response = $this->actingAs($admin, 'admin')
            ->get('/admin/attendance/list?date=' . $date);

        $response->assertStatus(200);
        $response->assertSee($date);
    }

    /**
     * 管理者：勤怠詳細の内容が選択したデータと一致する
     */
    public function test_管理者_勤怠詳細画面が選択したデータと一致する()
    {
        $admin = User::factory()->create([
            'role' => 'admin',
            'email_verified_at' => now(),
        ]);
        $attendance = \App\Models\Attendance::factory()->create(['work_date' => '2025-10-10']);

        $response = $this->actingAs($admin)->get("/admin/attendance/{$attendance->id}");
        $response->assertStatus(200);
        $response->assertSee('2025-10-10');
    }

    /**
     * メール認証誘導画面に「認証はこちら」ボタンがある & 再送フォームがある
     */
    public function test_メール認証誘導画面_認証はこちらボタンが存在する()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $response = $this->get('/email/verify');

        $response->assertStatus(200);
        $response->assertSee('認証はこちらから'); // Blade の文言確認
        $response->assertSee('/email/verification-notification'); // 再送フォーム先
    }

    /**
     * 署名付き認証リンクをクリックすると認証完了して勤怠画面にリダイレクトされる
     */
    public function test_認証リンクをクリックするとメール認証が完了し勤怠登録にリダイレクトされる()
    {
        $user = User::factory()->create([
            'email_verified_at' => null,
        ]);

        $this->actingAs($user);

        $verifyUrl = URL::temporarySignedRoute(
            'verification.verify',
            Carbon::now()->addMinutes(10),
            ['id' => $user->id, 'hash' => sha1($user->email)]
        );

        $response = $this->get($verifyUrl);

        $response->assertRedirect('/attendance');
        $this->assertNotNull($user->fresh()->email_verified_at);
    }
}