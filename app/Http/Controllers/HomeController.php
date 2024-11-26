<?php

namespace App\Http\Controllers;

use App\Services\WaStatus;
use Illuminate\Http\Request;

class HomeController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        return view('home');
    }

    public function getStatusWa()
    {
        $service = new WaStatus();
        $result = $service->getStatusWa();

        return response()->json($result);
    }
}
