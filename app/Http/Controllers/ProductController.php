<?php

namespace App\Http\Controllers;

use App\Models\db\BahanBaku;
use App\Models\db\KategoriProduct;
use App\Models\db\Kemasan;
use App\Models\db\Product;
use App\Models\db\ProductKomposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProductController extends Controller
{
    public function index()
    {

        $data = KategoriProduct::has('product')->with(['product', 'product.komposisi', 'product.komposisi.bahan_baku', 'product.komposisi.bahan_baku.kategori'])->get();
        $bahan = BahanBaku::all();
        return view('db.product.index', [
            'data' => $data,
            'bahan' => $bahan
        ]);
    }

    public function create()
    {
        $kategori = KategoriProduct::all();
        return view('db.product.create', [
            'kategori' => $kategori
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'kode' => 'required|unique:products,kode',
            'kategori_product_id' => 'required',
        ]);

        $data['konversi_liter'] = 0; // [TODO] konversi liter

        $store = Product::create($data);

        return redirect()->route('db.product')->with('success', 'Data berhasil disimpan');
    }

    public function update(Product $product, Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'kode' => 'required|unique:products,kode,' . $product->id,
            'kategori_product_id' => 'required',
        ]);

        $product->update($data);

        return redirect()->route('db.product')->with('success', 'Data berhasil diupdate');
    }

    public function delete(Product $product)
    {
        $product->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function create_komposisi(Product $product)
    {
        $bahan = BahanBaku::all();

        return view('db.product.create-komposisi', [
            'product' => $product,
            'bahan' => $bahan
        ]);
    }

    public function store_komposisi(Request $request)
    {
        $data = $request->validate([
            'product_id' => 'required|exists:products,id',
            'bahan_baku_id' => 'required|array',
            'bahan_baku_id.*' => 'required|exists:bahan_bakus,id', // '*' untuk array, '.' untuk 'bahan_baku_id.0
            'jumlah' => 'required|array',
            'jumlah.*' => 'required',
        ]);

        // check if jumlah is 100
        $total = array_sum($data['jumlah']);

        if ($total != 100) {
            return redirect()->back()->with('error', 'Total persentase jumlah harus 100');
        }

        $konversi = 0;

        try {
            DB::beginTransaction();
            foreach ($data['bahan_baku_id'] as $key => $bahan) {
                ProductKomposisi::create([
                    'product_id' => $data['product_id'],
                    'bahan_baku_id' => $bahan,
                    'jumlah' => $data['jumlah'][$key]
                ]);

                $konversi += BahanBaku::find($bahan)->konversi * ($data['jumlah'][$key]/100);
            }

            Product::find($data['product_id'])->update([
                'konversi_liter' => $konversi
            ]);

            DB::commit();
        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return redirect()->back()->with('error', 'Terjadi kesalahan');
        }

        return redirect()->route('db.product')->with('success', 'Data berhasil disimpan');
    }

    public function edit_komposisi(Product $product)
    {
        $bahan = BahanBaku::all();

        return view('db.product.edit-komposisi', [
            'product' => $product->load('komposisi'),
            'bahan' => $bahan
        ]);
    }

    public function delete_komposisi(Product $product, BahanBaku $bahan)
    {
        ProductKomposisi::where('product_id', $product->id)->where('bahan_baku_id', $bahan->id)->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function kosongkan_komposisi(Product $product)
    {
        ProductKomposisi::where('product_id', $product->id)->delete();

        Product::find($product->id)->update([
            'konversi_liter' => 0
        ]);

        return redirect()->back()->with('success', 'Data berhasil dikosongkan');
    }

    public function kategori_store(Request $request)
    {
        $data = $request->validate([
            'nama' => 'required',
            'kode' => 'required',
        ]);

        KategoriProduct::create($data);

        return redirect()->back()->with('success', 'Data berhasil disimpan');
    }

    public function kategori_update(Request $request, KategoriProduct $kategori)
    {
        $data = $request->validate([
            'nama' => 'required',
            'kode' => 'required',
        ]);

        $kategori->update($data);

        return redirect()->back()->with('success', 'Data berhasil diupdate');
    }

    public function kategori_delete(KategoriProduct $kategori)
    {
        $check = $kategori->products->count();

        if ($check > 0) {
            return redirect()->back()->with('error', 'Kategori masih memiliki product. Hapus terlebih dahulu product yang terkait dengan kategori ini!');
        }

        $kategori->delete();

        return redirect()->back()->with('success', 'Data berhasil dihapus');
    }

    public function product_jadi()
    {
        $data = KategoriProduct::has('product.product_jadi')->with(['product', 'product.product_jadi', 'product.product_jadi.kemasan'])->get();
        $product = Product::all();
        $kemasan = Kemasan::all();
        // dd($data);
        return view('db.product-jadi.index', [
            'data' => $data,
            'product' => $product,
            'kemasan' => $kemasan
        ]);
    }
}
