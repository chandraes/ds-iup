<?php

namespace App\Http\Controllers;

use App\Models\db\CostOperational;
use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\db\Supplier;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\Pengelola;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Illuminate\Validation\Rule;

class DatabaseController extends Controller
{

    public function cost_operational()
    {
        $data = CostOperational::all();

        return view('db.cost-operational.index', [
            'data' => $data
        ]);
    }

    public function cost_operational_store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
        ]);

        CostOperational::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function cost_operational_update(CostOperational $cost, Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
        ]);

        $cost->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }

    public function cost_operational_delete(CostOperational $cost)
    {
        $cost->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function jabatan_store(Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
        ]);

        Jabatan::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function jabatan_update(Jabatan $jabatan, Request $req)
    {
        $data = $req->validate([
            'nama' => 'required',
        ]);

        $jabatan->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }

    public function jabatan_delete(Jabatan $jabatan)
    {
        $jabatan->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function staff()
    {
        $data = Karyawan::with(['jabatan'])->get();
        $jabatan = Jabatan::all();

        return view('db.karyawan.index', [
            'data' => $data,
            'jabatan' => $jabatan
        ]);
    }

    public function staff_create()
    {
        $jabatan = Jabatan::all();

        return view('db.karyawan.create', [
            'jabatan' => $jabatan
        ]);
    }

    public function staff_store(Request $request)
    {
        $data = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'nama' => 'required',
            'nickname' => 'required',
            'gaji_pokok' => 'required',
            'tunjangan_jabatan' => 'required',
            'tunjangan_keluarga' => 'required',
            'nik' => 'required',
            'npwp' => 'required',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kes' => 'nullable',
            'bpjs_tk' => 'required',
            'bpjs_kesehatan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'bank' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'mulai_bekerja' => 'required',
            'foto_ktp' => 'required|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'required|mimes:jpg,jpeg,png|max:10000',
            'status' => 'required',
        ]);

        $data['nomor'] = Karyawan::max('nomor') + 1;

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['apa_bpjs_tk'] = $request->filled('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kes'] = $request->filled('apa_bpjs_kes') ? 1 : 0;

        try {
            DB::beginTransaction();
            $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
            $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();

            $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
            $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);

            Karyawan::create($data);

            DB::commit();

            return redirect()->route('db.staff')->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

    }

    public function staff_edit(Karyawan $staff)
    {
        $jabatan = Jabatan::all();

        return view('db.karyawan.edit', [
            'data' => $staff,
            'jabatan' => $jabatan
        ]);
    }

    public function staff_update(Karyawan $staff, Request $request)
    {
        $data = $request->validate([
            'jabatan_id' => 'required|exists:jabatans,id',
            'nama' => 'required',
            'nickname' => 'required',
            'gaji_pokok' => 'required',
            'tunjangan_jabatan' => 'required',
            'tunjangan_keluarga' => 'required',
            'nik' => 'required',
            'npwp' => 'required',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kes' => 'nullable',
            'bpjs_tk' => 'required',
            'bpjs_kesehatan' => 'required',
            'tempat_lahir' => 'required',
            'tanggal_lahir' => 'required',
            'alamat' => 'required',
            'no_hp' => 'required',
            'bank' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'mulai_bekerja' => 'required',
            'status' => 'required',
            'foto_ktp' => 'nullable|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'nullable|mimes:jpg,jpeg,png|max:10000',
        ]);

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['apa_bpjs_tk'] = $request->filled('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kes'] = $request->filled('apa_bpjs_kes') ? 1 : 0;

        try {
            DB::beginTransaction();

            if ($request->hasFile('foto_ktp')) {
                $file_name_ktp = Uuid::uuid4().'- KTP - '. $data['nama']. '.' . $request->foto_ktp->extension();
                $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
                $ktp_path = storage_path('app/'.$staff->foto_ktp);
                unlink($ktp_path);
            }

            if ($request->hasFile('foto_diri')) {
                $file_name_diri = Uuid::uuid4(). ' - Foto Diri '. $data['nama']. '.' . $request->foto_diri->extension();
                $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
                $diri_path = storage_path('app/'.$staff->foto_diri);
                unlink($diri_path);
            }

            $staff->update($data);

            DB::commit();


        } catch (\Throwable $th) {
            //throw $th;

            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

        return redirect()->route('db.staff')->with('success', 'Data berhasil diupdate');
    }

    public function staff_delete(Karyawan $staff)
    {
        try {
            DB::beginTransaction();

            $ktp_path = storage_path('app/'.$staff->foto_ktp);
            $diri_path = storage_path('app/'.$staff->foto_diri);

            if (file_exists($ktp_path)) {
                unlink($ktp_path);
            }

            if (file_exists($diri_path)) {
                unlink($diri_path);
            }

            $staff->delete();

            DB::commit();


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }


        return redirect()->route('db.staff')->with('success', 'Data berhasil dihapus');
    }

    public function pajak()
    {
        $data = Pajak::all();
        return view('db.pajak.index', [
            'data' => $data
        ]);
    }

    public function pajak_update(Pajak $pajak, Request $request)
    {
        $data = $request->validate([
            'persen' => 'required'
        ]);

        $pajak->update($data);

        return redirect()->route('db.pajak')->with('success', 'Data berhasil diupdate');
    }

    public function pengelola()
    {
        $data = Pengelola::all();

        return view('db.pengelola.index', [
            'data' => $data
        ]);
    }

    public function pengelola_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'persentase' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required'
        ]);

        $check = Pengelola::sum('persentase') + $data['persentase'];

        if ($check > 100) {
            return redirect()->route('db.pengelola')->with('error', 'Persentase tidak boleh melebihi 100%');
        }

        Pengelola::create($data);

        return redirect()->route('db.pengelola')->with('success', 'Data berhasil ditambahkan');
    }

    public function pengelola_update(Pengelola $pengelola, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'no_hp' => 'required',
            'persentase' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required'
        ]);

        $check = Pengelola::whereNot('id', $pengelola->id)->sum('persentase') + $data['persentase'];

        if ($check > 100) {
            return redirect()->route('db.pengelola')->with('error', 'Persentase tidak boleh melebihi 100%');
        }

        $pengelola->update($data);

        return redirect()->route('db.pengelola')->with('success', 'Data berhasil diupdate');
    }

    public function pengelola_delete(Pengelola $pengelola)
    {
        $pengelola->delete();

        return redirect()->route('db.pengelola')->with('success', 'Data berhasil dihapus');
    }

    public function konsumen()
    {
        $data = Konsumen::all();

        return view('db.konsumen.index', [
            'data' => $data
        ]);
    }

    public function konsumen_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_kantor' => 'nullable',
            'npwp' => 'required',
            'kota' => 'required',
            'alamat' => 'required',
            'pembayaran' => 'required',
            'plafon' => 'required_if:pembayaran,1',
            'tempo_hari' => 'required_if:pembayaran,1',
        ]);

        $db = new Konsumen();

        $data['plafon'] = str_replace('.', '', $data['plafon']);

        $data['kode'] = $db->generateKode();

        $db->create($data);

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil ditambahkan');
    }

    public function konsumen_update(Konsumen $konsumen, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_kantor' => 'nullable',
            'npwp' => 'required',
            'kota' => 'required',
            'alamat' => 'required',
            'pembayaran' => 'required',
            'plafon' => 'required',
            'tempo_hari' => 'required'
        ]);

        $data['plafon'] = str_replace('.', '', $data['plafon']);
        $konsumen->update($data);

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil diupdate');
    }

    public function konsumen_delete(Konsumen $konsumen)
    {
        $konsumen->delete();

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil dihapus');
    }

    public function supplier()
    {
        $data = Supplier::all();

        return view('db.supplier.index', [
            'data' => $data
        ]);
    }

    public function supplier_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
            'pembayaran' => 'required|in:1,2',
            'tempo_hari' => 'required_if:pembayaran,2',
            'status' => 'required',
        ]);

        $db = new Supplier();

        $store = $db->createSupplier($data);

        return redirect()->route('db.supplier')->with($store['status'], $store['message']);
    }

    public function supplier_update(Request $request, Supplier $supplier)
    {
        $data = $request->validate([
            'nama' => 'required',
            'alamat' => 'required',
            'kota' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_rek' => 'required',
            'bank' => 'required',
            'nama_rek' => 'required',
            'pembayaran' => 'required|in:1,2',
            'tempo_hari' => 'required_if:pembayaran,2',
            'status' => 'required',
        ]);

        if($data['pembayaran'] == 1) {
            $data['tempo_hari'] = null;
        }

        $supplier->update($data);

        return redirect()->route('db.supplier')->with('success', 'Data berhasil diupdate');
    }

    public function supplier_delete(Supplier $supplier)
    {
        $supplier->delete();

        return redirect()->route('db.supplier')->with('success', 'Data berhasil dihapus');
    }

    public function kategori_inventaris()
    {
        $data = InventarisKategori::with(['jenis'])->whereHas('jenis')->get();
        $kategori = InventarisKategori::all();

        return view('db.kategori-inventaris.index', [
            'data' => $data,
            'kategori' => $kategori
        ]);
    }

    public function kategori_inventaris_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        InventarisKategori::create($data);

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil ditambahkan');
    }

    public function kategori_inventaris_update(InventarisKategori $kategori, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $kategori->update($data);

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil diupdate');
    }

    public function kategori_inventaris_delete(InventarisKategori $kategori)
    {
        $kategori->delete();

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil dihapus');
    }

    public function jenis_inventaris_store(Request $request)
    {
        $data = $request->validate([
            'kategori_id' => 'required|exists:inventaris_kategoris,id',
            'nama' => 'required',
        ]);

        InventarisJenis::create($data);

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil ditambahkan');
    }

    public function jenis_inventaris_update(InventarisJenis $jenis, Request $request)
    {
        $data = $request->validate([
            'kategori_id' => 'required|exists:inventaris_kategoris,id',
            'nama' => 'required',
        ]);

        $jenis->update($data);

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil diupdate');
    }

    public function jenis_inventaris_delete(InventarisJenis $jenis)
    {
        $jenis->delete();

        return redirect()->route('db.kategori-inventaris')->with('success', 'Data berhasil dihapus');
    }


}
