<?php

namespace App\Http\Controllers;

use App\Http\Requests\CustomerBiodataRequest;
use App\Http\Requests\UpdateCustomerSalesRequest;
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
use App\Models\MetodeBayar;
use App\Models\Pengelola;
use App\Models\User;
use App\Models\Wilayah;
use App\Services\CustomerFileService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Ramsey\Uuid\Uuid;
use Spatie\SimpleExcel\SimpleExcelWriter;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
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
        if ($jabatan->isProtectedSales()) {
            return back()->with('error', 'Nama jabatan Sales dikunci karena digunakan pada proses operasional dan aturan komisi.');
        }

        $data = $req->validate([
            'nama' => 'required',
        ]);

        $jabatan->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }

    public function jabatan_delete(Jabatan $jabatan)
    {
        if ($jabatan->isProtectedSales()) {
            return back()->with('error', 'Jabatan Sales merupakan jabatan sistem dan tidak dapat dihapus.');
        }

        if ($jabatan->karyawan()->exists()) {
            return back()->with('error', 'Jabatan masih digunakan pegawai dan tidak dapat dihapus.');
        }
        $jabatan->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function staff(Request $request)
    {
        $filters = $request->validate(['status' => 'nullable|in:1,0,all', 'jabatan_id' => 'nullable|integer|exists:jabatans,id']);
        $status = $filters['status'] ?? '1';
        $query = Karyawan::with('jabatan');
        if ($status !== 'all') {
            $query->where('status', (int) $status);
        }
        if (! empty($filters['jabatan_id'])) {
            $query->where('jabatan_id', $filters['jabatan_id']);
        }
        if ($request->ajax()) {
            $data = $request->validate(['draw' => 'required|integer|min:0', 'start' => 'required|integer|min:0', 'length' => 'required|integer|in:10,25,50,100', 'search.value' => 'nullable|string|max:100', 'order.0.column' => 'nullable|integer|between:0,7', 'order.0.dir' => 'nullable|in:asc,desc']);
            $total = (clone $query)->count();
            $search = trim($data['search']['value'] ?? '');
            if ($search !== '') {
                $query->where(function ($query) use ($search): void {
                    $query->where('nama', 'like', '%'.$search.'%')->orWhere('nickname', 'like', '%'.$search.'%')
                        ->orWhereHas('jabatan', fn ($position) => $position->where('nama', 'like', '%'.$search.'%'));
                });
            }
            $filtered = $search === '' ? $total : (clone $query)->count();
            $columns = ['nomor', 'nama', 'nickname', 'jabatan_id', 'bank', 'status', 'id', 'id'];
            $rows = $query->orderBy($columns[$data['order'][0]['column'] ?? 1], $data['order'][0]['dir'] ?? 'asc')->orderBy('id')
                ->offset($data['start'])->limit($data['length'])->get()->map(fn ($employee) => [
                    e((string) $employee->nomor), e($employee->nama), e($employee->nickname), e($employee->jabatan?->nama ?? '—'),
                    view('db.karyawan.bank', ['employee' => $employee])->render(),
                    $employee->status ? '<span class="badge bg-success">Aktif</span>' : '<span class="badge bg-secondary">Nonaktif</span>',
                    view('db.karyawan.documents', ['employee' => $employee])->render(),
                    view('db.karyawan.actions', ['employee' => $employee])->render(),
                ]);

            return response()->json(['draw' => (int) $data['draw'], 'recordsTotal' => $total, 'recordsFiltered' => $filtered, 'data' => $rows]);
        }

        return view('db.karyawan.index', ['status' => $status, 'selectedJabatan' => $filters['jabatan_id'] ?? null, 'jabatan' => Jabatan::orderBy('nama')->get()]);
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
        foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'tunjangan_bbm'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => str_replace('.', '', $request->input($field))]);
            }
        }
        $isSales = Jabatan::find($request->integer('jabatan_id'))?->isSales() ?? false;
        $data = $request->validate([
            'persen_komisi' => ($isSales ? 'required' : 'nullable').'|numeric|between:0,100|decimal:0,2',
            'tunjangan_bbm' => ($isSales ? 'required' : 'nullable').'|integer|between:0,2147483647',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nama' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'gaji_pokok' => 'required|integer|between:0,2147483647',
            'tunjangan_jabatan' => 'required|integer|between:0,2147483647',
            'tunjangan_keluarga' => 'required|integer|between:0,2147483647',
            'nik' => 'required|string|max:255',
            'npwp' => 'required|string|max:255',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kes' => 'nullable',
            'bpjs_tk' => 'required|string|max:255',
            'bpjs_kesehatan' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date_format:Y-m-d',
            'alamat' => 'required',
            'no_hp' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
            'no_rek' => 'required|string|max:255',
            'nama_rek' => 'required|string|max:255',
            'mulai_bekerja' => 'required|date_format:Y-m-d',
            'perjanjian_kerja' => 'nullable|file|mimes:pdf|extensions:pdf|max:5120',
            'foto_ktp' => 'required|image|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'required|image|mimes:jpg,jpeg,png|max:10000',
            'status' => 'required|boolean',
        ]);

        $data['nomor'] = Karyawan::max('nomor') + 1;

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['persen_komisi'] = $isSales ? $data['persen_komisi'] : null;
        $data['tunjangan_bbm'] = $isSales ? $data['tunjangan_bbm'] : null;
        $data['apa_bpjs_tk'] = $request->boolean('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kes'] = $request->boolean('apa_bpjs_kes') ? 1 : 0;

        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        try {
            DB::beginTransaction();
            $file_name_ktp = Uuid::uuid4().'- KTP - '.$data['nama'].'.'.$request->foto_ktp->extension();
            $file_name_diri = Uuid::uuid4().' - Foto Diri '.$data['nama'].'.'.$request->foto_diri->extension();

            $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
            $newPhotoPaths[] = $data['foto_ktp'];
            $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
            $newPhotoPaths[] = $data['foto_diri'];

            if ($request->hasFile('perjanjian_kerja')) {
                $data['perjanjian_kerja'] = $request->file('perjanjian_kerja')->store('karyawan/perjanjian-kerja', 'local');
                $newPhotoPaths[] = $data['perjanjian_kerja'];
            }

            Karyawan::create($data);

            DB::commit();
            foreach (array_filter($oldPhotoPaths) as $path) {
                Storage::disk('local')->delete($path);
            }

            return redirect()->route('db.staff')->with('success', 'Data berhasil ditambahkan');
        } catch (\Exception $e) {
            DB::rollBack();
            foreach ($newPhotoPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

    }

    public function staff_show(Karyawan $staff): View
    {
        return view('db.karyawan.show', ['employee' => $staff->load('jabatan')]);
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
        foreach (['gaji_pokok', 'tunjangan_jabatan', 'tunjangan_keluarga', 'tunjangan_bbm'] as $field) {
            if (is_string($request->input($field))) {
                $request->merge([$field => str_replace('.', '', $request->input($field))]);
            }
        }
        $isSales = Jabatan::find($request->integer('jabatan_id'))?->isSales() ?? false;
        $data = $request->validate([
            'persen_komisi' => ($isSales ? 'required' : 'nullable').'|numeric|between:0,100|decimal:0,2',
            'tunjangan_bbm' => ($isSales ? 'required' : 'nullable').'|integer|between:0,2147483647',
            'jabatan_id' => 'required|exists:jabatans,id',
            'nama' => 'required|string|max:255',
            'nickname' => 'required|string|max:255',
            'gaji_pokok' => 'required|integer|between:0,2147483647',
            'tunjangan_jabatan' => 'required|integer|between:0,2147483647',
            'tunjangan_keluarga' => 'required|integer|between:0,2147483647',
            'nik' => 'required|string|max:255',
            'npwp' => 'required|string|max:255',
            'apa_bpjs_tk' => 'nullable',
            'apa_bpjs_kes' => 'nullable',
            'bpjs_tk' => 'required|string|max:255',
            'bpjs_kesehatan' => 'required|string|max:255',
            'tempat_lahir' => 'required|string|max:255',
            'tanggal_lahir' => 'required|date_format:Y-m-d',
            'alamat' => 'required',
            'no_hp' => 'required|string|max:255',
            'bank' => 'required|string|max:255',
            'no_rek' => 'required|string|max:255',
            'nama_rek' => 'required|string|max:255',
            'mulai_bekerja' => 'required|date_format:Y-m-d',
            'status' => 'required|boolean',
            'perjanjian_kerja' => 'nullable|file|mimes:pdf|extensions:pdf|max:5120',
            'foto_ktp' => 'nullable|image|mimes:jpg,jpeg,png|max:10000',
            'foto_diri' => 'nullable|image|mimes:jpg,jpeg,png|max:10000',
        ]);

        $data['gaji_pokok'] = str_replace('.', '', $data['gaji_pokok']);
        $data['tunjangan_jabatan'] = str_replace('.', '', $data['tunjangan_jabatan']);
        $data['tunjangan_keluarga'] = str_replace('.', '', $data['tunjangan_keluarga']);

        $data['persen_komisi'] = $isSales ? $data['persen_komisi'] : null;
        $data['tunjangan_bbm'] = $isSales ? $data['tunjangan_bbm'] : null;
        $data['apa_bpjs_tk'] = $request->boolean('apa_bpjs_tk') ? 1 : 0;
        $data['apa_bpjs_kes'] = $request->boolean('apa_bpjs_kes') ? 1 : 0;

        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        try {
            DB::beginTransaction();

            if ($request->hasFile('foto_ktp')) {
                $file_name_ktp = Uuid::uuid4().'- KTP - '.$data['nama'].'.'.$request->foto_ktp->extension();
                $data['foto_ktp'] = $request->file('foto_ktp')->storeAs('public/karyawan', $file_name_ktp);
                $newPhotoPaths[] = $data['foto_ktp'];
                $oldPhotoPaths[] = $staff->foto_ktp;
            }

            if ($request->hasFile('foto_diri')) {
                $file_name_diri = Uuid::uuid4().' - Foto Diri '.$data['nama'].'.'.$request->foto_diri->extension();
                $data['foto_diri'] = $request->file('foto_diri')->storeAs('public/karyawan', $file_name_diri);
                $newPhotoPaths[] = $data['foto_diri'];
                $oldPhotoPaths[] = $staff->foto_diri;
            }

            unset($data['perjanjian_kerja']);
            if ($request->hasFile('perjanjian_kerja')) {
                $data['perjanjian_kerja'] = $request->file('perjanjian_kerja')->store('karyawan/perjanjian-kerja', 'local');
                $newPhotoPaths[] = $data['perjanjian_kerja'];
                $oldPhotoPaths[] = $staff->perjanjian_kerja;
            }

            $staff->update($data);

            DB::commit();
            foreach (array_filter($oldPhotoPaths) as $path) {
                Storage::disk('local')->delete($path);
            }

        } catch (\Throwable $th) {
            // throw $th;

            DB::rollBack();
            foreach ($newPhotoPaths as $path) {
                Storage::disk('local')->delete($path);
            }

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

        return redirect()->route('db.staff')->with('success', 'Data berhasil diupdate');
    }

    public function staff_document(Karyawan $staff, string $document): BinaryFileResponse
    {
        abort_unless(in_array($document, ['foto_ktp', 'foto_diri', 'perjanjian_kerja'], true), 404);
        $path = $staff->getAttribute($document);
        abort_unless($path && Storage::disk('local')->exists($path), 404);

        return response()->file(Storage::disk('local')->path($path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function staff_status(Request $request, Karyawan $staff)
    {
        $data = $request->validate(['status' => 'required|boolean']);
        $staff->update(['status' => (bool) $data['status']]);

        return redirect()->route('db.staff')->with('success', $data['status'] ? 'Pegawai berhasil diaktifkan kembali.' : 'Pegawai dinonaktifkan. Riwayat tetap tersimpan dan akses akun web terkait diblokir.');
    }

    public function staff_delete(Request $request, Karyawan $staff)
    {
        $request->validate(['deletion_reason' => 'required|string|min:5|max:500']);
        $newPhotoPaths = [];
        $oldPhotoPaths = [];
        try {
            DB::beginTransaction();
            $staff = Karyawan::whereKey($staff->id)->lockForUpdate()->firstOrFail();
            if ($reason = $staff->deletionBlockReason()) {
                DB::rollBack();

                return redirect()->back()->with('error', $reason);
            }
            $oldPhotoPaths = [$staff->foto_ktp, $staff->foto_diri, $staff->perjanjian_kerja];

            $staff->delete();

            DB::commit();
            foreach (array_filter($oldPhotoPaths) as $path) {
                Storage::disk('local')->delete($path);
            }

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();
            foreach ($newPhotoPaths as $path) {
                Storage::disk('local')->delete($path);
            }

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
            'kode' => ['required', 'string', 'max:255', 'not_regex:/[<>]/', Rule::unique('kode_tokos', 'kode')],
        ]);

        KodeToko::create($data);

        return response()->json(['message' => 'Data berhasil disimpan']);
    }

    public function kode_toko_update(KodeToko $kode, Request $request)
    {
        $data = $request->validate([
            'kode' => ['required', 'string', 'max:255', 'not_regex:/[<>]/', Rule::unique('kode_tokos', 'kode')->ignore($kode->id)],
        ]);

        $kode->update($data);

        return response()->json(['message' => 'Data berhasil diubah']);
    }

    public function kode_toko_delete(KodeToko $kode): JsonResponse
    {
        DB::transaction(function () use ($kode): void {
            $locked = KodeToko::whereKey($kode->id)->lockForUpdate()->firstOrFail();
            if ($locked->konsumen()->exists()) {
                throw ValidationException::withMessages(['kode' => 'Kode toko masih digunakan konsumen dan tidak dapat dihapus.']);
            }
            $locked->delete();
        }, 3);

        return response()->json(['message' => 'Data berhasil dihapus']);
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

        $sales_area = Karyawan::where('status', 1)->whereHas('jabatan', function ($query) {
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
        $filters = $request->only(['area', 'kecamatan', 'kode_toko', 'status', 'kabupaten_kota', 'status_wa']); // Ambil filter dari request

        $data = Konsumen::query()->with(['kode_toko', 'provinsi', 'kabupaten_kota', 'kecamatan', 'karyawan'])
            ->withCount('docs')
            ->filter($filters);

        $data->selectSub(function ($query): void {
            $query->from('konsumens as duplicate')->selectRaw('COUNT(*)')->whereColumn('duplicate.no_hp', 'konsumens.no_hp');
        }, 'phone_matches');

        return DataTables::of($data)
            ->addColumn('full_kode', fn ($d) => $d->full_kode)
            ->addColumn('limit_plafon', fn ($d) => $d->nf_plafon)
            ->addColumn('kode_toko', fn ($d) => $d->kode_toko->kode ?? '')
            // ->addColumn('nama', fn($d) => $d->nama)
            ->addColumn('cp', function ($d) {
                $noHpWarning = (substr_count($d->no_hp, ' ') > 0 || $d->phone_matches > 1) ? 'text-danger' : '';

                return '<ul class="customer-contact"><li>CP: '.e($d->cp).'</li><li class="'.$noHpWarning.'">HP: '.e($d->no_hp).'</li><li>Telepon: '.e($d->no_kantor ?? '-').'</li></ul>';
            })
            ->filterColumn('cp', function ($query, $keyword) {
                // Gunakan where() dengan closure agar kueri dikelompokkan dengan tanda kurung (...)
                // Ini mencegah orWhere merusak kueri utama kamu.
                $query->where(function ($q) use ($keyword) {
                    $q->where('no_hp', 'like', "%{$keyword}%")
                        ->orWhere('cp', 'like', "%{$keyword}%")
                        ->orWhere('no_kantor', 'like', "%{$keyword}%");
                });
            })
            ->addColumn('pembayaran_raw', function ($d) {
                return "
                    $d->sistem_pembayaran <br>
                    (".($d->pembayaran == 2 ? $d->tempo_hari.' Hari' : '').')
                ';
            })
            ->addColumn('sales_area', fn ($d) => '<button type="button" class="btn btn-link customer-sales-history" data-sales-id="'.$d->karyawan_id.'" data-sales-name="'.e($d->karyawan?->nama ?? 'Belum ditentukan').'" data-id="'.$d->id.'" data-name="'.e(trim(($d->kode_toko?->kode ?? '').' '.$d->nama).' ('.$d->full_kode.')').'">'.e($d->karyawan?->nama ?? 'Belum ditentukan').' <i class="fa fa-history" aria-hidden="true"></i></button>')
            ->addColumn('ktp', function ($d) {
                return view('db.konsumen._ktp', compact('d'))->render();
            })
            ->addColumn('diskon', function ($d) {
                return view('db.konsumen._diskon', compact('d'))->render();
            })
            ->addColumn('aksi', function ($d) {
                return view('db.konsumen._aksi', compact('d'))->render();
            })
            ->addColumn('dokumen', function ($d) {
                return view('db.konsumen._dokumen', compact('d'))->render();
            })
            ->addColumn('limit_plafon', function ($d) {
                return '<button type="button" class="btn btn-link customer-plafon" data-id="'.$d->id.'" data-name="'.e($d->nama).'" data-limit="'.(int) $d->plafon.'">'.$d->nf_plafon.' <i class="fa fa-pencil" aria-hidden="true"></i></button>';
            })
            ->addColumn('checklist_kunjungan', function ($d) {
                $checked = $d->checklist_kunjungan ? 'checked' : '';

                return '
                    <div class="text-center">
                        <input class="form-check-input shadow-none" type="checkbox"
                               onchange="toggleChecklist('.$d->id.', this)" '.$checked.'
                               style="cursor: pointer; transform: scale(2.3);">
                    </div>
                ';
            })
            ->addColumn('notif', function ($d) {
                $checked = $d->wa_notif ? 'checked' : '';

                return '
                    <div class="text-center">
                        <input class="form-check-input shadow-none" type="checkbox"
                               onchange="toggleNotif('.$d->id.', this)" '.$checked.'
                               style="cursor: pointer; transform: scale(2.3);">
                    </div>
                ';
            })
            ->rawColumns(['sales_area', 'cp', 'ktp', 'aksi', 'pembayaran_raw', 'diskon', 'dokumen', 'checklist_kunjungan', 'limit_plafon', 'notif'])->removeColumn('phone_matches')
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
                return 'Rp '.number_format($d->nominal_lama, 0, ',', '.');
            })
            ->editColumn('plafon_baru', function ($d) {
                return 'Rp '.number_format($d->nominal_baru, 0, ',', '.');
            })
            ->addColumn('user', function ($d) {
                return $d->updatedBy->name ?? '-';
            })
            ->make(true);
    }

    public function update_plafon(Request $request, int $id): JsonResponse
    {
        $data = $request->validate(['plafon' => 'required|integer|min:0|max:2147483647', 'keterangan' => 'nullable|string|max:500']);
        $changed = DB::transaction(function () use ($data, $id): bool {
            $customer = Konsumen::whereKey($id)->lockForUpdate()->firstOrFail();
            $old = (int) $customer->plafon;
            $new = (int) $data['plafon'];
            if ($old === $new) {
                return false;
            }
            $customer->update(['plafon' => $new]);
            KonsumenPlafonHistory::create(['konsumen_id' => $id, 'nominal_lama' => $old, 'nominal_baru' => $new, 'updated_by' => Auth::id(), 'keterangan' => $data['keterangan'] ?? null]);

            return true;
        }, 3);

        return response()->json(['status' => 'success', 'message' => $changed ? 'Limit plafon berhasil diperbarui.' : 'Nilai plafon tidak berubah.']);
    }

    public function toggleChecklist(Request $request, $id)
    {
        $konsumen = Konsumen::findOrFail($id);
        $data = $request->validate(['status' => 'required|boolean']);
        $konsumen->checklist_kunjungan = (bool) $data['status'];
        $konsumen->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status checklist kunjungan berhasil diperbarui.',
        ]);
    }

    public function toggleNotif(Request $request, $id)
    {
        $konsumen = Konsumen::findOrFail($id);
        $data = $request->validate(['status' => 'required|boolean']);
        $konsumen->wa_notif = (bool) $data['status'];
        $konsumen->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Status whatsapp notifikasi berhasil diperbarui.',
        ]);
    }

    public function konsumen_dokumen(Request $request)
    {
        $input = $request->validate(['konsumen_id' => ['required', 'integer']]);
        $konsumen = Konsumen::withCount('docs')->find($input['konsumen_id']);

        if (! $konsumen) {
            return response()->json(['status' => 'error', 'message' => 'Konsumen tidak ditemukan'], 404);
        }

        $docs = $konsumen->docs->load('barang_unit');

        return response()->json([
            'status' => 'success',
            'data' => $docs,
            'konsumen' => $konsumen,
        ]);
    }

    public function konsumen_dokumen_destroy(KonsumenDoc $dokumen, CustomerFileService $files)
    {
        try {
            $files->deleteDocument($dokumen);

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil dihapus']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error', 'message' => 'Dokumen belum dapat dihapus. Silakan coba lagi.'], 500);
        }
    }

    public function konsumen_dokumen_store(Request $request, CustomerFileService $files)
    {
        $data = $request->validate([
            'konsumen_id' => 'required|integer|exists:konsumens,id',
            'nama' => 'required|string|max:255',
            'barang_unit_id' => 'nullable|integer|exists:barang_units,id',
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png|max:5120', // Maks 5MB
        ]);

        unset($data['file']);
        try {
            $files->storeDocument($data, $request->file('file'));

            return response()->json(['status' => 'success', 'message' => 'Dokumen berhasil diunggah']);
        } catch (\Throwable $exception) {
            report($exception);

            return response()->json(['status' => 'error', 'message' => 'Dokumen belum dapat disimpan. Silakan coba lagi.'], 500);
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

    public function konsumen_upload_ktp(Request $request, Konsumen $konsumen, CustomerFileService $files): RedirectResponse
    {
        $request->validate(['upload_ktp' => 'required|image|mimes:jpeg,png,jpg|max:512']);
        $files->replaceKtp($konsumen, $request->file('upload_ktp'));

        return back()->with('success', 'KTP konsumen berhasil diperbarui.');
    }

    public function konsumen_daftar_kunjungan(Konsumen $konsumen)
    {
        $pt = Config::where('untuk', 'resmi')->first();

        $tahun = date('Y');

        $pdf = Pdf::loadview('db.konsumen.daftar-kunjungan', [
            'konsumen' => $konsumen,
            'pt' => $pt,
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('Daftar Kunjungan - '.$konsumen->nama.' - '.$tahun.'.pdf');
    }

    public function konsumen_store(CustomerBiodataRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ((int) $data['pembayaran'] === 1) {
            $data['plafon'] = 0;
            $data['tempo_hari'] = 0;
        }
        DB::transaction(function () use ($data): void {
            $last = Konsumen::orderByDesc('kode')->lockForUpdate()->first(['kode']);
            $data['kode'] = ($last?->kode ?? 0) + 1;
            Konsumen::create($data);
        }, 3);

        return to_route('db.konsumen')->with('success', 'Konsumen berhasil ditambahkan.');
    }

    public function histori_sales(Konsumen $konsumen): JsonResponse
    {
        return DataTables::of(DB::table('konsumen_sales_histories')->where('konsumen_id', $konsumen->id))
            ->editColumn('created_at', fn ($history) => Carbon::parse($history->created_at)->format('d/m/Y H:i'))
            ->toJson();
    }

    public function konsumen_update(Konsumen $konsumen, CustomerBiodataRequest $request): RedirectResponse
    {
        $data = $request->validated();
        if ((int) $data['pembayaran'] === 1) {
            $data['tempo_hari'] = 0;
        }
        $reason = $data['sales_change_reason'] ?? null;
        $expectedSales = $data['_original_sales_id'];
        unset($data['sales_change_reason'], $data['_original_sales_id']);
        DB::transaction(function () use ($konsumen, $request, $data, $reason, $expectedSales): void {
            $customer = Konsumen::whereKey($konsumen->id)->lockForUpdate()->firstOrFail();
            $this->ensureCustomerSalesUnchanged($customer, $expectedSales);
            $this->applyCustomerSalesChange($customer, (int) $data['karyawan_id'], $reason, $request->user());
            $customer->update($data);
        }, 3);

        return to_route('db.konsumen')->with('success', 'Biodata konsumen berhasil diperbarui.');
    }

    public function update_customer_sales(Konsumen $konsumen, UpdateCustomerSalesRequest $request): JsonResponse
    {
        $data = $request->validated();
        DB::transaction(function () use ($konsumen, $request, $data): void {
            $customer = Konsumen::whereKey($konsumen->id)->lockForUpdate()->firstOrFail();
            $this->ensureCustomerSalesUnchanged($customer, $data['_original_sales_id']);
            $this->applyCustomerSalesChange($customer, (int) $data['karyawan_id'], $data['sales_change_reason'] ?? null, $request->user());
            $customer->update(['karyawan_id' => $data['karyawan_id']]);
        }, 3);

        return response()->json(['status' => 'success', 'message' => 'Sales penanggung jawab berhasil disimpan.']);
    }

    private function ensureCustomerSalesUnchanged(Konsumen $customer, int|string|null $expectedSales): void
    {
        if ((int) $customer->karyawan_id !== (int) $expectedSales) {
            throw ValidationException::withMessages(['karyawan_id' => 'Sales penanggung jawab telah berubah. Muat ulang halaman sebelum menyimpan kembali.']);
        }
    }

    private function applyCustomerSalesChange(Konsumen $customer, int $salesId, ?string $reason, User $user): void
    {
        if ((int) $customer->karyawan_id !== $salesId) {
            DB::table('invoice_juals')
                ->where('konsumen_id', $customer->id)
                ->where('lunas', 0)
                ->where('void', 0)
                ->update(['karyawan_id' => $salesId, 'updated_at' => now()]);
            $names = Karyawan::whereIn('id', array_filter([$customer->karyawan_id, $salesId]))->pluck('nama', 'id');
            DB::table('konsumen_sales_histories')->insert([
                'konsumen_id' => $customer->id,
                'sales_lama_id' => $customer->karyawan_id,
                'sales_baru_id' => $salesId,
                'sales_lama' => $names[$customer->karyawan_id] ?? null,
                'sales_baru' => $names[$salesId],
                'updated_by' => $user->id,
                'updated_by_name' => $user->name ?? '-',
                'keterangan' => $reason,
                'created_at' => now(), 'updated_at' => now(),
            ]);
        }
    }

    public function konsumen_delete(Konsumen $konsumen, Request $request): RedirectResponse
    {
        $data = $request->validate(['active' => ['required', 'boolean']]);
        $active = (bool) $data['active'];
        DB::transaction(function () use ($konsumen, $request, $active): void {
            $customer = Konsumen::whereKey($konsumen->id)->lockForUpdate()->firstOrFail();
            if ((bool) $customer->active === $active) {
                return;
            }
            $data = $request->validate(['alasan' => [$active ? 'nullable' : 'required', 'string', 'max:1000']]);
            $customer->update(['active' => $active, 'checklist_kunjungan' => $active, 'alasan' => $active ? null : $data['alasan']]);
        }, 3);

        return to_route('db.konsumen')->with('success', 'Status konsumen berhasil diperbarui.');
    }

    public function supplier()
    {
        $data = Supplier::all();

        $unit = BarangUnit::get();

        return view('db.supplier.index', [
            'data' => $data,
            'unit' => $unit,
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
            // throw $th;
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
            // throw $th;
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
            // throw $th;
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
            $kategoriQuery->whereHas('barangs', function ($q) use ($unitId, $bidangId) {
                $q->where('is_active', 1); // Opsional: hanya yang aktif
                if ($unitId) {
                    $q->where('barang_unit_id', $unitId);
                }
                if ($bidangId) {
                    $q->where('barang_type_id', $bidangId);
                }
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
            $namaQuery->whereHas('barang', function ($q) use ($unitId, $bidangId) {
                $q->where('is_active', 1);
                if ($unitId) {
                    $q->where('barang_unit_id', $unitId);
                }
                if ($bidangId) {
                    $q->where('barang_type_id', $bidangId);
                }
            });
        }

        $namaOptions = $namaQuery->orderBy('nama')->get(['id', 'nama']);

        return response()->json([
            'bidang' => $bidangOptions,
            'kategori' => $kategoriOptions,
            'nama' => $namaOptions,
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
        if (is_null($multiplier) || $multiplier === '') {
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
            'unit' => 'required|exists:barang_units,id',
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid',
        ]);
        // --- LOGIKA QUERY SAMA PERSIS DENGAN ORDER_DATA ---
        // Kita ulangi query builder disini agar hasil PDF sama persis dengan tabel

        $multiplier = $request->input('multiplier');
        if (is_null($multiplier) || $multiplier === '') {
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
            'unit' => 'required|exists:barang_units,id',
        ], [
            'unit.required' => ' Silahkan Melakukan Filter Perusahaan Terlebih Dahulu!!',
            'unit.exists' => 'Perusahaan yang dipilih tidak valid',
        ]);
        // set_time_limit(0);
        // ini_set('memory_limit', '-1');
        if (ob_get_contents()) {
            ob_end_clean();
        }

        $multiplier = $request->input('multiplier');
        if (is_null($multiplier) || $multiplier === '') {
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
                'Perusahaan' => $row->unit?->nama,
                'Bidang' => $row->type?->nama,
                'Kategori' => $row->kategori?->nama,
                'Nama Barang' => $row->barang_nama?->nama,
                'Kode' => $row->kode,
                'Merk' => $row->merk,
                'Jenis' => $row->jenis == 1 ? 'PPN' : ($row->jenis == 2 ? 'Non PPN' : '-'),
                'Stok Saat Ini' => (float) $row->stok_ready,
                'Satuan' => $row->satuan?->nama,
                'Rata2 Jual' => round($row->avg_sales),
                'Saran Order' => $saranOrder,
            ]);

            if (ob_get_length() > 0) {
                ob_flush();
                flush();
            }
        });

        exit;
    }

    public function metode_bayar()
    {
        // Proteksi Role Admin / Super User
        if (! in_array(Auth::user()->role, ['admin', 'su'])) {
            abort(403, 'Anda tidak memiliki akses ke halaman ini.');
        }

        $metodeBayars = MetodeBayar::all();

        return view('db.metode-bayar.index', compact('metodeBayars'));
    }

    public function metode_bayar_update(Request $request, MetodeBayar $metodeBayar)
    {
        // Proteksi Role Admin / Super User
        if (! in_array(Auth::user()->role, ['admin', 'su'])) {
            return response()->json(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $data = $request->validate([
            'max_hari' => 'required|integer|min:0',
        ]);

        $metodeBayar->update($data);

        return response()->json([
            'status' => 'success',
            'message' => 'Batas jatuh tempo untuk '.$metodeBayar->nama.' berhasil diubah menjadi '.$request->max_hari.' hari.',
        ]);
    }
}
