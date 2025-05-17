<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Transaksi;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index()
    {
        return view('statistik.index.blade.php');
    }
}
