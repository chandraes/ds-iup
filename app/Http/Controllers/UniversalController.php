<?php

namespace App\Http\Controllers;

use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangType;
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

    public function searchKonsumen(Request $request)
    {
        $search = $request->search;
        $data = Konsumen::with(['kode_toko', 'kecamatan'])->where('nama', 'like', '%' . $search . '%')
            ->where('active', 1)
            ->select('id', 'nama', 'kode_toko_id', 'pembayaran', 'kecamatan_id')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
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

    public function searchBarangNama(Request $request)
    {
         $search = $request->search;
        $kategori = $request->kategori; // tambahkan ini

        $query = BarangNama::query();

        // Filter berdasarkan nama jika ada search
        if ($search) {
            $query->where('nama', 'like', '%' . $search . '%');
        }

        // Filter berdasarkan kategori jika ada
        if ($kategori) {
            $query->where('barang_kategori_id', $kategori);
        }

        $data = $query->select('id', 'nama')
                    ->take(50) // batasi hasil untuk performa
                    ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function searchBarangKategori(Request $request)
    {
        $search = $request->search;
        $data = BarangKategori::where('nama', 'like', '%' . $search . '%')
            ->select('id', 'nama')
            ->get();

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }

    public function searchBarangType(Request $request)
    {
        $search = $request->search;
        $data = BarangType::where('nama', 'like', '%' . $search . '%')
            ->select('id', 'nama')
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
