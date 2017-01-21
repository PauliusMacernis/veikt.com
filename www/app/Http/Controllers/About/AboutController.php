<?php

namespace App\Http\Controllers\About;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class AboutController extends Controller
{
    public function mission() {
        return view('about/mission');
    }

    public function vision() {
        return view('about/vision');
    }
}
