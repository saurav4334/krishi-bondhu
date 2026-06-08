<?php

namespace App\Http\Controllers;

use App\Models\Expert;
use Illuminate\View\View;

class ExpertController extends Controller
{
    public function index(): View
    {
        $experts = Expert::active()->orderBy('name')->get();
        return view('experts.index', compact('experts'));
    }
}
