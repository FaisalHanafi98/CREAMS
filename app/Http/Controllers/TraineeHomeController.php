<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Trainees;
use Illuminate\Support\Facades\Auth;

class TraineeHomeController extends Controller
{
    public function index()
    {
        $trainees = Trainees::all();
        $classes = $trainees->pluck('class')->unique()->toArray();
        $traineesByClass = Trainees::orderBy('centre_name')->get()->groupBy('centre_name');

        return view('traineeshome', [
            'traineesByClass' => $traineesByClass,
            'classes' => $classes
        ]);
    }

    public function updateuserpage($id)
    {
        $trainee = Trainees::findOrFail($id);

        return view('updateuserprofile', ['trainee' => $trainee]);
    }
}

