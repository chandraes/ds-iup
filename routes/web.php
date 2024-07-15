<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

Route::get('/', function () {
    return redirect('/login')->with('status', 'Please login to continue.');
});

Auth::routes([
    'register' => false,
]);

Route::group(['middleware' => ['auth']], function() {
    Route::get('/home', [App\Http\Controllers\HomeController::class, 'index'])->name('home');

    Route::prefix('inventaris')->group(function(){
        Route::get('/', [App\Http\Controllers\InventarisController::class, 'index'])->name('inventaris.index');
        Route::get('/invoice', [App\Http\Controllers\InventarisController::class, 'invoice'])->name('inventaris.invoice');

        Route::prefix('/{kategori}')->group(function(){
            Route::get('/', [App\Http\Controllers\InventarisController::class, 'detail'])->name('inventaris.detail');
            Route::get('/{jenis}', [App\Http\Controllers\InventarisController::class, 'detail_jenis'])->name('inventaris.detail.jenis');
            Route::post('/{jenis}/{inventaris}', [App\Http\Controllers\InventarisController::class, 'aksi'])->name('inventaris.aksi');
        });
    });

    Route::prefix('pajak')->group(function(){
        // Route::view('/pajak', 'pajak.index')->name('pajak.index');
        Route::get('/', [App\Http\Controllers\PajakController::class, 'index'])->name('pajak.index');
        Route::get('/ppn-masukan', [App\Http\Controllers\PajakController::class, 'ppn_masukan'])->name('pajak.ppn-masukan');
    });

    Route::prefix('laporan-keuangan')->group(function(){
        Route::view('/laporan-keuangan', 'laporan-keuangan.index')->name('laporan-keuangan.index');
    });

    Route::group(['middleware' => ['role:su,admin']], function() {
        // ROUTE PENGATURAN
        // Route::view('pengaturan', 'pengaturan.index')->name('pengaturan');
        Route::prefix('pengaturan')->group(function () {

            Route::prefix('aplikasi')->group(function(){
                Route::get('/', [App\Http\Controllers\PengaturanController::class, 'aplikasi'])->name('pengaturan.aplikasi');
                Route::get('/edit/{config}', [App\Http\Controllers\PengaturanController::class, 'aplikasi_edit'])->name('pengaturan.aplikasi.edit');
                Route::patch('/update/{config}', [App\Http\Controllers\PengaturanController::class, 'aplikasi_update'])->name('pengaturan.aplikasi.update');
            });

            Route::get('/', [App\Http\Controllers\PengaturanController::class, 'index_view'])->name('pengaturan');
            Route::get('/wa', [App\Http\Controllers\WaController::class, 'index'])->name('pengaturan.wa');
            Route::get('/wa/get-wa-group', [App\Http\Controllers\WaController::class, 'get_group_wa'])->name('pengaturan.wa.get-group-wa');
            Route::patch('/wa/{group_wa}/update', [App\Http\Controllers\WaController::class, 'update'])->name('pengaturan.wa.update');

            Route::get('/akun', [App\Http\Controllers\PengaturanController::class, 'index'])->name('pengaturan.akun');
            Route::post('/akun/store', [App\Http\Controllers\PengaturanController::class, 'store'])->name('pengaturan.akun.store');
            Route::patch('/akun/{akun}/update', [App\Http\Controllers\PengaturanController::class, 'update'])->name('pengaturan.akun.update');
            Route::delete('/akun/{akun}/delete', [App\Http\Controllers\PengaturanController::class, 'destroy'])->name('pengaturan.akun.delete');

            Route::post('/password-konfirmasi', [App\Http\Controllers\PengaturanController::class, 'password_konfirmasi'])->name('pengaturan.password-konfirmasi');
            Route::post('/password-konfirmasi/cek', [App\Http\Controllers\PengaturanController::class, 'password_konfirmasi_cek'])->name('pengaturan.password-konfirmasi-cek');

            Route::prefix('batasan')->group(function(){
                Route::get('/', [App\Http\Controllers\PengaturanController::class, 'batasan'])->name('pengaturan.batasan');
                Route::patch('/update/{batasan}', [App\Http\Controllers\PengaturanController::class, 'batasan_update'])->name('pengaturan.batasan.update');
            });
        });

        Route::get('/histori-pesan', [App\Http\Controllers\HistoriController::class, 'index'])->name('histori-pesan');
        Route::post('/histori-pesan/resend/{pesanWa}', [App\Http\Controllers\HistoriController::class, 'resend'])->name('histori.resend');
        Route::delete('/histori-pesan/delete-sended', [App\Http\Controllers\HistoriController::class, 'delete_sended'])->name('histori.delete-sended');
        // END ROUTE PENGATURAN
    });

        // ROUTE DB
    Route::view('db', 'db.index')->name('db')->middleware('role:su,admin');
    Route::prefix('db')->group(function () {

        Route::group(['middleware' => ['role:su,admin']], function() {

            Route::prefix('kategori-inventaris')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'kategori_inventaris'])->name('db.kategori-inventaris');

                Route::prefix('kategori')->group(function(){
                    Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'kategori_inventaris_store'])->name('db.kategori-inventaris.store');
                    Route::patch('/update/{kategori}', [App\Http\Controllers\DatabaseController::class, 'kategori_inventaris_update'])->name('db.kategori-inventaris.update');
                    Route::delete('/delete/{kategori}', [App\Http\Controllers\DatabaseController::class, 'kategori_inventaris_delete'])->name('db.kategori-inventaris.delete');
                });

                Route::prefix('jenis')->group(function(){
                    Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'jenis_inventaris_store'])->name('db.jenis-inventaris.store');
                    Route::patch('/update/{jenis}', [App\Http\Controllers\DatabaseController::class, 'jenis_inventaris_update'])->name('db.jenis-inventaris.update');
                    Route::delete('/delete/{jenis}', [App\Http\Controllers\DatabaseController::class, 'jenis_inventaris_delete'])->name('db.jenis-inventaris.delete');
                });
            });

            Route::prefix('konsumen')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'konsumen'])->name('db.konsumen');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'konsumen_store'])->name('db.konsumen.store');
                Route::patch('/{konsumen}/update', [App\Http\Controllers\DatabaseController::class, 'konsumen_update'])->name('db.konsumen.update');
                Route::delete('/{konsumen}/delete', [App\Http\Controllers\DatabaseController::class, 'konsumen_delete'])->name('db.konsumen.delete');
            });

            Route::get('/investor', [App\Http\Controllers\InvestorController::class, 'index'])->name('db.investor');
            Route::patch('/investor/{investor}/update', [App\Http\Controllers\InvestorController::class, 'update'])->name('db.investor.update');

            Route::get('/rekening', [App\Http\Controllers\RekeningController::class, 'index'])->name('db.rekening');
            Route::patch('/rekening/{rekening}/update', [App\Http\Controllers\RekeningController::class, 'update'])->name('db.rekening.update');

            Route::prefix('investor-modal')->group(function (){
                Route::get('/', [App\Http\Controllers\InvestorModalController::class, 'index'])->name('db.investor-modal');
                Route::post('/store', [App\Http\Controllers\InvestorModalController::class, 'store'])->name('db.investor-modal.store');
                Route::patch('/{investor}/update', [App\Http\Controllers\InvestorModalController::class, 'update'])->name('db.investor-modal.update');
                Route::delete('/{investor}/delete', [App\Http\Controllers\InvestorModalController::class, 'destroy'])->name('db.investor-modal.delete');
            });

            Route::prefix('pengelola')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'pengelola'])->name('db.pengelola');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'pengelola_store'])->name('db.pengelola.store');
                Route::patch('/{pengelola}/update', [App\Http\Controllers\DatabaseController::class, 'pengelola_update'])->name('db.pengelola.update');
                Route::delete('/{pengelola}/delete', [App\Http\Controllers\DatabaseController::class, 'pengelola_delete'])->name('db.pengelola.delete');
            });

            Route::prefix('supplier')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'supplier'])->name('db.supplier');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'supplier_store'])->name('db.supplier.store');
                Route::patch('/update/{supplier}', [App\Http\Controllers\DatabaseController::class, 'supplier_update'])->name('db.supplier.update');
                Route::delete('/delete/{supplier}', [App\Http\Controllers\DatabaseController::class, 'supplier_delete'])->name('db.supplier.delete');
            });

            Route::prefix('pajak')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'pajak'])->name('db.pajak');
                Route::patch('/update/{pajak}', [App\Http\Controllers\DatabaseController::class, 'pajak_update'])->name('db.pajak.update');
            });

            Route::prefix('staff')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'staff'])->name('db.staff');

                Route::prefix('jabatan')->group(function(){
                    Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'jabatan_store'])->name('db.staff.jabatan.store');
                    Route::patch('/update/{jabatan}', [App\Http\Controllers\DatabaseController::class, 'jabatan_update'])->name('db.staff.jabatan.update');
                    Route::delete('/delete/{jabatan}', [App\Http\Controllers\DatabaseController::class, 'jabatan_delete'])->name('db.staff.jabatan.delete');
                });

                Route::get('/create', [App\Http\Controllers\DatabaseController::class, 'staff_create'])->name('db.staff.create');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'staff_store'])->name('db.staff.store');

                Route::get('/edit/{staff}', [App\Http\Controllers\DatabaseController::class, 'staff_edit'])->name('db.staff.edit');
                Route::patch('/update/{staff}', [App\Http\Controllers\DatabaseController::class, 'staff_update'])->name('db.staff.update');
                Route::delete('/delete/{staff}', [App\Http\Controllers\DatabaseController::class, 'staff_delete'])->name('db.staff.delete');
            });

            Route::prefix('cost-operational')->group(function(){
                Route::get('/', [App\Http\Controllers\DatabaseController::class, 'cost_operational'])->name('db.cost-operational');
                Route::post('/store', [App\Http\Controllers\DatabaseController::class, 'cost_operational_store'])->name('db.cost-operational.store');
                Route::patch('/update/{cost}', [App\Http\Controllers\DatabaseController::class, 'cost_operational_update'])->name('db.cost-operational.update');
                Route::delete('/delete/{cost}', [App\Http\Controllers\DatabaseController::class, 'cost_operational_delete'])->name('db.cost-operational.delete');
            });

            Route::prefix('barang-unit')->group(function(){

                Route::get('/getType', [App\Http\Controllers\BarangController::class, 'get_type'])->name('db.barang.get-type');

                Route::get('/', [App\Http\Controllers\BarangController::class, 'unit'])->name('db.unit');
                Route::post('/store', [App\Http\Controllers\BarangController::class, 'unit_store'])->name('db.unit.store');
                Route::patch('/update/{unit}', [App\Http\Controllers\BarangController::class, 'unit_update'])->name('db.unit.update');
                Route::delete('/delete/{unit}', [App\Http\Controllers\BarangController::class, 'unit_delete'])->name('db.unit.delete');

                Route::post('/type/store', [App\Http\Controllers\BarangController::class, 'type_store'])->name('db.unit.type.store');
                Route::patch('/type/update/{type}', [App\Http\Controllers\BarangController::class, 'type_update'])->name('db.unit.type.update');
                Route::delete('/type/delete/{type}', [App\Http\Controllers\BarangController::class, 'type_delete'])->name('db.unit.type.delete');
            });

            Route::prefix('barang')->group(function(){
                Route::get('/', [App\Http\Controllers\BarangController::class, 'barang'])->name('db.barang');
                Route::post('/store', [App\Http\Controllers\BarangController::class, 'barang_store'])->name('db.barang.store');
                Route::patch('/update/{barang}', [App\Http\Controllers\BarangController::class, 'barang_update'])->name('db.barang.update');
                Route::delete('/delete/{barang}', [App\Http\Controllers\BarangController::class, 'barang_delete'])->name('db.barang.delete');

                Route::post('/kategori/store', [App\Http\Controllers\BarangController::class, 'kategori_barang_store'])->name('db.barang.kategori.store');
                Route::patch('/kategori/update/{kategori}', [App\Http\Controllers\BarangController::class, 'kategori_barang_update'])->name('db.barang.kategori.update');
                Route::delete('/kategori/delete/{kategori}', [App\Http\Controllers\BarangController::class, 'kategori_barang_delete'])->name('db.barang.kategori.delete');
            });

            Route::prefix('stok-ppn')->group(function(){
                Route::get('/', [App\Http\Controllers\BarangController::class, 'stok_ppn'])->name('db.stok-ppn');
                Route::patch('/store/{barang}', [App\Http\Controllers\BarangController::class, 'stok_ppn_store'])->name('db.stok-ppn.store');
            });

            Route::prefix('stok-non-ppn')->group(function(){
                Route::get('/', [App\Http\Controllers\BarangController::class, 'stok_non_ppn'])->name('db.stok-non-ppn');
                Route::patch('/store/{barang}', [App\Http\Controllers\BarangController::class, 'stok_non_ppn_store'])->name('db.stok-non-ppn.store');

            });
        });
    });


    Route::group(['middleware' => ['role:su,admin,user,investor']], function() {
        Route::get('rekap', [App\Http\Controllers\RekapController::class, 'index'])->name('rekap');
        Route::prefix('rekap')->group(function() {

            Route::prefix('kas-besar/{ppn_kas}')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'kas_besar'])->name('rekap.kas-besar');
                Route::get('/print/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'kas_besar_print'])->name('rekap.kas-besar.print');
            });

            Route::prefix('kas-kecil')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'kas_kecil'])->name('rekap.kas-kecil');
                Route::get('/print/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'kas_kecil_print'])->name('rekap.kas-kecil.print');
                Route::get('/{kas}/void', [App\Http\Controllers\RekapController::class, 'void_kas_kecil'])->name('rekap.kas-kecil.void');
            });

            Route::get('/invoice', [App\Http\Controllers\RekapController::class, 'rekap_invoice'])->name('rekap.invoice');
            Route::get('/invoice/detail-project', [App\Http\Controllers\RekapController::class, 'rekap_invoice_detail_project'])->name('rekap.invoice.detail-project');

            Route::get('/statistik/{customer}', [App\Http\Controllers\StatistikController::class, 'index'])->name('statistik.index');
            Route::get('/statistik/{customer}/print', [App\Http\Controllers\StatistikController::class, 'print'])->name('statistik.print');

            Route::get('kas-project', [App\Http\Controllers\RekapController::class, 'kas_project'])->name('rekap.kas-project');
            Route::post('/kas-project/void/{kasProject}', [App\Http\Controllers\RekapController::class, 'void_kas_project'])->name('rekap.kas-project.void');
            Route::get('/kas-project/print/{project}/{bulan}/{tahun}', [App\Http\Controllers\RekapController::class, 'kas_project_print'])->name('rekap.kas-project.print');

            Route::prefix('kas-investor')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'rekap_investor'])->name('rekap.kas-investor');
                Route::get('/show/{investor}', [App\Http\Controllers\RekapController::class, 'rekap_investor_show'])->name('rekap.kas-investor.show');
                Route::get('/detail/{investor}', [App\Http\Controllers\RekapController::class, 'rekap_investor_detail'])->name('rekap.kas-investor.detail');
                Route::get('/detail-deviden/{investor}/show', [App\Http\Controllers\RekapController::class, 'rekap_investor_detail_deviden_show'])->name('rekap.kas-investor.detail-deviden.show');
                Route::get('/detail-deviden/{investor}', [App\Http\Controllers\RekapController::class, 'rekap_investor_detail_deviden'])->name('rekap.kas-investor.detail-deviden');
            });

            Route::prefix('kas-konsumen')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'konsumen'])->name('rekap.kas-konsumen');
            });

            Route::prefix('invoice-penjualan')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'invoice_penjualan'])->name('rekap.invoice-penjualan');
                Route::get('/{invoice}/detail', [App\Http\Controllers\RekapController::class, 'invoice_penjualan_detail'])->name('rekap.invoice-penjualan.detail');
            });

            Route::prefix('pph-masa')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'pph_masa'])->name('rekap.pph-masa');
                Route::get('/detail/{month}/{year}', [App\Http\Controllers\RekapController::class, 'pph_masa_detail'])->name('rekap.pph-masa.detail');
            });

            Route::prefix('gaji')->group(function(){
                Route::view('/', 'rekap.gaji.index')->name('rekap.gaji');
                Route::get('/detail', [App\Http\Controllers\RekapController::class, 'gaji_detail'])->name('rekap.gaji.detail');
            });

            Route::prefix('pph-badan')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'pph_badan'])->name('rekap.pph-badan');
            });

            Route::prefix('inventaris')->group(function(){
                Route::get('/', [App\Http\Controllers\RekapController::class, 'inventaris'])->name('rekap.inventaris');
                Route::get('/{jenis}', [App\Http\Controllers\RekapController::class, 'inventaris_detail'])->name('rekap.inventaris.detail');
            });
        });
    });

    // END ROUTE REKAP
    Route::group(['middleware' => ['role:su,admin,user']], function() {

        Route::prefix('po')->group(function(){
            Route::get('/', [App\Http\Controllers\PoController::class, 'index'])->name('po');
            Route::get('/form', [App\Http\Controllers\PoController::class, 'form'])->name('po.form');
            Route::post('/form/store', [App\Http\Controllers\PoController::class, 'store'])->name('po.form.store');

            Route::get('/rekap', [App\Http\Controllers\PoController::class, 'rekap'])->name('po.rekap');
            Route::get('/rekap/{po}', [App\Http\Controllers\PoController::class, 'pdf'])->name('po.rekap.pdf');
            Route::delete('/rekap/{po}', [App\Http\Controllers\PoController::class, 'delete'])->name('po.rekap.delete');

            Route::get('/get-types/{unitId}', [App\Http\Controllers\PoController::class, 'getTypes']);
            Route::get('/get-kategori/{typeId}', [App\Http\Controllers\PoController::class, 'getKategori']);
            Route::get('/get-barang/{typeId}/{kategoriId}', [App\Http\Controllers\PoController::class, 'getBarang']);
        });

        Route::prefix('billing')->group(function() {

            Route::get('/', [App\Http\Controllers\BillingController::class, 'index'])->name('billing');
            Route::prefix('form-inventaris')->group(function(){
                Route::get('/', [App\Http\Controllers\BillingController::class, 'form_inventaris'])->name('billing.form-inventaris');
                Route::get('/get-jenis', [App\Http\Controllers\FormInventaris::class, 'getJenis'])->name('billing.form-inventaris.get-jenis');
                Route::get('/beli', [App\Http\Controllers\FormInventaris::class, 'index'])->name('billing.form-inventaris.beli');
                Route::post('/beli/store', [App\Http\Controllers\FormInventaris::class, 'store'])->name('billing.form-inventaris.beli.store');

                Route::prefix('hutang')->group(function(){
                    Route::get('/', [App\Http\Controllers\FormInventaris::class, 'hutang'])->name('billing.form-inventaris.hutang');
                    Route::post('/pelunasan/{invoice}', [App\Http\Controllers\FormInventaris::class, 'pelunasan'])->name('billing.form-inventaris.hutang.pelunasan');
                    Route::post('/void/{invoice}', [App\Http\Controllers\FormInventaris::class, 'void'])->name('billing.form-inventaris.hutang.void');
                });
            });
            Route::prefix('form-cost-operational')->group(function(){
                Route::view('/', 'billing.form-cost-operational.index')->name('billing.form-cost-operational');
                Route::prefix('cost-operational')->group(function(){
                    Route::get('/', [App\Http\Controllers\BillingController::class, 'cost_operational'])->name('billing.form-cost-operational.cost-operational');
                    Route::post('/store', [App\Http\Controllers\BillingController::class, 'cost_operational_store'])->name('billing.form-cost-operational.cost-operational.store');
                });

                Route::prefix('form-gaji')->group(function(){
                    Route::get('/', [App\Http\Controllers\BillingController::class, 'gaji'])->name('billing.form-cost-operational.gaji');
                    Route::post('/store', [App\Http\Controllers\BillingController::class, 'gaji_store'])->name('billing.form-cost-operational.gaji.store');
                });
            });

            Route::prefix('form-deposit')->group(function() {
                Route::get('/getRekening', [App\Http\Controllers\FormDepositController::class, 'getRekening'])->name('form-deposit.get-rekening');
                Route::get('/masuk', [App\Http\Controllers\FormDepositController::class, 'masuk'])->name('form-deposit.masuk');
                Route::post('/masuk/store', [App\Http\Controllers\FormDepositController::class, 'masuk_store'])->name('form-deposit.masuk.store');
                Route::get('/keluar', [App\Http\Controllers\FormDepositController::class, 'keluar'])->name('form-deposit.keluar');
                Route::post('/keluar/store', [App\Http\Controllers\FormDepositController::class, 'keluar_store'])->name('form-deposit.keluar.store');
                Route::get('/keluar-all', [App\Http\Controllers\FormDepositController::class, 'keluar_all'])->name('form-deposit.keluar-all');
                Route::post('/keluar-all/store', [App\Http\Controllers\FormDepositController::class, 'keluar_all_store'])->name('form-deposit.keluar-all.store');
            });

            Route::prefix('form-kas-kecil')->group(function(){
                Route::get('/masuk', [App\Http\Controllers\FormKasKecilController::class, 'masuk'])->name('form-kas-kecil.masuk');
                Route::post('/masuk/store', [App\Http\Controllers\FormKasKecilController::class, 'masuk_store'])->name('form-kas-kecil.masuk.store');
                Route::get('/keluar', [App\Http\Controllers\FormKasKecilController::class, 'keluar'])->name('form-kas-kecil.keluar');
                Route::post('/keluar/store', [App\Http\Controllers\FormKasKecilController::class, 'keluar_store'])->name('form-kas-kecil.keluar.store');
            });

            Route::prefix('form-lain')->group(function(){
                Route::get('/masuk', [App\Http\Controllers\FormLainController::class, 'masuk'])->name('form-lain.masuk');
                Route::post('/masuk/store', [App\Http\Controllers\FormLainController::class, 'masuk_store'])->name('form-lain.masuk.store');
                Route::get('/keluar', [App\Http\Controllers\FormLainController::class, 'keluar'])->name('form-lain.keluar');
                Route::post('/keluar/store', [App\Http\Controllers\FormLainController::class, 'keluar_store'])->name('form-lain.keluar.store');
            });

            Route::prefix('form-dividen')->group(function(){
                Route::get('/', [App\Http\Controllers\BillingController::class, 'form_dividen'])->name('billing.form-dividen');
                Route::post('/store', [App\Http\Controllers\BillingController::class, 'form_dividen_store'])->name('billing.form-dividen.store');
            });

            Route::prefix('form-beli')->group(function(){
                Route::get('/', [App\Http\Controllers\FormBeliController::class, 'index'])->name('billing.form-beli');
                Route::get('/get-kategori', [App\Http\Controllers\FormBeliController::class, 'getKategori'])->name('billing.form-beli.get-kategori');
                Route::get('/get-barang', [App\Http\Controllers\FormBeliController::class, 'getBarang'])->name('billing.form-beli.get-barang');
                Route::get('/get-supplier', [App\Http\Controllers\FormBeliController::class, 'getSupplier'])->name('billing.form-beli.get-supplier');

                Route::prefix('keranjang')->group(function(){
                    Route::get('/', [App\Http\Controllers\FormBeliController::class, 'keranjang'])->name('billing.form-beli.keranjang');
                    Route::post('/checkout', [App\Http\Controllers\FormBeliController::class, 'keranjang_checkout'])->name('billing.form-beli.keranjang.checkout');
                    Route::post('/empty', [App\Http\Controllers\FormBeliController::class, 'keranjang_empty'])->name('billing.form-beli.keranjang.empty');
                    Route::post('/store', [App\Http\Controllers\FormBeliController::class, 'keranjang_store'])->name('billing.form-beli.keranjang.store');
                    Route::delete('/delete/{keranjang}', [App\Http\Controllers\FormBeliController::class, 'keranjang_delete'])->name('billing.form-beli.keranjang.delete');
                });
            });

            Route::prefix('form-jual')->group(function(){
                Route::get('/', [App\Http\Controllers\FormJualController::class, 'index'])->name('billing.form-jual');
                Route::get('/get-stok/{id}/{barangPpn}', [App\Http\Controllers\FormJualController::class, 'get_stok'])->name('billing.form-jual.get-stok-ppn');

                Route::prefix('keranjang')->group(function(){
                    Route::post('/store', [App\Http\Controllers\FormJualController::class, 'keranjang_store'])->name('billing.form-jual.keranjang.store');
                    Route::delete('/delete/{keranjangJual}', [App\Http\Controllers\FormJualController::class, 'keranjang_destroy'])->name('billing.form-jual.keranjang.delete');
                    Route::post('/empty', [App\Http\Controllers\FormJualController::class, 'keranjang_empty'])->name('billing.form-jual.keranjang.empty');
                });
            });

            Route::prefix('invoice-supplier')->group(function(){
                Route::get('/', [App\Http\Controllers\InvoiceController::class, 'invoice_supplier'])->name('billing.invoice-supplier');
                Route::get('/detail/{invoice}', [App\Http\Controllers\InvoiceController::class, 'invoice_supplier_detail'])->name('billing.invoice-supplier.detail');
                Route::post('/bayar/{invoice}', [App\Http\Controllers\InvoiceController::class, 'invoice_supplier_bayar'])->name('billing.invoice-supplier.bayar');
                Route::post('/void/{invoice}', [App\Http\Controllers\InvoiceController::class, 'invoice_supplier_void'])->name('billing.invoice-supplier.void');
            });

            Route::prefix('nota-ppn-masukan')->group(function(){
                Route::get('/', [App\Http\Controllers\BillingController::class, 'nota_ppn_masukan'])->name('nota-ppn-masukan');
                Route::post('/claim/{invoice}', [App\Http\Controllers\BillingController::class, 'claim_ppn'])->name('nota-ppn-masukan.claim');

            });

            Route::prefix('invoice-tagihan')->group(function () {
                Route::get('/', [App\Http\Controllers\BillingController::class, 'invoice_tagihan'])->name('invoice-tagihan');
            });

            Route::prefix('invoice-ppn')->group(function() {
                Route::get('/', [App\Http\Controllers\BillingController::class, 'invoice_ppn'])->name('invoice-ppn');
                Route::post('/bayar/{invoice}', [App\Http\Controllers\BillingController::class, 'invoice_ppn_bayar'])->name('invoice-ppn.bayar');
            });

            Route::prefix('ppn-susulan')->group(function() {
                Route::get('/', [App\Http\Controllers\BillingController::class, 'ppn_masuk_susulan'])->name('ppn-susulan');
                Route::post('/store', [App\Http\Controllers\BillingController::class, 'ppn_masuk_susulan_store'])->name('ppn-susulan.store');
            });

        });

    });

});
