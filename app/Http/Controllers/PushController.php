<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PushController extends Controller
{
    public function index()
    {
        return view('push.index');
    }

    public function create()
    {
        return view('push.create');
    }
}
