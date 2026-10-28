<?php

namespace App\Http\Controllers;

use App\Models\PageSection;

class HomeController extends Controller
{
    public function __invoke()
    {
        return view('pages.home', [
            'sections' => PageSection::forPage('home')->get(),
        ]);
    }
}
