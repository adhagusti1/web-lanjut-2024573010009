<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class WelcomeController extends Controller
{
    public function show()
    {
        $message = "Welcome to Laravel!";
        return view('mywelcome', ['message' => $message]);
    }
}
