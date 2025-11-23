<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;

class AdminStaffController extends Controller
{
    public function index()
    {
        // 一般ユーザー全員取得
        $users = User::select('id', 'name', 'email')->get();

        return view('admin_staff_list', compact('users'));
    }
}