<?php

namespace App\Http\Controllers;

use App\Models\Holding;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class HoldingController extends Controller
{
    public function check_connection()
    {
        $holding = Holding::first();

        if (!$holding) {
            return response()->json(['status' => 'error', 'message' => 'Holding belum diatur']);
        }

        // http request to holding_url
        $response = Http::get($holding->holding_url.'/api/1.0/check-connection', [
            'token' => $holding->token
        ]);

        // dd($response);

        if ($response->status() == 200) {
            return response()->json(['status' => 'success', 'message' => 'Koneksi berhasil']);
        } else {
            return response()->json(['status' => 'error', 'message' => 'Koneksi gagal']);
        }

    }
}
