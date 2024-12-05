<?php

namespace App\Http\Controllers;

use App\Models\Wilayah;
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

    public function getKabKota(Request $request)
    {
        $provinsi = $request->provinsi;
        $db = new Wilayah();
        $provinsi_data = $db->find($provinsi);

        $data = $db->where('id_level_wilayah', 2)->where('id_induk_wilayah', $provinsi_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);

    }

    public function getKecamatan(Request $request)
    {
        $kab = $request->kab;
        $db = new Wilayah();
        $kab_data = $db->find($kab);

        $data = $db->where('id_level_wilayah', 3)->where('id_induk_wilayah', $kab_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data
        ]);
    }
}
