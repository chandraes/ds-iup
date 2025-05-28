<?php

namespace App\Http\Controllers;

use App\Models\db\CostOperational;
use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\db\Kreditor;
use App\Models\db\Pajak;
use App\Models\db\SalesArea;
use App\Models\db\Satuan;
use App\Models\db\Supplier;
use App\Models\Pengelola;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;

class DatabaseController extends Controller
{
    public function satuan()
    {
        $data = Satuan::all();

        return view('db.satuan.index', [
            'data' => $data,
        ]);
    }

    public function satuan_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        Satuan::create($data);

        return redirect()->route('db.satuan')->with('success', 'Data berhasil ditambahkan');
    }

    public function satuan_update(Satuan $satuan, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $satuan->update($data);

        return redirect()->route('db.satuan')->with('success', 'Data berhasil diupdate');
    }

    public function satuan_delete(Satuan $satuan)
    {
        $satuan->delete();

        return redirect()->route('db.satuan')->with('success', 'Data berhasil dihapus');
    }

    public function cost_operational()
    {
        $data = CostOperational::all();

        return view('db.cost-operational.index', [
            'data' => $data,
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
            'jabatan' => $jabatan,
        ]);
    }

    public function staff_create()
    {
        $jabatan = Jabatan::all();

        return view('db.karyawan.create', [
            'jabatan' => $jabatan,
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
            $file_name_ktp = Uuid::uuid4().'- KTP - '.$data['nama'].'.'.$request->foto_ktp->extension();
            $file_name_diri = Uuid::uuid4().' - Foto Diri '.$data['nama'].'.'.$request->foto_diri->extension();

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
            'jabatan' => $jabatan,
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
                $file_name_ktp = Uuid::uuid4().'- KTP - '.$data['nama'].'.'.$request->foto_ktp->extension();
                $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
                $ktp_path = storage_path('app/'.$staff->foto_ktp);
                unlink($ktp_path);
            }

            if ($request->hasFile('foto_diri')) {
                $file_name_diri = Uuid::uuid4().' - Foto Diri '.$data['nama'].'.'.$request->foto_diri->extension();
                $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
                $diri_path = storage_path('app/'.$staff->foto_diri);
                unlink($diri_path);
            }

            $staff->update($data);

            DB::commit();

        } catch (\Throwable $th) {
            // throw $th;

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
            // throw $th;
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

        return redirect()->route('db.staff')->with('success', 'Data berhasil dihapus');
    }

    public function pajak()
    {
        $data = Pajak::all();

        return view('db.pajak.index', [
            'data' => $data,
        ]);
    }

    public function pajak_update(Pajak $pajak, Request $request)
    {
        $data = $request->validate([
            'persen' => 'required',
        ]);

        $pajak->update($data);

        return redirect()->route('db.pajak')->with('success', 'Data berhasil diupdate');
    }

    public function pengelola()
    {
        $data = Pengelola::all();

        return view('db.pengelola.index', [
            'data' => $data,
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
            'nama_rek' => 'required',
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
            'nama_rek' => 'required',
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

    public function kode_toko()
    {
        return response()->json([
            'data' => KodeToko::select('id', 'kode')->get(),
        ]);
    }

    public function kode_toko_store(Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|unique:kode_tokos,kode',
        ]);

        KodeToko::create($data);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function kode_toko_update(KodeToko $kode, Request $request)
    {
        $data = $request->validate([
            'kode' => 'required|unique:kode_tokos,kode,'.$kode->id,
        ]);

        $kode->update($data);

        return response()->json(['message' => 'Data berhasil diubah']);
    }

    public function kode_toko_delete(KodeToko $kode)
    {
        try {
            DB::beginTransaction();
            $kode->delete();
            DB::commit();

            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function sales_area(Request $request)
    {
        return response()->json([
            'data' => SalesArea::select('id', 'nama')->get(),
        ]);
    }

    public function sales_area_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        SalesArea::create($data);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function sales_area_update(SalesArea $sales, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
        ]);

        $sales->update($data);

        return response()->json(['message' => 'Data berhasil diubah']);
    }

    public function sales_area_delete(SalesArea $sales)
    {
        try {
            DB::beginTransaction();
            $sales->delete();
            DB::commit();

            return response()->json(['message' => 'Data berhasil dihapus']);
        } catch (\Throwable $th) {
            DB::rollBack();

            return response()->json(['error' => $th->getMessage()], 500);
        }
    }

    public function konsumen(Request $request)
    {
         $filters = $request->only(['area', 'kabupaten_kota','kecamatan', 'kode_toko', 'status', 'provinsi']); // Ambil filter dari request
        $wilayah = Wilayah::whereIn('id_level_wilayah', [1, 2, 3])->get()->groupBy('id_level_wilayah');
        $provinsi = $wilayah->get(1, collect());

        if ($request->has('provinsi') && $request->input('provinsi') != '') {
            $prov = Wilayah::find($request->input('provinsi'));
            $kabupaten_kota = Wilayah::where('id_induk_wilayah', $prov->id_wilayah)->where('id_level_wilayah', 2)->get();
        } else {
            $kabupaten_kota = $wilayah->get(2, collect());
        }

        if ($request->has('kabupaten_kota') && $request->input('kabupaten_kota') != '') {
            $kab = Wilayah::find($request->input('kabupaten_kota'));
            $kecamatan_filter = Wilayah::where('id_induk_wilayah', $kab->id_wilayah )->where('id_level_wilayah', 3)->get();
        } else {
            $kecamatan_filter = $wilayah->get(3, collect());
        }

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('db.konsumen.index', [
            'provinsi' => $provinsi,
            'kabupaten_kota' => $kabupaten_kota,
            'sales_area' => $sales_area,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'kecamatan_filter' => $kecamatan_filter,
        ]);
    }

    public function konsumen_data(Request $request)
    {
        $start = $request->input('start', 0);
        $length = $request->input('length', 10);
        $search = $request->input('search.value');
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'provinsi', 'kabupaten_kota', 'provinsi']);

        $query = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
            ->filter($filters);

        if ($search) {
            $query->where(function($q) use ($search) {
            $q->where('nama', 'like', "%$search%")
            ->orWhere('no_hp', 'like', "%{$search}%")
            ->orWhere('alamat', 'like', "%$search%");
            });
        }

        // Sorting
        $orderColumnIndex = $request->input('order.0.column');
        $orderDir = $request->input('order.0.dir', 'asc');
        $columns = $request->input('columns', []);
        if ($orderColumnIndex !== null && isset($columns[$orderColumnIndex]['data'])) {
            $orderColumn = $columns[$orderColumnIndex]['data'];
            // Map frontend column to database column if needed
            $sortable = [
            'full_kode' => 'kode',
            'kode_toko' => 'kode_toko_id',
            'nama' => 'nama',
            'npwp' => 'npwp',
            'sales_area' => 'karyawan_id',
            'provinsi' => 'provinsi_id',
            'kab_kota' => 'kabupaten_kota_id',
            'kecamatan' => 'kecamatan_id',
            'alamat' => 'alamat',
            'pembayaran' => 'pembayaran',
            'limit_plafon' => 'plafon',
            ];
            if (isset($sortable[$orderColumn])) {
            $query->orderBy($sortable[$orderColumn], $orderDir);
            }
        } else {
            $query->orderBy('id', 'desc');
        }

        $total = $query->count();
        $data = $query->skip($start)->take($length)->get();

        $allNoHp = Konsumen::pluck('no_hp');
        $noHpCounts = collect($allNoHp)->countBy();

        // Format data sesuai kebutuhan DataTables
        $result = [];
        foreach ($data as $d) {
            $no_hp = $d->no_hp;
            $hasSpace = strpos($no_hp, ' ') !== false;
            $isDuplicate = $noHpCounts[$no_hp] > 1;

            $textClass = ($hasSpace || $isDuplicate) ? 'text-danger' : '';
            $result[] = [
            'full_kode' => $d->full_kode,
            'kode_toko' => $d->kode_toko ? $d->kode_toko->kode : '',
            'nama' => $d->nama,
            'cp' => "
                <ul>
                    <li>CP : {$d->cp}</li>
                    <li class='{$textClass}'>No.HP : {$no_hp}</li>
                    <li>No.Kantor : {$d->no_kantor}</li>
                </ul>",
            'npwp' => $d->npwp,
            'sales_area' => $d->karyawan ? $d->karyawan->nama : '',
            'provinsi' => $d->provinsi ? $d->provinsi->nama_wilayah : '',
            'kab_kota' => $d->kabupaten_kota ? $d->kabupaten_kota->nama_wilayah : '',
            'kecamatan' => $d->kecamatan ? $d->kecamatan->nama_wilayah : '',
            'alamat' => $d->alamat,
            'pembayaran' => $d->sistem_pembayaran . ($d->pembayaran == 2 ? " ({$d->tempo_hari} Hari)" : ''),
            'limit_plafon' => $d->nf_plafon,
            'act' => view('db.konsumen.action', compact('d'))->render(),
            ];
        }

        return response()->json([
            'draw' => intval($request->input('draw')),
            'recordsTotal' => $total,
            'recordsFiltered' => $total,
            'data' => $result,
        ]);

    }

    public function konsumen_store(Request $request)
    {
        $data = $request->validate([
            'kode_toko_id' => 'required|exists:kode_tokos,id',
            'nama' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_kantor' => 'nullable',
            'npwp' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_kota_id' => 'required',
            'kecamatan_id' => 'nullable',
            'alamat' => 'required',
            'pembayaran' => 'required',
            'plafon' => 'required_if:pembayaran,1',
            'tempo_hari' => 'required_if:pembayaran,1',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        $db = new Konsumen;

        $data['plafon'] = str_replace('.', '', $data['plafon']);

        $data['kode'] = $db->generateKode();

        $db->create($data);

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil ditambahkan');
    }

    public function konsumen_update(Konsumen $konsumen, Request $request)
    {
        $data = $request->validate([
            'kode_toko_id' => 'required|exists:kode_tokos,id',
            'nama' => 'required',
            'cp' => 'required',
            'no_hp' => 'required',
            'no_kantor' => 'nullable',
            'npwp' => 'required',
            'provinsi_id' => 'required',
            'kabupaten_kota_id' => 'required',
            'kecamatan_id' => 'nullable',
            'alamat' => 'required',
            'pembayaran' => 'required',
            'plafon' => 'required',
            'tempo_hari' => 'required',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        $data['plafon'] = str_replace('.', '', $data['plafon']);
        $konsumen->update($data);

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil diupdate');
    }

    public function konsumen_delete(Konsumen $konsumen, Request $request)
    {
        $data = $request->validate([
            'alasan' => 'required_if:status,1',
        ]);

        $konsumen->update([
            'active' => !$konsumen->active,
            'alasan' => $data['alasan'] ?? null,
        ]);

        return redirect()->route('db.konsumen')->with('success', 'Data berhasil Di Nonaktifkan');
    }

    public function supplier()
    {
        $data = Supplier::all();

        return view('db.supplier.index', [
            'data' => $data,
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

        $db = new Supplier;

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

        if ($data['pembayaran'] == 1) {
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
            'kategori' => $kategori,
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

    public function kreditor()
    {
        $data = Kreditor::where('is_active', 1)->get();

        return view('db.kreditor.index', [
            'data' => $data,
        ]);
    }

    public function kreditor_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persen' => 'required',
            'npwp' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'bank' => 'required',
            'apa_pph' => 'required',
        ]);

        Kreditor::create($data);

        return redirect()->back()->with('success', 'Data berhasil ditambahkan');
    }

    public function kreditor_update(Kreditor $kreditor, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'persen' => 'required',
            'npwp' => 'required',
            'no_rek' => 'required',
            'nama_rek' => 'required',
            'bank' => 'required',
            'apa_pph' => 'required',
        ]);

        $kreditor->update($data);

        return redirect()->back()->with('success', 'Data berhasil diubah');
    }

    public function kreditor_destroy(Kreditor $kreditor)
    {
        $kreditor->update(['is_active' => 0]);

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }
}
