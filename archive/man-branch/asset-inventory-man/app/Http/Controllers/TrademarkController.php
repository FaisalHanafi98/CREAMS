<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class TrademarkController extends Controller
{
    public function index()
    {
        return view('trademarks');
    }
}
