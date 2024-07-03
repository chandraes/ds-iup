<?php

namespace App\Http\Controllers;

use App\Models\GroupWa;
use App\Services\WaStatus;
use Illuminate\Http\Request;

class WaController extends Controller
{
    public function index()
    {
        $data = GroupWa::whereNot('untuk', 'team')->get();
        return view('pengaturan.wa.index', [
            'data' => $data
        ]);
    }

    public function get_group_wa()
    {
        $wa = new WaStatus();
        $group = $wa->getGroup();

        return response()->json($group['data']['groups']);
    }

    public function update(Request $request, GroupWa $group_wa)
    {
        $data = $request->validate([
            'nama_group' => 'required',
            'group_id' => 'required',
        ]);

        $group_wa->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah.');
    }
}
