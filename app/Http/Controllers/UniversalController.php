<?php

namespace App\Http\Controllers;

use App\Models\db\Konsumen;
use App\Models\Wilayah;
use App\Services\WaStatus;
use Illuminate\Http\Request;

class UniversalController extends Controller
{
    public function getKonsumen(Request $request)
    {
        $data = $request->validate([
            'id' => 'required|exists:konsumens,id',
        ]);

        $konsumen = Konsumen::with('kabupaten_kota')->where('id',$data['id'])->first();

        return response()->json($konsumen);
    }

    public function searchKecamatan(Request $request)
    {
        $search = $request->search;
        $data = Wilayah::where('id_level_wilayah', 3)
            ->where('nama_wilayah', 'like', '%' . $search . '%')
            ->select('id', 'nama_wilayah')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function getStatusWa()
    {
        $service = new WaStatus;
        $result = $service->getStatusWa();

        return response()->json($result);
    }
}
