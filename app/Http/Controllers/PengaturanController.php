<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\DB;
Use App\Http\Controllers\Hash;
use App\Models\Config;
use App\Models\PasswordKonfirmasi;
use App\Models\Pengaturan;
use Illuminate\Http\Request;

class PengaturanController extends Controller
{

    public function index_view()
    {
        $password = PasswordKonfirmasi::first();
        return view('pengaturan.index',
            [
                'password' => $password
            ]
        );
    }

    public function password_konfirmasi(Request $request)
    {
        $data = $request->validate([
            'password' => 'required'
        ]);

        $response = PasswordKonfirmasi::updatePassword($data);

        return redirect()->route('pengaturan')->with($response['status'], $response['message']);
    }

    public function password_konfirmasi_cek(Request $request)
    {
        try {
            $data = $request->validate([
                'password' => 'required'
            ]);

            $password = PasswordKonfirmasi::first();

            if (!$password) {
                return response()->json(['status' => 'error', 'message' => 'Password belum diatur']);
            }

            if ($data['password'] == $password->password) {
                return response()->json(['status' => 'success', 'message' => 'Password benar']);
            } else {
                return response()->json(['status' => 'error', 'message' => 'Password salah']);
            }
        } catch (\Exception $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
        }
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {

        $users = User::all();
        return view('pengaturan.pengguna.index', [
            'data' => $users,
        ]);
    }


    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('pengaturan.pengguna.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users,username',
            'name' => 'required|string|max:255',
            'email' => 'nullable',
            'password' => 'required',
            'role' => 'required',
            'supplier_id' => 'nullable',
        ]);

        $data['password'] = bcrypt($data['password']);

        User::create($data);

        return redirect()->route('pengaturan.akun')->with('success', 'Data berhasil ditambahkan!');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        $user = User::findOrFail($id);
        // dd($user);
        return view('pengaturan.pengguna.edit',  compact('user'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $data = $request->validate([
            'username' => 'required|string|max:255|unique:users,username,' . $id,
            'name' => 'required|string|max:255',
            'email' => 'nullable',
            'password' => 'nullable',
            'role' => 'required',
        ]);

        $user = User::findOrFail($id);

        if ($request->password) {
            $data['password'] = bcrypt($data['password']);
        } else {
            unset($data['password']);
        }

        if ($data['role'] != 'supplier') {
            $data['supplier_id'] = null;
        }

        try {
            $user->update($data);
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', $th->getMessage());
        }

        return redirect()->route('pengaturan.akun')->with('success', 'Data berhasil diubah!');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $check = User::count();

        if ($check == 1) {
            return redirect()->back()->with('error', 'Tidak dapat menghapus user, karena hanya ada 1 user');
        }

        DB::transaction(function () use ($id) {
            $user = User::findOrFail($id);

            // Pengecekan apakah hanya tersisa satu data
            $totalUsers = User::count();
            if ($totalUsers > 1) {
                $user->delete();
            } else {
                return redirect()->route('pengaturan.akun')->with('error', 'Tidak dapat menghapus satu-satunya pengguna.');
            }
        });

        return redirect()->route('pengaturan.akun')->with('success', 'User has been deleted');
    }

    public function batasan()
    {
        $data = Pengaturan::all();

        return view('pengaturan.batasan.index', [
            'data' => $data
        ]);
    }

    public function batasan_update(Pengaturan $batasan, Request $request)
    {
        $data = $request->validate([
            'nilai' => 'required'
        ]);

        $data['nilai'] = str_replace('.', '', $data['nilai']);

        $batasan->update($data);

        return redirect()->route('pengaturan.batasan')->with('success', 'Data berhasil diubah!');
    }

    public function aplikasi()
    {
        $data = Config::all();

        return view('pengaturan.aplikasi.index', [
            'data' => $data
        ]);
    }

    public function aplikasi_edit(Config $config)
    {
        $data = $config;
        return view('pengaturan.aplikasi.edit', compact('data'));
    }

    public function aplikasi_update(Config $config, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'singkatan' => 'required',
            'alamat' => 'required',
            'kode_pos' => 'required',
            'nama_direktur' => 'required',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = time() . '.' . $file->getClientOriginalExtension();
            $file->move('uploads/logo/', $filename);
            $data['logo'] = $filename;
        }

        $config->update($data);

        return redirect()->route('pengaturan.aplikasi')->with('success', 'Data berhasil diubah!');
    }

}
