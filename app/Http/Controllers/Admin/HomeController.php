<?php

namespace App\Http\Controllers\Admin;

use App\Components\Admin\Manager as AdminManager;
use App\Forms\UpdatePasswordForm;
use Auth;
use Illuminate\Http\Request;

class HomeController extends ManagementController
{
    public function index()
    {
        return view('index');
    }
}
