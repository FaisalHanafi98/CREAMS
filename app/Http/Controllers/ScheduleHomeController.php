<?php

namespace App\Http\Controllers;

use App\Models\Courses;
use Illuminate\Http\Request;

class ScheduleHomeController extends Controller
{
    public function index()
    {
        $courses = Courses::with('teacher')->get();
        return view('schedulehome', compact('courses'));
    }
}
