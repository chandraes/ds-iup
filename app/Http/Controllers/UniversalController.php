<?php

namespace App\Http\Controllers;

use App\Models\db\Konsumen;
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

    public function getStatusWa()
    {
        $service = new WaStatus;
        $result = $service->getStatusWa();

        return response()->json($result);
    }
}
