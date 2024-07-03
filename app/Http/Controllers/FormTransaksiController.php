<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class FormTransaksiController extends Controller
{
    public function index()
    {
        return view('billing.form-transaksi.index', [

        ]);
    }
}
