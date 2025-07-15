<?php

namespace App\Http\Controllers;

class HomeController extends Controller
{
    public function index()
    {
        return view('home'); // This will render 'resources/views/home.blade.php'
    }
}
