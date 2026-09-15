<?php

namespace App\Http\Controllers;

/**
 * Landing sahifa — hero ko'rinishidagi home.
 */
class HomeController extends Controller
{
    public function index()
    {
        return view('home');
    }
}
