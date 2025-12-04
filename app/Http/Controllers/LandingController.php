<?php

namespace App\Http\Controllers;

use Illuminate\View\View;

class LandingController extends Controller
{
    /**
     * Public marketing landing page for BadliCash.
     */
    public function index(): View
    {
        return view('landing');
    }
}


