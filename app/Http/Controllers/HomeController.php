<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\Barang;
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
        if (auth()->user()->role == 'perusahaan') {
            $barang_ppn = Barang::where('barang_unit_id', auth()->user()->barang_unit_id)
                ->where('jenis', 1)
                ->first() ? 1 : 0;

            $barang_non_ppn = Barang::where('barang_unit_id', auth()->user()->barang_unit_id)
                ->where('jenis', 2)
                ->first() ? 1 : 0;

            return view('home', [
                'barang_ppn' => $barang_ppn,
                'barang_non_ppn' => $barang_non_ppn,
            ]);
        }
        return view('home');
    }

    public function getStatusWa()
    {
        $service = new WaStatus;
        $result = $service->getStatusWa();

        return response()->json($result);
    }

    public function getKabKota(Request $request)
    {
        $provinsi = $request->provinsi;
        $db = new Wilayah;
        $provinsi_data = $db->find($provinsi);

        $data = $db->where('id_level_wilayah', 2)->where('id_induk_wilayah', $provinsi_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);

    }

    public function getKecamatan(Request $request)
    {
        $kab = $request->kab;
        $db = new Wilayah;
        $kab_data = $db->find($kab);

        $data = $db->where('id_level_wilayah', 3)->where('id_induk_wilayah', $kab_data->id_wilayah)->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
}
