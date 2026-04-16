<?php

namespace App\Http\Controllers;

use App\Models\Config;
use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangKategori;
use App\Models\db\Barang\BarangNama;
use App\Models\db\Barang\BarangType;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\CostOperational;
use App\Models\db\DiskonUmum;
use App\Models\db\InventarisJenis;
use App\Models\db\InventarisKategori;
use App\Models\db\Jabatan;
use App\Models\db\Karyawan;
use App\Models\db\KelompokRute;
use App\Models\db\KodeToko;
use App\Models\db\Konsumen;
use App\Models\db\KonsumenDoc;
use App\Models\db\KonsumenPlafonHistory;
use App\Models\db\Kreditor;
use App\Models\db\Pajak;
use App\Models\db\SalesArea;
use App\Models\db\Satuan;
use App\Models\db\Supplier;
use App\Models\Pengelola;
use App\Models\transaksi\InvoiceJualDetail;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Ramsey\Uuid\Uuid;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Spatie\SimpleExcel\SimpleExcelWriter;
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

    public function konsumen()
    {
        $kabupatenKotaIds = Konsumen::select('kabupaten_kota_id')->distinct()->pluck('kabupaten_kota_id')->filter()->toArray();

        $kab_filter = Wilayah::whereIn('id', $kabupatenKotaIds)->get();

        $provinsi = Wilayah::where('id_level_wilayah', 1)->get();

        $sales_area = Karyawan::with('jabatan')->whereHas('jabatan', function ($query) {
            $query->where('is_sales', 1);
        })->select('id', 'nama')->get();

        return view('db.konsumen.index', [
            'provinsi' => $provinsi,
            'sales_area' => $sales_area,
            'kab_filter' => $kab_filter,
            'kode_toko' => KodeToko::select('id', 'kode')->get(),
        ]);
    }


    public function konsumen_data(Request $request)
    {
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'kabupaten_kota']); // Ambil filter dari request

        $data = Konsumen::query()->with(['kode_toko', 'provinsi', 'kabupaten_kota', 'kecamatan', 'karyawan'])
                ->withCount('docs')
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
            ->filterColumn('cp', function($query, $keyword) {
                // Gunakan where() dengan closure agar kueri dikelompokkan dengan tanda kurung (...)
                // Ini mencegah orWhere merusak kueri utama kamu.
                $query->where(function($q) use ($keyword) {
                    $q->where('no_hp', 'like', "%{$keyword}%")
                    ->orWhere('cp', 'like', "%{$keyword}%")
                    ->orWhere('no_kantor', 'like', "%{$keyword}%");
                });
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
            ->addColumn('dokumen', function ($d) {
                return view('db.konsumen._dokumen', compact('d'))->render();
            })
            ->addColumn('limit_plafon', function ($d) {
                // Kita passing ID, Nama, dan nilai plafon raw (tanpa format) untuk dimasukkan ke input
                return '
                    <a href="javascript:void(0)"
                    class="text-primary fw-bold text-decoration-none"
                    onclick="openPlafonModal('.$d->id.', \''.addslashes($d->nama).'\', '.$d->plafon.')">
                    '.$d->nf_plafon.' <i class="fa fa-edit ms-1"></i>
                    </a>
                ';
            })
            ->addColumn('checklist_kunjungan', function ($d) {
                $checked = $d->checklist_kunjungan ? 'checked' : '';
                return '
                    <div class="text-center">
                        <input class="form-check-input shadow-none" type="checkbox"
                               onchange="toggleChecklist(' . $d->id . ', this)" ' . $checked . '
                               style="cursor: pointer; transform: scale(2.3);">
                    </div>
                ';
            })
            ->rawColumns(['cp', 'ktp','aksi', 'pembayaran_raw', 'diskon', 'dokumen', 'checklist_kunjungan', 'limit_plafon'])
            ->make(true);
    }

    public function histori_plafon($id)
    {
        $konsumen = Konsumen::findOrFail($id);

        // Mengambil data dari relationship 'plafon_histories'
        $query = $konsumen->plafon_histories()->with('updatedBy')->select('konsumen_plafon_histories.*');

        return DataTables::of($query)
            ->editColumn('created_at', function ($d) {
                return $d->created_at->format('d/m/Y H:i');
            })
            ->editColumn('plafon_lama', function ($d) {
                return 'Rp ' . number_format($d->nominal_lama, 0, ',', '.');
            })
            ->editColumn('plafon_baru', function ($d) {
                return 'Rp ' . number_format($d->nominal_baru, 0, ',', '.');
            })
            ->addColumn('user', function ($d) {
                return $d->updatedBy->name ?? '-';
            })
            ->make(true);
    }

    public function update_plafon(Request $request, $id)
    {
        $request->validate([
            'plafon' => 'required',
            // 'keterangan' => 'nullable|string|max:255',
        ]);

        // Bersihkan format titik dari Cleave.js (contoh: 1.000.000 -> 1000000)
        $plafonBaru = str_replace('.', '', $request->plafon);

        DB::beginTransaction();
        try {
            $konsumen = Konsumen::findOrFail($id);
            $plafonLama = $konsumen->plafon;

            if ($plafonLama == $plafonBaru) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Nilai plafon sama.'
                ]);
            }

            // 1. Update plafon di tabel konsumen
            $konsumen->update([
                'plafon' => $plafonBaru
            ]);

            // 2. Simpan ke tabel histori
            KonsumenPlafonHistory::create([
                'konsumen_id' => $id,
                'nominal_lama' => $plafonLama,
                'nominal_baru' => $plafonBaru,
                // 'keterangan' => $request->keterangan ?? 'Perubahan limit plafon',
                'updated_by' => Auth::id(), // Mencatat siapa yang mengubah
            ]);

            DB::commit();

            return response()->json([
                'status' => 'success',
                'message' => 'Limit plafon berhasil diperbarui.'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    public function toggleChecklist(Request $request, $id)
    {
        $konsumen = Konsumen::findOrFail($id);
        $konsumen->checklist_kunjungan = filter_var($request->status, FILTER_VALIDATE_BOOLEAN);
        $konsumen->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status checklist kunjungan berhasil diperbarui.'
        ]);
    }

    public function konsumen_dokumen(Request $request)
    {
        $konsumen = Konsumen::withCount('docs')->find($request->konsumen_id);

        if (!$konsumen) {
            return response()->json(['status' => 'error', 'message' => 'Konsumen tidak ditemukan'], 404);
        }

        $docs = $konsumen->docs->load('barang_unit');

        return response()->json([
            'status' => 'success',
            'data' => $docs,
            'konsumen' => $konsumen,
        ]);
    }

    public function konsumen_dokumen_destroy(KonsumenDoc $dokumen)
    {
        try {
            DB::beginTransaction();

            if (Storage::disk('public')->exists($dokumen->file_path)) {
                Storage::disk('public')->delete($dokumen->file_path);
            }

            $dokumen->delete();

            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil dihapus']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menghapus dokumen. '. $e->getMessage() ], 500);
        }
    }

    public function konsumen_dokumen_store(Request $request)
    {
        $data = $request->validate([
            'konsumen_id' => 'required|exists:konsumens,id',
            'nama' => 'required|string|max:255',
            'barang_unit_id' => 'nullable|exists:barang_units,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Maks 5MB
        ]);

        if ($request->hasFile('file')) {
            $file = $request->file('file');
            $filename = $data['konsumen_id'].'_'.$data['nama'].'_'.time().'.'.$file->getClientOriginalExtension();

            if (!Storage::disk('public')->exists('konsumen_docs')) {
                Storage::disk('public')->makeDirectory('konsumen_docs');
            }

            $path = $file->storeAs('konsumen_docs', $filename, 'public');
            $data['file_path'] = $path;
            unset($data['file']);
        }

        DB::beginTransaction();
        try {
            KonsumenDoc::create($data);
            DB::commit();

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil diunggah']);
        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json(['status' => 'error', 'message' => 'Terjadi kesalahan saat menyimpan dokumen. '. $e->getMessage() ], 500);
        }
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
            // 'plafon' => 'required',
            'tempo_hari' => 'required',
            'karyawan_id' => 'required|exists:karyawans,id',
        ]);

        // $data['plafon'] = str_replace('.', '', $data['plafon']);
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

        $unit = BarangUnit::get();

        return view('db.supplier.index', [
            'data' => $data,
            'unit' => $unit
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
            'barang_unit_id' => 'required|exists:barang_units,id',
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
            'barang_unit_id' => 'required|exists:barang_units,id',
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

    // Tambahkan di dalam DatabaseController class

    public function getFilterOptions(Request $request)
    {
        // Ambil parameter filter saat ini
        $unitId = $request->input('unit_id');
        $bidangId = $request->input('bidang_id');
        $kategoriId = $request->input('kategori_id');

        // 1. Logic untuk Bidang (BarangType)
        // Bidang selalu bergantung pada Unit (relation: belongsTo unit)
        $bidangQuery = BarangType::query();
        if ($unitId) {
            $bidangQuery->where('barang_unit_id', $unitId);
        }
        $bidangOptions = $bidangQuery->orderBy('nama')->get(['id', 'nama']);

        // 2. Logic untuk Kategori (BarangKategori)
        // Karena tidak ada relasi langsung Unit->Kategori, kita cari kategori
        // yang DIGUNAKAN oleh barang-barang dengan filter unit/bidang tersebut.
        $kategoriQuery = BarangKategori::query();

        if ($unitId || $bidangId) {
            $kategoriQuery->whereHas('barangs', function($q) use ($unitId, $bidangId) {
                $q->where('is_active', 1); // Opsional: hanya yang aktif
                if ($unitId) $q->where('barang_unit_id', $unitId);
                if ($bidangId) $q->where('barang_type_id', $bidangId);
            });
        }
        // Jika tidak ada filter, tampilkan semua kategori (atau batasi sesuai kebutuhan)
        $kategoriOptions = $kategoriQuery->orderBy('nama')->get(['id', 'nama']);

        // 3. Logic untuk Nama Barang (BarangNama)
        // Bergantung pada Kategori
        $namaQuery = BarangNama::query();
        if ($kategoriId) {
            $namaQuery->where('barang_kategori_id', $kategoriId);
        }
        // Jika unit/bidang dipilih, pastikan Nama Barang tersebut memang ada di unit/bidang itu (via relasi Barang)
        if ($unitId || $bidangId) {
            $namaQuery->whereHas('barang', function($q) use ($unitId, $bidangId) {
                $q->where('is_active', 1);
                if ($unitId) $q->where('barang_unit_id', $unitId);
                if ($bidangId) $q->where('barang_type_id', $bidangId);
            });
        }

        $namaOptions = $namaQuery->orderBy('nama')->get(['id', 'nama']);

        return response()->json([
            'bidang' => $bidangOptions,
            'kategori' => $kategoriOptions,
            'nama' => $namaOptions
        ]);
    }

    public function order(Request $request)
    {
        $selectUnit = BarangUnit::select('id', 'nama')->get();
        $selectBidang = BarangType::select('id', 'nama')->get();
        $selectKategori = BarangKategori::all();
        $selectBarangNama = BarangNama::select('id', 'nama')->distinct()->orderBy('id')->get();
        return view('db.order.index', compact('selectKategori', 'selectBarangNama', 'selectUnit', 'selectBidang'));
    }

    public function order_data(Request $request)
    {
        // 0. Ambil Input Multiplier (Default 2 jika kosong)
        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            // Pastikan angka untuk keamanan
            $multiplier = (float) $multiplier;
        }

        // 1. Definisikan Subquery untuk 'stok_ready'
        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        // 2. Definisikan Subquery untuk 'avg_sales'
        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        // 3. QUERY UTAMA
        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        // 4. FILTERING STANDAR
        if ($request->filled('unit')) {
            $query->where('barangs.barang_unit_id', $request->input('unit'));
        }
        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        // 5. FILTER KHUSUS (Menggunakan Variabel $multiplier)
        // Rumus: (Avg * Multiplier) - Stok > 0
        // Karena kita inject variable ke string SQL, pastikan variable aman (sudah di-cast float diatas)
        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // 6. DATATABLES CONFIG
        return DataTables::of($query)
            ->addIndexColumn()
            ->addColumn('barang_ppn', function ($row) {
                return $row->jenis == 1 ? 'Ya' : 'Tidak';
            })
            // Kolom Stok
            ->addColumn('stok_info', function ($row) {
                return number_format($row->stok_ready, 0, ',', '.');
            })
            ->orderColumn('stok_info', 'stok_ready $1')

            // Kolom Avg
            ->addColumn('avg_penjualan', function ($row) {
                return number_format($row->avg_sales, 0, ',', '.');
            })
            ->orderColumn('avg_penjualan', 'avg_sales $1')

            // Kolom Saran Order (Dinamis berdasarkan multiplier)
            ->addColumn('saran_order', function ($row) use ($multiplier) {
                return number_format($row->avg_sales * $multiplier, 0, ',', '.');
            })
            ->orderColumn('saran_order', "(avg_sales * {$multiplier}) $1")

            // Kolom Order Qty (Dinamis berdasarkan multiplier)
            ->addColumn('order_qty', function ($row) use ($multiplier) {
                $qty = ($row->avg_sales * $multiplier) - $row->stok_ready;
                return number_format($qty, 0, ',', '.');
            })
            ->orderColumn('order_qty', "((avg_sales * {$multiplier}) - stok_ready) $1")

            ->rawColumns(['nama_barang'])
            ->make(true);
    }

    public function order_export_pdf(Request $request)
    {
        $request->validate([
            'unit' => 'required|exists:barang_units,id'
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        ]);
        // --- LOGIKA QUERY SAMA PERSIS DENGAN ORDER_DATA ---
        // Kita ulangi query builder disini agar hasil PDF sama persis dengan tabel

        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            $multiplier = (float) $multiplier;
        }

        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->orderBy('barangs.barang_nama_id', 'asc')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        if ($request->filled('unit')) {
            $query->where('barangs.barang_unit_id', $request->input('unit'));
        }
        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // Urutkan default (misal berdasarkan Nama Barang atau Order Qty terbesar)
        $query->orderByRaw("((avg_sales * {$multiplier}) - stok_ready) DESC");

        // Ambil Data (Get, bukan DataTables)
        $data = $query->get();

        if (count($data) == 0) {
            return redirect()->back()->with('error', 'Tidak ada data untuk diekspor. Silahkan sesuaikan filter Anda.');
        }

        $perusahaan = BarangUnit::find($request->input('unit'));
        // Load View PDF
        $pdf = Pdf::loadView('db.order.pdf', compact('data', 'multiplier', 'perusahaan'));

        // Set Paper Size (Optional)
        $pdf->setPaper('a4', 'landscape');

        return $pdf->stream('laporan_saran_order.pdf');
    }

    // Tambahkan method ini di dalam class DatabaseController
    public function export_excel(Request $request)
    {
        $request->validate([
            'unit' => 'required|exists:barang_units,id'
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid'
        ]);
        // set_time_limit(0);
        // ini_set('memory_limit', '-1');
        if (ob_get_contents()) ob_end_clean();

        $multiplier = $request->input('multiplier');
        if(is_null($multiplier) || $multiplier === '') {
            $multiplier = 2;
        } else {
            $multiplier = (float) $multiplier;
        }

        $stokSubquery = DB::table('barang_stok_hargas')
            ->selectRaw('COALESCE(SUM(stok), 0)')
            ->whereColumn('barang_id', 'barangs.id')
            ->where('stok', '>', 0);

        $avgSubquery = DB::table('invoice_jual_details as ijd')
            ->join('invoice_juals as ij', 'ijd.invoice_jual_id', '=', 'ij.id')
            ->whereColumn('ijd.barang_id', 'barangs.id')
            ->where('ij.void', 0)
            ->selectRaw('COALESCE(SUM(ijd.jumlah), 0) / GREATEST(TIMESTAMPDIFF(MONTH, MIN(ij.created_at), NOW()), 1)');

        $query = Barang::with(['unit', 'type', 'barang_nama', 'satuan', 'kategori'])
            ->where('barangs.is_active', 1)
            ->select('barangs.*')
            ->orderBy('barangs.barang_nama_id', 'asc')
            ->selectSub($stokSubquery, 'stok_ready')
            ->selectSub($avgSubquery, 'avg_sales')
            ->leftJoin('barang_namas', 'barangs.barang_nama_id', '=', 'barang_namas.id')
            ->leftJoin('barang_kategoris', 'barangs.barang_kategori_id', '=', 'barang_kategoris.id')
            ->leftJoin('barang_types', 'barangs.barang_type_id', '=', 'barang_types.id')
            ->leftJoin('barang_units', 'barangs.barang_unit_id', '=', 'barang_units.id')
            ->orderBy('barang_units.nama', 'asc')
            ->orderBy('barang_types.nama', 'asc')
            ->orderBy('barang_kategoris.nama', 'asc')
            ->orderBy('barang_namas.nama', 'asc');

        if ($request->filled('unit')) {
            $query->where('barangs.barang_unit_id', $request->input('unit'));
        }
        if ($request->filled('bidang')) {
            $query->where('barangs.barang_type_id', $request->input('bidang'));
        }
        if ($request->filled('kategori')) {
            $query->where('barangs.barang_kategori_id', $request->input('kategori'));
        }
        if ($request->filled('barang_nama')) {
            $query->where('barangs.barang_nama_id', $request->input('barang_nama'));
        }
        if ($request->filled('jenis')) {
            $query->where('barangs.jenis', $request->input('jenis'));
        }

        $query->havingRaw("((avg_sales * {$multiplier}) - stok_ready) >= 1");

        // Urutkan default (misal berdasarkan Nama Barang atau Order Qty terbesar)
        $query->orderByRaw("((avg_sales * {$multiplier}) - stok_ready) DESC");

        $writer = SimpleExcelWriter::streamDownload('saran_order_'.date('Y-m-d_H-i').'.xlsx');

        $query->cursor()->each(function ($row) use ($writer, $multiplier) {
            $saranOrder = ($row->avg_sales * $multiplier) - $row->stok_ready;
            $saranOrder = max(0, round($saranOrder));

            $writer->addRow([
                'Perusahaan'   => $row->unit?->nama,
                'Bidang'       => $row->type?->nama,
                'Kategori'     => $row->kategori?->nama,
                'Nama Barang'  => $row->barang_nama?->nama,
                'Kode'         => $row->kode,
                'Merk'         => $row->merk,
                'Jenis'        => $row->jenis == 1 ? 'PPN' : ($row->jenis == 2 ? 'Non PPN' : '-'),
                'Stok Saat Ini'=> (float) $row->stok_ready,
                'Satuan'       => $row->satuan?->nama,
                'Rata2 Jual'   => round($row->avg_sales),
                'Saran Order'  => $saranOrder,
            ]);

            if (ob_get_length() > 0) {
                ob_flush();
                flush();
            }
        });

        exit;
    }


}
