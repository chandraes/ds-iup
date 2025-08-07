<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\CostOperational;
use App\Models\db\DiskonUmum;
use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\db\KelompokRute;
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
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Storage;
use Yajra\DataTables\Facades\DataTables;

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


    public function konsumen_data(Request $request)
    {
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        $data = Konsumen::query()->with(['kode_toko', 'provinsi', 'kabupaten_kota', 'kecamatan', 'karyawan'])
                ->filter($filters);

        // Ambil semua no_hp yang duplikat sekaligus
        $duplicateNoHp = Konsumen::select('no_hp')
            ->groupBy('no_hp')
            ->havingRaw('COUNT(*) > 1')
            ->pluck('no_hp')
            ->toArray();

        return DataTables::of($data)
            ->addColumn('full_kode', fn($d) => $d->full_kode)
            ->addColumn('limit_plafon', fn($d) => $d->nf_plafon)
            ->addColumn('kode_toko', fn($d) => $d->kode_toko->kode ?? '')
            // ->addColumn('nama', fn($d) => $d->nama)
            ->addColumn('cp', function ($d) use ($duplicateNoHp) {
                $noHpWarning = (substr_count($d->no_hp, ' ') > 0 || in_array($d->no_hp, $duplicateNoHp)) ? 'text-danger' : '';
                return "
                    <ul>
                        <li>CP : $d->cp</li>
                        <li class='$noHpWarning'>No.HP : $d->no_hp</li>
                        <li>No.Kantor : $d->no_kantor</li>
                    </ul>
                ";
            })
            ->addColumn('pembayaran_raw', function ($d){
                return "
                    $d->sistem_pembayaran <br>
                    (".($d->pembayaran == 2 ? $d->tempo_hari. ' Hari' : '').")
                ";
            })
            ->addColumn('ktp', function ($d) {
                return view('db.konsumen._ktp', compact('d'))->render();
            })
            ->addColumn('diskon', function ($d){
                return view('db.konsumen._diskon', compact('d'))->render();
            })
            ->addColumn('aksi', function ($d) {
                return view('db.konsumen._aksi', compact('d'))->render();
            })
            ->rawColumns(['cp', 'ktp','aksi', 'pembayaran_raw', 'diskon'])
            ->make(true);
    }

    public function konsumen(Request $request)
    {
        // $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status']); // Ambil filter dari request

        // $data = Konsumen::with(['provinsi', 'kabupaten_kota', 'kecamatan', 'sales_area', 'kode_toko', 'karyawan'])
        //     ->filter($filters) // Gunakan scope filter
        //     // ->limit(10)
        //     ->get();

        $kecamatan_filter = Wilayah::whereIn('id_induk_wilayah', function ($query) {
            $query->select('id_wilayah')
                ->from('wilayahs')
                ->where('id_induk_wilayah', '110000');
        })->where('id_level_wilayah', 3)->get();

        $provinsi = Wilayah::where('id_level_wilayah', 1)->get();

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('db.konsumen.index', [
            // 'data' => $data,
            'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
            'kecamatan_filter' => $kecamatan_filter,
        ]);
    }

    public function konsumen_diskon_khusus(Konsumen $konsumen, Request $request)
    {
        $data = $request->validate([
            'diskon_khusus' => 'required|numeric|min:0|max:100',
        ]);

        $konsumen->update([
            'diskon_khusus' => $data['diskon_khusus'],
        ]);

        return redirect()->back()->with('success', 'Diskon khusus berhasil diubah!');
    }
    
    public function konsumen_upload_ktp(Request $request, Konsumen $konsumen)
    {
        $data = $request->validate([
            'upload_ktp' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:512',
        ]);


        if ($konsumen->upload_ktp && Storage::disk('public')->exists($konsumen->upload_ktp)) {
            Storage::disk('public')->delete($konsumen->upload_ktp);
        }

        $file = $request->file('upload_ktp');
        $filename = $konsumen->id.'_KTP'.'_'.time().'.'.$file->getClientOriginalExtension();

        if (!Storage::disk('public')->exists('konsumen')) {
            Storage::disk('public')->makeDirectory('konsumen');
        }

        $path = $file->storeAs('konsumen', $filename, 'public');
        $data['upload_ktp'] = $path;

        $konsumen->update($data);

        return redirect()->back()->with('success', 'Berhasil mengubah KTP Konsumen!');
    }

    public function konsumen_daftar_kunjungan(Konsumen $konsumen)
    {
        $pt = Config::where('untuk', 'resmi')->first();

        $tahun = date('Y');

        $pdf = PDF::loadview('db.konsumen.daftar-kunjungan', [
            'konsumen' => $konsumen,
            'pt' => $pt,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Daftar Kunjungan - '.$konsumen->nama.' - '.$tahun.'.pdf');
    }

    public function konsumen_store(Request $request)
    {
        $data = $request->validate([
            'kode_toko_id' => 'required|exists:kode_tokos,id',
            'nik' => 'nullable',
            'diskon_khusus' => 'required',
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
            'nik' => 'nullable',
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

    public function kelompok_rute()
    {
        $data = KelompokRute::with(['details.wilayah'])->get();

        return view('db.kelompok-rute.index', [
            'data' => $data,
        ]);
    }

    public function kelompok_rute_store(Request $request)
    {
        // dd($request->all());
        $data = $request->validate([
            'nama' => 'required',
            'wilayah_id' => 'required|array',
            'wilayah_id.*' => 'required|exists:wilayahs,id',
        ]);


        try {
            DB::beginTransaction();

            $kelompok = KelompokRute::create([
                'nama' => $data['nama'],
            ]);

            foreach ($data['wilayah_id'] as $detail) {
                $kelompok->details()->create([
                    'wilayah_id' => $detail,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Data berhasil ditambahkan');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menyimpan data! ', $th->getMessage());
        }
    }

    public function kelompok_rute_update(KelompokRute $kelompok, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required|unique:kelompok_rutes,nama,'.$kelompok->id,
            'wilayah_id' => 'required|array',
            'wilayah_id.*' => 'required|exists:wilayahs,id',
        ]);

        try {
            DB::beginTransaction();

            $kelompok->update([
                'nama' => $data['nama'],
            ]);

            $kelompok->details()->delete();

            foreach ($data['wilayah_id'] as $detail) {
                $kelompok->details()->create([
                    'wilayah_id' => $detail,
                ]);
            }

            DB::commit();

            return redirect()->back()->with('success', 'Data berhasil diupdate');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat mengupdate data! ', $th->getMessage());
        }
    }

    public function kelompok_rute_delete(KelompokRute $kelompok)
    {
        try {
            DB::beginTransaction();
            $kelompok->details()->delete();
            $kelompok->delete();
            DB::commit();

            return redirect()->back()->with('success', 'Data berhasil dihapus');
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return redirect()->back()->with('error', 'Terjadi kesalahan saat menghapus data! ', $th->getMessage());
        }
    }

    public function diskon_umum()
    {
        $data = DiskonUmum::all();

        return view('db.diskon-umum.index', [
            'data' => $data,
        ]);
    }

     public function diskon_umum_update(DiskonUmum $diskon, Request $request)
    {
        $data = $request->validate([
            'persen' => 'required|numeric|min:0|max:100',
        ]);

        $diskon->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }


}
