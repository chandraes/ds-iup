<?php

use App\Http\Controllers\AdminStockController;
use App\Http\Controllers\AttendanceIntegrationController;
use App\Http\Controllers\BarangController;
use App\Http\Controllers\BillingController;
use App\Http\Controllers\BpjsHealthMinimumController;
use App\Http\Controllers\CustomerFileController;
use App\Http\Controllers\CustomerInvoiceTableController;
use App\Http\Controllers\DatabaseController;
use App\Http\Controllers\DokumenController;
use App\Http\Controllers\FormBeliController;
use App\Http\Controllers\FormDepositController;
use App\Http\Controllers\FormInventaris;
use App\Http\Controllers\FormJualController;
use App\Http\Controllers\FormKasKecilController;
use App\Http\Controllers\FormLainController;
use App\Http\Controllers\FormLainReportController;
use App\Http\Controllers\HistoriController;
use App\Http\Controllers\HoldingController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InventarisController;
use App\Http\Controllers\InvestorController;
use App\Http\Controllers\InvestorModalController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KasbonController;
use App\Http\Controllers\LegalitasController;
use App\Http\Controllers\PajakController;
use App\Http\Controllers\PayrollAttendanceController;
use App\Http\Controllers\PayrollCommissionController;
use App\Http\Controllers\PayrollPreviewController;
use App\Http\Controllers\PengaturanController;
use App\Http\Controllers\PerusahaanController;
use App\Http\Controllers\PoController;
use App\Http\Controllers\RekapController;
use App\Http\Controllers\RekeningController;
use App\Http\Controllers\ReturController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\SalesPayrollController;
use App\Http\Controllers\StatistikController;
use App\Http\Controllers\UniversalController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WaController;
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

Route::get('/katalog/download/{katalog}', [HomeController::class, 'katalog_download'])
    ->name('katalog.download');

Auth::routes([
    'register' => false,
]);

Route::group(['middleware' => ['auth']], function () {
    Route::get('/customer-files/ktp/{konsumen}', [CustomerFileController::class, 'ktp'])->middleware('role:su,admin,user')->name('customer-files.ktp');
    Route::get('/customer-files/document/{dokumen}', [CustomerFileController::class, 'document'])->middleware('role:su,admin,user')->name('customer-files.document');

    Route::get('/home', [HomeController::class, 'index'])->name('home');

    Route::prefix('universal')->group(function () {
        Route::get('/get-konsumen', [UniversalController::class, 'getKonsumen'])->name('universal.get-konsumen');
        Route::get('/get-unit', [UniversalController::class, 'getUnit'])->name('universal.get-unit');
        Route::get('/get-kategori-by-unit', [UniversalController::class, 'getKategoriByUnit'])->name('universal.get-kategori-by-unit');

        Route::get('/search-kecamatan', [UniversalController::class, 'searchKecamatan'])->name('universal.search-kecamatan');
        Route::get('/status-wa', [UniversalController::class, 'getStatusWa'])->name('universal.get-status-wa');

        Route::get('/search-konsumen', [UniversalController::class, 'searchKonsumen'])->name('universal.search-konsumen');
        Route::get('/search-barang-nama', [UniversalController::class, 'searchBarangNama'])->name('universal.search-barang-nama');
        Route::get('/search-barang-kategori', [UniversalController::class, 'searchBarangKategori'])->name('universal.search-barang-kategori');
        Route::get('/search-barang-type', [UniversalController::class, 'searchBarangType'])->name('universal.search-barang-type');

        Route::get('/konsumen-data', [UniversalController::class, 'konsumen_data'])->name('universal.konsumen-data');

        Route::get('/get-kab-kota', [UniversalController::class, 'getKabKota'])->name('universal.get-kab-kota');
        Route::get('/get-kecamatan', [UniversalController::class, 'getKecamatan'])->name('universal.get-kecamatan');
    });

    Route::group(['middleware' => ['role:user']], function () {
        Route::prefix('user')->group(function () {
            Route::get('/stok-all', [UserController::class, 'stok_all'])->name('user.stok-all');
            Route::get('/stok-all/data', [UserController::class, 'stockData'])->name('user.stok-all.data');

            Route::prefix('konsumen')->group(function () {
                Route::get('/', [UserController::class, 'konsumen'])->name('user.konsumen');
                Route::get('/data', [UserController::class, 'konsumen_data'])->name('user.konsumen.data');

                Route::get('/dokumen', [UserController::class, 'konsumen_dokumen'])->name('user.konsumen.dokumen');
                Route::post('/dokumen/store', [UserController::class, 'konsumen_dokumen_store'])->name('user.konsumen.dokumen.store');
                Route::delete('/dokumen/destroy/{dokumen}', [UserController::class, 'konsumen_dokumen_destroy'])->name('.konsumen.dokumen.destroy');
            });

        });

    });

    Route::group(['middleware' => ['role:su,admin,user,sales']], function () {
        Route::get('billing/invoice-konsumen/detail/{invoice}', [InvoiceController::class, 'invoice_konsumen_detail'])->name('billing.invoice-konsumen.detail');
        Route::get('sales/invoice-konsumen/detail/{invoice}', [SalesController::class, 'invoice_konsumen_detail'])->name('sales.invoice-konsumen.detail');

    });

    // Routing statistik yang bisa di akses hampir seluruh role
    Route::group(['middleware' => ['role:su,admin,user,sales,perusahaan']], function () {
        Route::get('/checklist-sales', [HomeController::class, 'checklist_sales'])->name('checklist-sales');
        Route::prefix('statistik')->group(function () {
            Route::get('/omset-tahunan-konsumen', [StatistikController::class, 'omset_tahunan_konsumen'])->name('statistik.omset-tahunan-konsumen');
            Route::get('/omset-tahunan-konsumen/detail/{konsumen}/{tahun}', [StatistikController::class, 'detail_omset_tahunan_page'])->name('statistik.omset.detail_tahunan_page');
            Route::get('/omset-tahunan-konsumen/print', [StatistikController::class, 'print_omset_tahunan'])->name('statistik.omset.print_tahunan');
            Route::get('/omset-tahunan-konsumen/excel', [StatistikController::class, 'export_excel_omset_tahunan'])->name('statistik.omset.excel_tahunan');

            Route::prefix('omset-barang')->group(function () {
                Route::prefix('bulanan')->group(function () {
                    Route::get('/', [StatistikController::class, 'omset_bulanan_barang'])->name('statistik.omset-barang.bulanan');
                    Route::get('/detail/{barang}/{bulan}/{tahun}', [StatistikController::class, 'detail_omset_barang_page'])->name('statistik.omset-barang.bulanan.detail_page');
                    Route::get('statistik/omset-barang/bulanan/excel', [StatistikController::class, 'export_excel_omset_barang'])->name('statistik.omset-barang.bulanan.excel');
                    Route::get('statistik/omset-barang/bulanan/print', [StatistikController::class, 'print_omset_barang'])->name('statistik.omset-barang.bulanan.print');
                });

                Route::prefix('tahunan')->group(function () {
                    Route::get('/', [StatistikController::class, 'omset_tahunan_barang'])->name('statistik.omset-barang.tahunan');
                    Route::get('/detail/{barang}/{tahun}', [StatistikController::class, 'detail_omset_barang_tahunan_page'])->name('statistik.omset-barang.tahunan.detail_page');
                    Route::get('/excel', [StatistikController::class, 'export_excel_omset_barang_tahunan'])->name('statistik.omset-barang.tahunan.excel');
                    Route::get('/print', [StatistikController::class, 'print_omset_barang_tahunan'])->name('statistik.omset-barang.tahunan.print');
                });
            });
        });

    });

    Route::group(['middleware' => ['role:perusahaan']], function () {
        Route::prefix('perusahaan')->group(function () {
            Route::get('/konsumen', [PerusahaanController::class, 'konsumen'])->name('perusahaan.konsumen');
            Route::get('/konsumen/data', [PerusahaanController::class, 'konsumen_data'])->name('perusahaan.konsumen.data');
            Route::get('/konsumen/export', [PerusahaanController::class, 'exportKonsumen'])->name('perusahaan.konsumen.export');

            Route::get('/sales', [PerusahaanController::class, 'sales'])->name('perusahaan.sales');

            Route::get('/stok-ppn', [PerusahaanController::class, 'stok_ppn'])->name('perusahaan.stok-ppn');
            Route::get('/stok-non-ppn', [PerusahaanController::class, 'stok_non_ppn'])->name('perusahaan.stok-non-ppn');

            Route::get('/order', [PerusahaanController::class, 'order'])->name('perusahaan.order');
            Route::get('/order/data', [PerusahaanController::class, 'order_data'])->name('perusahaan.order.data');
            Route::get('/order/export-pdf', [PerusahaanController::class, 'order_export_pdf'])->name('perusahaan.order.export_pdf');
            Route::get('/order/filter-options', [PerusahaanController::class, 'getFilterOptions'])->name('perusahaan.order.filter_options');
            Route::get('/order/export-excel', [PerusahaanController::class, 'export_excel'])->name('perusahaan.order.export_excel');

            Route::prefix('stok-retur')->group(function () {
                Route::get('/', [PerusahaanController::class, 'stok_retur'])->name('perusahaan.stok-retur');
                Route::get('/data', [PerusahaanController::class, 'stok_retur_data'])->name('perusahaan.stok-retur.data');
                Route::get('/history/{id}', [PerusahaanController::class, 'stok_retur_sumber'])->name('perusahaan.stok-retur.history');

            });

            Route::prefix('penyelesaian-retur')->group(function () {
                Route::get('/', [PerusahaanController::class, 'invoiceIndex'])->name('perusahaan.penyelesaian-retur.index');
                Route::get('/data', [PerusahaanController::class, 'invoiceData'])->name('perusahaan.penyelesaian-retur.data');
                Route::get('/detail/{id}', [PerusahaanController::class, 'invoiceShow'])->name('perusahaan.penyelesaian-retur.detail');

            });

            Route::get('/omset-bulanan-konsumen', [PerusahaanController::class, 'omset_tahunan_konsumen'])->name('perusahaan.omset-bulanan-konsumen');
            Route::get('/omset-konsumen/excel', [PerusahaanController::class, 'export_excel_omset'])->name('perusahaan.omset.excel');
            Route::get('/omset-konsumen/print', [PerusahaanController::class, 'print_omset'])->name('perusahaan.omset.print');
            Route::get('/omset-konsumen/detail/{konsumen}/{bulan}/{tahun}', [PerusahaanController::class, 'detail_omset_page'])
                ->name('perusahaan.omset.detail_page');

            Route::prefix('selling-out')->group(function () {
                Route::get('/', [PerusahaanController::class, 'selling_out'])->name('perusahaan.selling-out');
            });
        });

    });

    Route::group(['middleware' => ['role:sales']], function () {
        Route::prefix('sales')->group(function () {
            Route::get('/gaji', [SalesPayrollController::class, 'index'])->name('sales.gaji');
            Route::get('/gaji/rincian', [SalesPayrollController::class, 'calculation'])->name('sales.gaji.rincian');

            Route::get('/omset-bulanan-konsumen', [SalesController::class, 'omset_tahunan_konsumen'])->name('sales.omset-bulanan-konsumen');
            Route::get('/omset-konsumen/excel', [SalesController::class, 'export_excel_omset'])->name('sales.omset.excel');
            Route::get('/omset-konsumen/print', [SalesController::class, 'print_omset'])->name('sales.omset.print');
            Route::get('/omset-konsumen/detail/{konsumen}/{bulan}/{tahun}', [SalesController::class, 'detail_omset_page'])
                ->name('sales.omset.detail_page');

            Route::get('/konsumen/data', [SalesController::class, 'konsumen_data'])->name('sales.konsumen.data');
            Route::get('/konsumen', [SalesController::class, 'konsumen'])->name('sales.konsumen');

            Route::get('/stok', [SalesController::class, 'stok'])->name('sales.stok');

            Route::prefix('invoice-konsumen')->group(function () {
                Route::get('/', [SalesController::class, 'invoice_konsumen'])->name('sales.invoice-konsumen');
            });

            Route::prefix('jual')->group(function () {
                Route::get('/', [SalesController::class, 'jual'])->name('sales.jual');
                Route::post('/store', [SalesController::class, 'jual_store'])->name('sales.jual.store');

                Route::get('/get-grosir', [SalesController::class, 'barang_get_grosir'])->name('sales.jual.get-grosir');

                Route::get('/keranjang/{keranjang}', [SalesController::class, 'jual_keranjang'])->name('sales.jual.keranjang');
                Route::post('/keranjang/empty/{keranjang}', [SalesController::class, 'jual_keranjang_empty'])->name('sales.jual.keranjang.empty');
                Route::post('/keranjang/delete/{keranjang}', [SalesController::class, 'jual_keranjang_delete'])->name('sales.jual.keranjang.delete');

                Route::get('/keranjang-review/{keranjang}', [SalesController::class, 'jual_keranjang_review'])->name('sales.jual.keranjang.review');
                Route::post('/keranjang/grosir/store', [SalesController::class, 'jual_keranjang_grosir_store'])->name('sales.jual.keranjang.grosir.store');
            });

            Route::prefix('check-konsumen')->group(function () {
                Route::get('/', [SalesController::class, 'check_konsumen'])->name('sales.check-konsumen');
                Route::post('/invoice', [SalesController::class, 'check_konsumen_invoice'])->name('sales.check-konsumen.invoice');
            });

            Route::prefix('keranjang')->group(function () {
                Route::get('/', [SalesController::class, 'keranjang'])->name('sales.stok.keranjang');
                Route::post('/delete/{keranjang}', [SalesController::class, 'keranjang_delete'])->name('sales.stok.keranjang.delete');
                Route::post('/store', [SalesController::class, 'keranjang_store'])->name('sales.stok.keranjang.store');
                Route::post('/update', [SalesController::class, 'keranjang_update'])->name('sales.stok.keranjang.update');
                Route::post('/set-jumlah', [SalesController::class, 'keranjang_set'])->name('sales.stok.keranjang.set-jumlah');
                Route::post('/empty', [SalesController::class, 'keranjang_empty'])->name('sales.stok.keranjang.empty');
                Route::post('/checkout', [SalesController::class, 'keranjang_checkout'])->name('sales.stok.keranjang.checkout');

                Route::post('/inden/store', [SalesController::class, 'keranjang_inden_store'])->name('sales.stok.keranjang.inden.store');
                Route::post('/inden/delete/{keranjangInden}', [SalesController::class, 'keranjang_inden_delete'])->name('sales.stok.keranjang.inden.delete');
            });

            Route::prefix('order')->group(function () {
                Route::get('/', [SalesController::class, 'order'])->name('sales.order');
                Route::get('/{order}', [SalesController::class, 'order_detail'])->name('sales.order.detail');
                Route::post('/void/{order}', [SalesController::class, 'order_void'])->name('sales.order.void');

                Route::post('/detail/delete/{orderDetail}', [SalesController::class, 'order_detail_delete'])->name('sales.order.detail.delete');
                Route::post('/detail/update/{order}', [SalesController::class, 'order_detail_update'])->name('sales.order.detail.update');
            });

            Route::prefix('pre-order')->group(function () {
                Route::get('/', [SalesController::class, 'preorder'])->name('sales.pre-order');
                Route::get('/{preorder}', [SalesController::class, 'preorder_detail'])->name('sales.pre-order.detail');
                Route::post('/update/{preorder}', [SalesController::class, 'preorder_detail_update'])->name('sales.pre-order.update');
                Route::post('/void/{preorder}', [SalesController::class, 'preorder_void'])->name('sales.pre-order.void');
                Route::post('/detail/delete/{orderDetail}', [SalesController::class, 'preorder_detail_delete'])->name('sales.pre-order.detail.delete');
            });

            Route::prefix('omset-harian')->group(function () {
                Route::get('/', [StatistikController::class, 'omset_harian_sales'])->name('sales.omset-harian');
                Route::get('/detail', [StatistikController::class, 'omset_harian_sales_detail'])->name('sales.omset-harian.detail');
                Route::get('/detail/invoice/{invoice}', [SalesController::class, 'omset_harian_detail_invoice'])->name('sales.omset-harian.detail.invoice');
            });

        });
    });

    Route::group(['middleware' => ['role:su,admin']], function () {

        // Tambahkan di dalam group route yang sesuai
        Route::prefix('billing/invoice-konsumen/')->group(function () {
            Route::get('{invoice}/edit', [InvoiceController::class, 'invoice_konsumen_edit'])->name('billing.invoice-konsumen.edit');

            // Siapkan route untuk proses updatenya nanti (Langkah 3)
            Route::post('{invoice}/update', [InvoiceController::class, 'invoice_konsumen_update'])->name('billing.invoice-konsumen.update');
        });

        // ROUTE PENGATURAN
        // Route::view('pengaturan', 'pengaturan.index')->name('pengaturan');
        Route::prefix('pengaturan')->group(function () {

            Route::prefix('aplikasi')->group(function () {
                Route::get('/', [PengaturanController::class, 'aplikasi'])->name('pengaturan.aplikasi');
                Route::get('/edit/{config}', [PengaturanController::class, 'aplikasi_edit'])->name('pengaturan.aplikasi.edit');
                Route::patch('/update/{config}', [PengaturanController::class, 'aplikasi_update'])->name('pengaturan.aplikasi.update');
            });

            Route::get('/holding', [PengaturanController::class, 'holding'])->name('pengaturan.holding');
            Route::post('/holding', [PengaturanController::class, 'holding_store'])->name('pengaturan.holding.store');

            Route::get('/', [PengaturanController::class, 'index_view'])->name('pengaturan');
            Route::get('/wa', [WaController::class, 'index'])->name('pengaturan.wa');
            Route::get('/wa/get-wa-group', [WaController::class, 'get_group_wa'])->name('pengaturan.wa.get-group-wa');
            Route::patch('/wa/{group_wa}/update', [WaController::class, 'update'])->name('pengaturan.wa.update');

            Route::get('/akun', [PengaturanController::class, 'index'])->name('pengaturan.akun');
            Route::post('/akun/store', [PengaturanController::class, 'store'])->name('pengaturan.akun.store');
            Route::patch('/akun/{akun}/update', [PengaturanController::class, 'update'])->name('pengaturan.akun.update');
            Route::delete('/akun/{akun}/delete', [PengaturanController::class, 'destroy'])->name('pengaturan.akun.delete');

            Route::post('/password-konfirmasi', [PengaturanController::class, 'password_konfirmasi'])->name('pengaturan.password-konfirmasi');
            Route::post('/password-konfirmasi/cek', [PengaturanController::class, 'password_konfirmasi_cek'])->name('pengaturan.password-konfirmasi-cek');

            Route::prefix('batasan')->group(function () {
                Route::get('/', [PengaturanController::class, 'batasan'])->name('pengaturan.batasan');
                Route::patch('/update/{batasan}', [PengaturanController::class, 'batasan_update'])->name('pengaturan.batasan.update');
            });
        });

        Route::get('/histori-pesan', [HistoriController::class, 'index'])->name('histori-pesan');
        Route::post('/histori-pesan/resend/{pesanWa}', [HistoriController::class, 'resend'])->name('histori.resend');
        Route::delete('/histori-pesan/delete-sended', [HistoriController::class, 'delete_sended'])->name('histori.delete-sended');
        // END ROUTE PENGATURAN
    });

    // ROUTE DB
    Route::view('db', 'db.index')->name('db')->middleware('role:su,admin');
    Route::prefix('db')->group(function () {
        Route::get('/bpjs-kesehatan', [BpjsHealthMinimumController::class, 'index'])->name('bpjs-health.index')->middleware('role:su,admin');
        Route::post('/bpjs-kesehatan', [BpjsHealthMinimumController::class, 'store'])->name('bpjs-health.store')->middleware('role:su,admin');

        Route::group(['middleware' => ['role:su,admin']], function () {
            Route::post('/approval-ajuan-harga/{harga}', [BarangController::class, 'harga_ajuan_approve'])->name('db.harga-ajuan-approve');

            Route::prefix('metode-bayar')->group(function () {
                Route::get('/', [DatabaseController::class, 'metode_bayar'])->name('db.metode-bayar');
                Route::post('/update/{metodeBayar}', [DatabaseController::class, 'metode_bayar_update'])->name('db.metode-bayar.update');
            });

            Route::prefix('subpg')->group(function () {
                Route::get('/', [BarangController::class, 'subpg'])->name('db.subpg');
                Route::post('/store', [BarangController::class, 'subpg_store'])->name('db.subpg.store');
                Route::patch('/update/{subpg}', [BarangController::class, 'subpg_update'])->name('db.subpg.update');
                Route::delete('/destroy/{subpg}', [BarangController::class, 'subpg_destroy'])->name('db.subpg.destroy');
            });

            Route::prefix('kreditor')->group(function () {
                Route::get('/', [DatabaseController::class, 'kreditor'])->name('db.kreditor');
                Route::post('/store', [DatabaseController::class, 'kreditor_store'])->name('db.kreditor.store');
                Route::patch('/update/{kreditor}', [DatabaseController::class, 'kreditor_update'])->name('db.kreditor.update');
                Route::delete('/destroy/{kreditor}', [DatabaseController::class, 'kreditor_destroy'])->name('db.kreditor.destroy');
            });

            Route::prefix('kategori-inventaris')->group(function () {
                Route::get('/', [DatabaseController::class, 'kategori_inventaris'])->name('db.kategori-inventaris');

                Route::prefix('kategori')->group(function () {
                    Route::post('/store', [DatabaseController::class, 'kategori_inventaris_store'])->name('db.kategori-inventaris.store');
                    Route::patch('/update/{kategori}', [DatabaseController::class, 'kategori_inventaris_update'])->name('db.kategori-inventaris.update');
                    Route::delete('/delete/{kategori}', [DatabaseController::class, 'kategori_inventaris_delete'])->name('db.kategori-inventaris.delete');
                });

                Route::prefix('jenis')->group(function () {
                    Route::post('/store', [DatabaseController::class, 'jenis_inventaris_store'])->name('db.jenis-inventaris.store');
                    Route::patch('/update/{jenis}', [DatabaseController::class, 'jenis_inventaris_update'])->name('db.jenis-inventaris.update');
                    Route::delete('/delete/{jenis}', [DatabaseController::class, 'jenis_inventaris_delete'])->name('db.jenis-inventaris.delete');
                });
            });

            Route::prefix('konsumen')->group(function () {
                Route::get('/', [DatabaseController::class, 'konsumen'])->name('db.konsumen');
                Route::get('/data', [DatabaseController::class, 'konsumen_data'])->name('db.konsumen.data');
                Route::patch('/{konsumen}/update-sales', [DatabaseController::class, 'update_customer_sales'])->name('db.konsumen.update_sales');
                Route::get('/{konsumen}/histori-sales', [DatabaseController::class, 'histori_sales'])->name('db.konsumen.histori_sales');
                Route::get('/{id}/histori-plafon', [DatabaseController::class, 'histori_plafon'])->name('db.konsumen.histori_plafon');
                Route::post('/{id}/update-plafon', [DatabaseController::class, 'update_plafon'])->name('db.konsumen.update_plafon');
                Route::post('/{id}/toggle-checklist', [DatabaseController::class, 'toggleChecklist'])->name('db.konsumen.toggle-checklist');
                Route::post('/{id}/toggle-notif', [DatabaseController::class, 'toggleNotif'])->name('db.konsumen.toggle-notif');

                Route::get('/dokumen', [DatabaseController::class, 'konsumen_dokumen'])->name('db.konsumen.dokumen');
                Route::post('/dokumen/store', [DatabaseController::class, 'konsumen_dokumen_store'])->name('db.konsumen.dokumen.store');
                Route::delete('/dokumen/destroy/{dokumen}', [DatabaseController::class, 'konsumen_dokumen_destroy'])->name('db.konsumen.dokumen.destroy');

                Route::get('/daftar-kunjungan/{konsumen}', [DatabaseController::class, 'konsumen_daftar_kunjungan'])->name('db.konsumen.daftar-kunjungan');
                Route::post('/store', [DatabaseController::class, 'konsumen_store'])->name('db.konsumen.store');
                Route::patch('/{konsumen}/update', [DatabaseController::class, 'konsumen_update'])->name('db.konsumen.update');
                Route::delete('/{konsumen}/delete', [DatabaseController::class, 'konsumen_delete'])->name('db.konsumen.delete');

                Route::post('/diskon-khusus/{konsumen}', [DatabaseController::class, 'konsumen_diskon_khusus'])->name('db.konsumen.diskon-khusus');

                Route::post('/upload-ktp/{konsumen}', [DatabaseController::class, 'konsumen_upload_ktp'])->name('db.konsumen.upload-ktp');
            });

            Route::prefix('kode-toko')->group(function () {
                Route::get('/', [DatabaseController::class, 'kode_toko'])->name('db.kode-toko');
                Route::post('/store', [DatabaseController::class, 'kode_toko_store'])->name('db.kode-toko.store');
                Route::patch('/update/{kode}', [DatabaseController::class, 'kode_toko_update'])->name('db.kode-toko.update');
                Route::delete('/delete/{kode}', [DatabaseController::class, 'kode_toko_delete'])->name('db.kode-toko.delete');
            });

            Route::prefix('sales-area')->group(function () {
                Route::get('/', [DatabaseController::class, 'sales_area'])->name('db.sales-area');
                Route::post('/store', [DatabaseController::class, 'sales_area_store'])->name('db.sales-area.store');
                Route::patch('/update/{sales}', [DatabaseController::class, 'sales_area_update'])->name('db.sales-area.update');
                Route::delete('/delete/{sales}', [DatabaseController::class, 'sales_area_delete'])->name('db.sales-area.delete');
            });

            Route::get('/investor', [InvestorController::class, 'index'])->name('db.investor');
            Route::patch('/investor/{investor}/update', [InvestorController::class, 'update'])->name('db.investor.update');

            Route::get('/rekening', [RekeningController::class, 'index'])->name('db.rekening');
            Route::patch('/rekening/{rekening}/update', [RekeningController::class, 'update'])->name('db.rekening.update');

            Route::prefix('investor-modal')->group(function () {
                Route::get('/', [InvestorModalController::class, 'index'])->name('db.investor-modal');
                Route::post('/store', [InvestorModalController::class, 'store'])->name('db.investor-modal.store');
                Route::patch('/{investor}/update', [InvestorModalController::class, 'update'])->name('db.investor-modal.update');
                Route::delete('/{investor}/delete', [InvestorModalController::class, 'destroy'])->name('db.investor-modal.delete');
            });

            Route::prefix('pengelola')->group(function () {
                Route::get('/', [DatabaseController::class, 'pengelola'])->name('db.pengelola');
                Route::post('/store', [DatabaseController::class, 'pengelola_store'])->name('db.pengelola.store');
                Route::patch('/{pengelola}/update', [DatabaseController::class, 'pengelola_update'])->name('db.pengelola.update');
                Route::delete('/{pengelola}/delete', [DatabaseController::class, 'pengelola_delete'])->name('db.pengelola.delete');
            });

            Route::prefix('supplier')->group(function () {
                Route::get('/', [DatabaseController::class, 'supplier'])->name('db.supplier');
                Route::post('/store', [DatabaseController::class, 'supplier_store'])->name('db.supplier.store');
                Route::patch('/update/{supplier}', [DatabaseController::class, 'supplier_update'])->name('db.supplier.update');
                Route::delete('/delete/{supplier}', [DatabaseController::class, 'supplier_delete'])->name('db.supplier.delete');
            });

            Route::prefix('satuan')->group(function () {
                Route::get('/', [DatabaseController::class, 'satuan'])->name('db.satuan');
                Route::post('/store', [DatabaseController::class, 'satuan_store'])->name('db.satuan.store');
                Route::patch('/update/{satuan}', [DatabaseController::class, 'satuan_update'])->name('db.satuan.update');
                Route::delete('/delete/{satuan}', [DatabaseController::class, 'satuan_delete'])->name('db.satuan.delete');
            });

            Route::prefix('pajak')->group(function () {
                Route::get('/', [DatabaseController::class, 'pajak'])->name('db.pajak');
                Route::patch('/update/{pajak}', [DatabaseController::class, 'pajak_update'])->name('db.pajak.update');
            });

            Route::prefix('diskon-umum')->group(function () {
                Route::get('/', [DatabaseController::class, 'diskon_umum'])->name('db.diskon-umum');
                Route::patch('/update/{diskon}', [DatabaseController::class, 'diskon_umum_update'])->name('db.diskon-umum.update');
            });

            Route::prefix('kelompok-rute')->group(function () {
                Route::get('/', [DatabaseController::class, 'kelompok_rute'])->name('db.kelompok-rute');
                Route::post('/store', [DatabaseController::class, 'kelompok_rute_store'])->name('db.kelompok-rute.store');
                Route::patch('/update/{kelompok}', [DatabaseController::class, 'kelompok_rute_update'])->name('db.kelompok-rute.update');
                Route::delete('/delete/{kelompok}', [DatabaseController::class, 'kelompok_rute_delete'])->name('db.kelompok-rute.delete');
            });

            Route::prefix('staff')->group(function () {
                Route::get('/attendance-integration', [AttendanceIntegrationController::class, 'index'])->name('attendance-integration.index');
                Route::post('/attendance-integration/sync', [AttendanceIntegrationController::class, 'sync'])->middleware('throttle:6,1')->name('attendance-integration.sync');
                Route::get('/', [DatabaseController::class, 'staff'])->name('db.staff');

                Route::prefix('jabatan')->group(function () {
                    Route::post('/store', [DatabaseController::class, 'jabatan_store'])->name('db.staff.jabatan.store');
                    Route::patch('/update/{jabatan}', [DatabaseController::class, 'jabatan_update'])->name('db.staff.jabatan.update');
                    Route::delete('/delete/{jabatan}', [DatabaseController::class, 'jabatan_delete'])->name('db.staff.jabatan.delete');
                });

                Route::get('/create', [DatabaseController::class, 'staff_create'])->name('db.staff.create');
                Route::post('/store', [DatabaseController::class, 'staff_store'])->name('db.staff.store');

                Route::get('/{staff}/dokumen/{document}', [DatabaseController::class, 'staff_document'])->name('db.staff.document');
                Route::get('/detail/{staff}', [DatabaseController::class, 'staff_show'])->name('db.staff.show');
                Route::get('/edit/{staff}', [DatabaseController::class, 'staff_edit'])->name('db.staff.edit');
                Route::patch('/update/{staff}', [DatabaseController::class, 'staff_update'])->name('db.staff.update');
                Route::patch('/status/{staff}', [DatabaseController::class, 'staff_status'])->name('db.staff.status');
                Route::delete('/delete/{staff}', [DatabaseController::class, 'staff_delete'])->name('db.staff.delete');
            });

            Route::prefix('cost-operational')->group(function () {
                Route::get('/', [DatabaseController::class, 'cost_operational'])->name('db.cost-operational');
                Route::post('/store', [DatabaseController::class, 'cost_operational_store'])->name('db.cost-operational.store');
                Route::patch('/update/{cost}', [DatabaseController::class, 'cost_operational_update'])->name('db.cost-operational.update');
                Route::delete('/delete/{cost}', [DatabaseController::class, 'cost_operational_delete'])->name('db.cost-operational.delete');
            });

            Route::prefix('barang-unit')->group(function () {

                Route::get('/getBarangNama', [BarangController::class, 'get_barang_nama'])->name('db.barang.get-barang-nama');

                Route::get('/', [BarangController::class, 'unit'])->name('db.unit');
                Route::post('/store', [BarangController::class, 'unit_store'])->name('db.unit.store');
                Route::patch('/update/{unit}', [BarangController::class, 'unit_update'])->name('db.unit.update');
                Route::delete('/delete/{unit}', [BarangController::class, 'unit_delete'])->name('db.unit.delete');

                Route::post('/type/store', [BarangController::class, 'type_store'])->name('db.unit.type.store');
                Route::patch('/type/update/{type}', [BarangController::class, 'type_update'])->name('db.unit.type.update');
                Route::delete('/type/delete/{type}', [BarangController::class, 'type_delete'])->name('db.unit.type.delete');
            });

            Route::prefix('barang-kategori')->group(function () {
                Route::get('/', [BarangController::class, 'barang_kategori'])->name('db.barang-kategori');
                Route::post('/nama-store', [BarangController::class, 'barang_nama_store'])->name('db.barang-kategori.nama-store');
                Route::patch('/nama-update/{nama}', [BarangController::class, 'barang_nama_update'])->name('db.barang-kategori.nama-update');
                Route::delete('/nama-delete/{nama}', [BarangController::class, 'barang_nama_delete'])->name('db.barang-kategori.delete');
            });

            Route::prefix('barang')->group(function () {
                Route::get('/', [BarangController::class, 'barang'])->name('db.barang');
                Route::get('/data', [BarangController::class, 'barang_data'])->name('db.barang.data');
                Route::post('/toggle-status/{id}', [BarangController::class, 'toggle_status'])->name('db.barang.toggle-status');

                Route::get('/get-grosir', [BarangController::class, 'barang_get_grosir'])->name('db.barang.get-grosir');
                Route::post('/store-grosir', [BarangController::class, 'barang_grosir_store'])->name('db.barang.store-grosir');
                Route::post('/delete-grosir', [BarangController::class, 'barang_grosir_delete'])->name('db.barang.delete-grosir');

                Route::post('/store', [BarangController::class, 'barang_store'])->name('db.barang.store');
                Route::patch('/update/{barang}', [BarangController::class, 'barang_update'])->name('db.barang.update');
                Route::delete('/delete/{barang}', [BarangController::class, 'barang_delete'])->name('db.barang.delete');

                Route::post('/upload/image/{barang}', [BarangController::class, 'barang_upload_foto'])->name('db.barang.upload-image');
                Route::post('/diskon/{barang}', [BarangController::class, 'barang_diskon'])->name('db.barang.diskon');

                Route::post('/kategori/store', [BarangController::class, 'kategori_barang_store'])->name('db.barang.kategori.store');
                Route::patch('/kategori/update/{kategori}', [BarangController::class, 'kategori_barang_update'])->name('db.barang.kategori.update');
                Route::delete('/kategori/delete/{kategori}', [BarangController::class, 'kategori_barang_delete'])->name('db.barang.kategori.delete');

            });

            Route::post('/hide/{barang}', [BarangController::class, 'hide_stok'])->name('db.hide');

            Route::patch('/stok/edit-stok-su/{stok}', [BarangController::class, 'edit_stok_su'])->name('db.stok.edit-stok-su');

            Route::get('/stok-data', [BarangController::class, 'stok_data'])->name('db.stok-data');

            Route::prefix('stok-ppn')->group(function () {
                Route::get('/', [BarangController::class, 'stok_ppn'])->name('db.stok-ppn');
                Route::get('/history', [BarangController::class, 'stok_history'])->name('db.stok-ppn.history');
                Route::get('/download', [BarangController::class, 'stok_ppn_download'])->name('db.stok-ppn.download');
                Route::patch('/store/{barang}', [BarangController::class, 'stok_harga_update'])->name('db.stok-ppn.store');
            });

            Route::post('/stok-hilang/{stok}', [BarangController::class, 'ganti_rugi'])->name('db.stok-hilang');

            Route::prefix('stok-non-ppn')->group(function () {
                Route::get('/', [BarangController::class, 'stok_non_ppn'])->name('db.stok-non-ppn');
                Route::get('/download', [BarangController::class, 'stok_non_ppn_download'])->name('db.stok-non-ppn.download');
                Route::patch('/store/{barang}', [BarangController::class, 'stok_harga_update'])->name('db.stok-non-ppn.store');

            });
        });
    });

    Route::group(['middleware' => ['role:su,admin,user,investor']], function () {

        Route::prefix('statistik')->group(function () {
            Route::get('/', [StatistikController::class, 'index'])->name('statistik');

            Route::prefix('omset-harian-sales')->group(function () {
                Route::get('/', [StatistikController::class, 'omset_harian_sales'])->name('statistik.omset-harian-sales');
                Route::get('/detail', [StatistikController::class, 'omset_harian_sales_detail'])->name('statistik.omset-harian-sales.detail');
            });

            Route::prefix('profit')->group(function () {
                Route::get('/harian', [StatistikController::class, 'profit_harian'])->name('statistik.profit.harian');
            });

            Route::get('/omset-bulanan-konsumen', [StatistikController::class, 'omset_bulanan_konsumen'])->name('statistik.omset-bulanan-konsumen');

            Route::get('/omset-konsumen/excel', [StatistikController::class, 'export_excel_omset'])->name('statistik.omset.excel');
            Route::get('/omset-konsumen/print', [StatistikController::class, 'print_omset'])->name('statistik.omset.print');
            // Route untuk halaman detail omset
            Route::get('/omset-konsumen/detail/{konsumen}/{bulan}/{tahun}', [StatistikController::class, 'detail_omset_page'])
                ->name('statistik.omset.detail_page');

        });

        Route::get('rekap', [RekapController::class, 'index'])->name('rekap');
        Route::prefix('rekap')->group(function () {
            Route::get('/konsumen-options', [RekapController::class, 'konsumenOptions'])->name('rekap.konsumen-options');
            Route::get('/lain-lain', [FormLainReportController::class, 'index'])->name('rekap.lain-lain');
            Route::get('/kasbon', [KasbonController::class, 'report'])->middleware('role:su,admin,user')->name('rekap.kasbon');
            Route::post('/kasbon/{kasbon}/cancel', [KasbonController::class, 'cancel'])->middleware('role:su,admin')->name('rekap.kasbon.cancel');

            Route::prefix('retur')->group(function () {
                Route::get('/', [RekapController::class, 'retur'])->name('rekap.retur');
                Route::get('/data', [RekapController::class, 'retur_data'])->name('rekap.retur.data');
            });

            Route::prefix('janji-bayar')->group(function () {
                Route::get('/', [RekapController::class, 'janji_bayar'])->name('rekap.janji-bayar');
            });

            Route::prefix('bunga-investor')->group(function () {
                Route::get('/', [RekapController::class, 'bunga_investor'])->name('rekap.bunga-investor');
            });

            Route::prefix('kas-besar/{ppn_kas}')->group(function () {
                Route::get('/', [RekapController::class, 'kas_besar'])->name('rekap.kas-besar');
                Route::get('/print/{bulan}/{tahun}', [RekapController::class, 'kas_besar_print'])->name('rekap.kas-besar.print');
            });

            Route::prefix('kas-kecil')->group(function () {
                Route::get('/', [RekapController::class, 'kas_kecil'])->name('rekap.kas-kecil');
                Route::get('/print/{bulan}/{tahun}', [RekapController::class, 'kas_kecil_print'])->name('rekap.kas-kecil.print');
                Route::get('/{kas}/void', [RekapController::class, 'void_kas_kecil'])->name('rekap.kas-kecil.void');
            });

            Route::prefix('barang-retur')->group(function () {
                Route::get('/', [RekapController::class, 'barang_retur'])->name('rekap.barang-retur');
                Route::get('/data', [RekapController::class, 'barang_retur_data'])->name('rekap.barang-retur.data');
            });

            // Route::get('/statistik/{customer}', [App\Http\Controllers\StatistikController::class, 'index'])->name('statistik.index');
            // Route::get('/statistik/{customer}/print', [App\Http\Controllers\StatistikController::class, 'print'])->name('statistik.print');

            Route::get('kas-project', [RekapController::class, 'kas_project'])->name('rekap.kas-project');
            Route::post('/kas-project/void/{kasProject}', [RekapController::class, 'void_kas_project'])->name('rekap.kas-project.void');
            Route::get('/kas-project/print/{project}/{bulan}/{tahun}', [RekapController::class, 'kas_project_print'])->name('rekap.kas-project.print');

            Route::prefix('kas-investor')->group(function () {
                Route::get('/', [RekapController::class, 'rekap_investor'])->name('rekap.kas-investor');
                Route::get('/show/{investor}', [RekapController::class, 'rekap_investor_show'])->name('rekap.kas-investor.show');
                Route::get('/detail/{investor}', [RekapController::class, 'rekap_investor_detail'])->name('rekap.kas-investor.detail');
                Route::get('/detail-deviden/{investor}/show', [RekapController::class, 'rekap_investor_detail_deviden_show'])->name('rekap.kas-investor.detail-deviden.show');
                Route::get('/detail-deviden/{investor}', [RekapController::class, 'rekap_investor_detail_deviden'])->name('rekap.kas-investor.detail-deviden');
            });

            Route::prefix('kas-konsumen')->group(function () {
                Route::get('/', [RekapController::class, 'konsumen'])->name('rekap.kas-konsumen');
            });

            Route::prefix('invoice-penjualan')->group(function () {
                Route::get('/', [RekapController::class, 'invoice_penjualan'])->name('rekap.invoice-penjualan');
                Route::get('/pdf', [RekapController::class, 'invoice_penjualan_download'])->name('rekap.invoice-penjualan.pdf');
                Route::get('/{invoice}/detail', [RekapController::class, 'invoice_penjualan_detail'])->name('rekap.invoice-penjualan.detail');
            });

            Route::prefix('pph-masa')->group(function () {
                Route::get('/', [RekapController::class, 'pph_masa'])->name('rekap.pph-masa');
                Route::get('/detail/{month}/{year}', [RekapController::class, 'pph_masa_detail'])->name('rekap.pph-masa.detail');
            });

            Route::prefix('gaji')->group(function () {
                Route::get('/absensi/{detail}', [PayrollAttendanceController::class, 'stored'])->name('rekap.gaji.absensi');
                Route::get('/komisi/{detail}', [PayrollCommissionController::class, 'stored'])->name('rekap.gaji.komisi');
                Route::view('/', 'rekap.gaji.index')->name('rekap.gaji');
                Route::get('/detail', [RekapController::class, 'gaji_detail'])->name('rekap.gaji.detail');
            });

            Route::prefix('pph-badan')->group(function () {
                Route::get('/', [RekapController::class, 'pph_badan'])->name('rekap.pph-badan');
            });

            Route::prefix('inventaris')->group(function () {
                Route::get('/', [RekapController::class, 'inventaris'])->name('rekap.inventaris');
                Route::get('/{jenis}', [RekapController::class, 'inventaris_detail'])->name('rekap.inventaris.detail');
            });

            Route::prefix('invoice-pembelian')->group(function () {
                Route::get('/', [RekapController::class, 'invoice_pembelian'])->name('rekap.invoice-pembelian');
                Route::get('/pdf', [RekapController::class, 'invoice_pembelian_download'])->name('rekap.invoice-pembelian.pdf');
                Route::get('/detail/{invoice}', [RekapController::class, 'invoice_pembelian_detail'])->name('rekap.invoice-pembelian.detail');
            });

            Route::prefix('pricelist')->group(function () {
                Route::get('/', [RekapController::class, 'pricelist'])->name('rekap.pricelist');
                Route::get('/pdf', [RekapController::class, 'pricelist_pdf'])->name('rekap.pricelist.pdf');
            });

            Route::prefix('cost-operational')->group(function () {
                Route::get('/', [RekapController::class, 'cost_operational'])->name('rekap.cost-operational');
            });

            Route::prefix('uang-gantung')->group(function () {
                Route::get('/', [RekapController::class, 'uang_gantung_selesai'])->name('rekap.uang-gantung.selesai');
                Route::get('/data', [RekapController::class, 'uang_gantung_selesai_data'])->name('rekap.uang-gantung.selesai.data');
            });
        });
    });

    // END ROUTE REKAP

    // START ROUTE ASISTEN ADMIN
    Route::group(['middleware' => ['role:su,admin,user,asisten-admin']], function () {
        Route::get('/db/barang-unit/getType', [BarangController::class, 'get_type'])->name('db.barang.get-type');

        Route::prefix('db')->group(function () {
            Route::prefix('stok-all')->middleware('role:su,admin,asisten-admin')->group(function () {
                Route::get('/', [AdminStockController::class, 'index'])->name('db.stok-all');
                Route::get('/data', [AdminStockController::class, 'data'])->name('db.stok-all.data');
                Route::get('/detail/{stock}', [AdminStockController::class, 'detail'])->name('db.stok-all.detail');
                Route::get('/history/{product}', [AdminStockController::class, 'history'])->middleware('role:su,admin')->name('db.stok-all.history');
                Route::get('/employees', [AdminStockController::class, 'employees'])->middleware('role:su,admin')->name('db.stok-all.employees');
                foreach (['price', 'propose', 'approve', 'correct', 'hide', 'loss'] as $action) {
                    Route::post('/'.$action.'/{stock}', [AdminStockController::class, 'mutate'])->defaults('action', $action)->name('db.stok-all.'.$action);
                }
                Route::post('/harga-ajuan/{barang}', [BarangController::class, 'harga_ajuan_store'])->name('db.stok-all.harga-ajuan-store');
            });

            Route::get('/order', [DatabaseController::class, 'order'])->name('db.order');
            Route::get('/order/data', [DatabaseController::class, 'order_data'])->name('db.order.data');
            Route::get('/order/export-pdf', [DatabaseController::class, 'order_export_pdf'])->name('db.order.export_pdf');
            Route::get('/order/filter-options', [DatabaseController::class, 'getFilterOptions'])->name('db.order.filter_options');
            Route::get('/order/export-excel', [DatabaseController::class, 'export_excel'])->name('db.order.export_excel');
        });

        Route::prefix('billing/form-beli')->group(function () {
            Route::get('/', [FormBeliController::class, 'index'])->name('billing.form-beli');
            Route::post('/store', [FormBeliController::class, 'store'])->name('billing.form-beli.store');
            Route::post('/delete/{keranjang}', [FormBeliController::class, 'delete'])->name('billing.form-beli.delete');
            Route::get('/detail/{keranjang}', [FormBeliController::class, 'detail'])->name('billing.form-beli.detail');

            Route::get('/detail/{keranjang}/data', [FormBeliController::class, 'detail_datatable'])->name('billing.form-beli.detail.data');
            Route::post('/detail/{keranjang}/store', [FormBeliController::class, 'detail_store'])->name('billing.form-beli.detail.store');
            Route::post('/detail/{keranjang}/empty', [FormBeliController::class, 'detail_empty'])->name('billing.form-beli.detail.empty');
            Route::post('/detail/{keranjang}/lanjutkan', [FormBeliController::class, 'detail_lanjutkan'])->name('billing.form-beli.detail.lanjutkan');

            Route::get('/detail/{keranjang}/preview', [FormBeliController::class, 'detail_preview'])->name('billing.form-beli.detail.preview');
            Route::post('/detail/preview/delete', [FormBeliController::class, 'detail_preview_delete'])->name('billing.form-beli.detail.preview.delete');

            // Route::get('/get-kategori', [App\Http\Controllers\FormBeliController::class, 'getKategori'])->name('billing.form-beli.get-kategori');
            // Route::get('/get-barang', [App\Http\Controllers\FormBeliController::class, 'getBarang'])->name('billing.form-beli.get-barang');
            // Route::get('/get-merk', [App\Http\Controllers\FormBeliController::class, 'getMerk'])->name('billing.form-beli.get-merk');
            // Route::get('/get-kode', [App\Http\Controllers\FormBeliController::class, 'getKode'])->name('billing.form-beli.get-kode');
            // Route::get('/get-supplier', [App\Http\Controllers\FormBeliController::class, 'getSupplier'])->name('billing.form-beli.get-supplier');

            // Route::prefix('keranjang')->group(function () {
            //     Route::post('/empty', [App\Http\Controllers\FormBeliController::class, 'keranjang_empty'])->name('billing.form-beli.keranjang.empty');
            //     Route::post('/store', [App\Http\Controllers\FormBeliController::class, 'keranjang_store'])->name('billing.form-beli.keranjang.store');
            //     Route::delete('/delete/{keranjang}', [App\Http\Controllers\FormBeliController::class, 'keranjang_delete'])->name('billing.form-beli.keranjang.delete');
            // });
        });

    });

    Route::group(['middleware' => ['role:su,admin,user']], function () {

        Route::prefix('checklist-sales')->group(function () {

            Route::get('/download', [HomeController::class, 'checklist_sales_download'])->name('checklist-sales.download');
            Route::post('/update', [HomeController::class, 'update_checklist'])->name('checklist-sales.update');
            Route::post('/uncheck', [HomeController::class, 'uncheck_checklist'])->name('checklist-sales.uncheck');
        });

        Route::get('/status-wa', [HomeController::class, 'getStatusWa'])->name('status-wa');
        Route::get('/get-kab-kota', [HomeController::class, 'getKabKota'])->name('get-kab-kota');
        Route::get('/get-kecamatan', [HomeController::class, 'getKecamatan'])->name('get-kecamatan');

        Route::prefix('holding')->group(function () {
            Route::get('/check-connection', [HoldingController::class, 'check_connection'])->name('holding.check_connection');
        });

        Route::prefix('inventaris')->group(function () {
            Route::get('/', [InventarisController::class, 'index'])->name('inventaris.index');
            Route::get('/invoice', [InventarisController::class, 'invoice'])->name('inventaris.invoice');

            Route::prefix('/{kategori}')->group(function () {
                Route::get('/', [InventarisController::class, 'detail'])->name('inventaris.detail');
                Route::get('/{jenis}', [InventarisController::class, 'detail_jenis'])->name('inventaris.detail.jenis');
                Route::post('/{jenis}/{inventaris}', [InventarisController::class, 'aksi'])->name('inventaris.aksi');
            });
        });

        Route::prefix('legalitas')->group(function () {

            Route::prefix('kategori')->group(function () {
                Route::post('/store', [LegalitasController::class, 'kategori_store'])->name('legalitas.kategori-store');
                Route::patch('/update/{id}', [LegalitasController::class, 'kategori_update'])->name('legalitas.kategori-update');
                Route::delete('/destroy/{id}', [LegalitasController::class, 'kategori_destroy'])->name('legalitas.kategori-destroy');
            });

            Route::get('/', [LegalitasController::class, 'index'])->name('legalitas');
            Route::post('/store', [LegalitasController::class, 'store'])->name('legalitas.store');
            Route::patch('/update/{legalitas}', [LegalitasController::class, 'update'])->name('legalitas.update');
            Route::delete('/destroy/{legalitas}', [LegalitasController::class, 'destroy'])->name('legalitas.destroy');

            Route::post('/kirim-wa/{legalitas}', [LegalitasController::class, 'kirim_wa'])->name('legalitas.kirim-wa');

        });

        Route::prefix('dokumen')->group(function () {
            Route::get('/', [DokumenController::class, 'index'])->name('dokumen');

            Route::prefix('mutasi-rekening')->group(function () {
                Route::get('/', [DokumenController::class, 'mutasi_rekening'])->name('dokumen.mutasi-rekening');
                Route::post('/store', [DokumenController::class, 'mutasi_rekening_store'])->name('dokumen.mutasi-rekening.store');
                Route::delete('/destroy/{mutasi}', [DokumenController::class, 'mutasi_rekening_destroy'])->name('dokumen.mutasi-rekening.destroy');
                Route::post('/kirim-wa/{mutasi}', [DokumenController::class, 'kirim_wa'])->name('dokumen.mutasi-rekening.kirim-wa');

                Route::post('/checklist/{mutasi}', [DokumenController::class, 'mutasi_rekening_checklist'])->name('dokumen.mutasi-rekening.checklist');
            });

            Route::prefix('kontrak-tambang')->group(function () {
                Route::get('/', [DokumenController::class, 'kontrak_tambang'])->name('dokumen.kontrak-tambang');
                Route::post('/store', [DokumenController::class, 'kontrak_tambang_store'])->name('dokumen.kontrak-tambang.store');
                Route::delete('/destroy/{kontrak_tambang}', [DokumenController::class, 'kontrak_tambang_destroy'])->name('dokumen.kontrak-tambang.destroy');
                Route::post('/kirim-wa/{kontrak_tambang}', [DokumenController::class, 'kirim_wa_tambang'])->name('dokumen.kontrak-tambang.kirim-wa');
            });

            Route::prefix('kontrak-vendor')->group(function () {
                Route::get('/', [DokumenController::class, 'kontrak_vendor'])->name('dokumen.kontrak-vendor');
                Route::post('/store', [DokumenController::class, 'kontrak_vendor_store'])->name('dokumen.kontrak-vendor.store');
                Route::delete('/destroy/{kontrak_vendor}', [DokumenController::class, 'kontrak_vendor_destroy'])->name('dokumen.kontrak-vendor.destroy');
                Route::post('/kirim-wa/{kontrak_vendor}', [DokumenController::class, 'kirim_wa_vendor'])->name('dokumen.kontrak-vendor.kirim-wa');
            });

            Route::prefix('sph')->group(function () {
                Route::get('/', [DokumenController::class, 'sph'])->name('dokumen.sph');
                Route::post('/store', [DokumenController::class, 'sph_store'])->name('dokumen.sph.store');
                Route::delete('/destroy/{sph}', [DokumenController::class, 'sph_destroy'])->name('dokumen.sph.destroy');
                Route::post('/kirim-wa/{sph}', [DokumenController::class, 'kirim_wa_sph'])->name('dokumen.sph.kirim-wa');
            });
        });

        Route::prefix('company-profile')->group(function () {
            Route::get('/', [DokumenController::class, 'company_profile'])->name('company-profile');
            Route::post('/store', [DokumenController::class, 'company_profile_store'])->name('company-profile.store');
            Route::delete('/destroy/{company_profile}', [DokumenController::class, 'company_profile_destroy'])->name('company-profile.destroy');
            Route::post('/kirim-wa/{company_profile}', [DokumenController::class, 'kirim_wa_cp'])->name('company-profile.kirim-wa');
        });

        Route::prefix('pajak')->group(function () {

            Route::get('/', [PajakController::class, 'index'])->name('pajak.index');
            Route::prefix('rekap-ppn')->group(function () {
                Route::get('/', [PajakController::class, 'rekap_ppn'])->name('pajak.rekap-ppn');
                Route::get('/masukan/{rekapPpn}', [PajakController::class, 'rekap_ppn_masukan_detail'])->name('pajak.rekap-ppn.masukan');
                Route::get('/keluaran/{rekapPpn}', [PajakController::class, 'rekap_ppn_keluaran_detail'])->name('pajak.rekap-ppn.keluaran');
            });
            // Route::get('/rekap-ppn', [App\Http\Controllers\PajakController::class, 'rekap_ppn'])->name('pajak.rekap-ppn');

            Route::prefix('ppn-expired')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_expired'])->name('pajak.ppn-expired');
                Route::post('/back/{ppnKeluaran}', [PajakController::class, 'ppn_expired_back'])->name('pajak.ppn-expired.back');
            });

            Route::prefix('ppn-masukan')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_masukan'])->name('pajak.ppn-masukan');
                Route::patch('/store-faktur/{ppnMasukan}', [PajakController::class, 'ppn_masukan_store_faktur'])->name('pajak.ppn-masukan.store-faktur');
                Route::post('/keranjang-store', [PajakController::class, 'ppn_masukan_keranjang_store'])->name('pajak.ppn-masukan.keranjang-store');
                Route::post('/keranjang-destroy/{ppnMasukan}', [PajakController::class, 'ppn_masukan_keranjang_destroy'])->name('pajak.ppn-masukan.keranjang-destroy');
                Route::post('/keranjang-lanjut', [PajakController::class, 'ppn_masukan_keranjang_lanjut'])->name('pajak.ppn-masukan.keranjang-lanjut');
            });

            Route::prefix('ppn-keluaran')->group(function () {
                Route::get('/', [PajakController::class, 'ppn_keluaran'])->name('pajak.ppn-keluaran');
                Route::post('/expired/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_expired'])->name('pajak.ppn-keluaran.expired');
                Route::patch('/store-faktur/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_store_faktur'])->name('pajak.ppn-keluaran.store-faktur');
                Route::get('/keranjang', [PajakController::class, 'ppn_keluaran_keranjang'])->name('pajak.ppn-keluaran.keranjang');
                Route::post('/keranjang-store', [PajakController::class, 'ppn_keluaran_keranjang_store'])->name('pajak.ppn-keluaran.keranjang-store');
                Route::post('/keranjang-destroy/{ppnKeluaran}', [PajakController::class, 'ppn_keluaran_keranjang_destroy'])->name('pajak.ppn-keluaran.keranjang-destroy');
                Route::post('/keranjang-lanjut', [PajakController::class, 'ppn_keluaran_keranjang_lanjut'])->name('pajak.ppn-keluaran.keranjang-lanjut');
            });

        });

        Route::prefix('laporan-keuangan')->group(function () {
            Route::view('/laporan-keuangan', 'laporan-keuangan.index')->name('laporan-keuangan.index');
        });

        Route::prefix('katalog')->group(function () {
            Route::get('/', [HomeController::class, 'katalog'])->name('katalog');
            Route::post('/', [HomeController::class, 'katalog_store'])->name('katalog.store');
            Route::patch('/{katalog}', [HomeController::class, 'katalog_update'])->name('katalog.update');
            Route::delete('/{katalog}', [HomeController::class, 'katalog_destroy'])->name('katalog.delete');
        });

        Route::prefix('po')->group(function () {
            Route::get('/', [PoController::class, 'index'])->name('po');
            Route::get('/form', [PoController::class, 'form'])->name('po.form');
            Route::post('/form/store', [PoController::class, 'store'])->name('po.form.store');

            Route::get('/rekap', [PoController::class, 'rekap'])->name('po.rekap');
            Route::get('/rekap/{po}', [PoController::class, 'pdf'])->name('po.rekap.pdf');
            Route::delete('/rekap/{po}', [PoController::class, 'delete'])->name('po.rekap.delete');

            Route::get('/get-types/{unitId}', [PoController::class, 'getTypes']);
            Route::get('/get-kategori/{typeId}', [PoController::class, 'getKategori']);
            Route::get('/get-barang/{typeId}/{kategoriId}', [PoController::class, 'getBarang']);
        });

        Route::prefix('billing')->group(function () {
            Route::prefix('kasbon')->name('billing.kasbon.')->group(function () {
                Route::get('/', [KasbonController::class, 'index'])->name('index');
                Route::get('/create', [KasbonController::class, 'create'])->name('create');
                Route::get('/summary', [KasbonController::class, 'summary'])->name('summary');
                Route::post('/', [KasbonController::class, 'store'])->name('store');
            });

            Route::get('/', [BillingController::class, 'index'])->name('billing');

            Route::prefix('otorisasi-pembelian')->group(function () {
                Route::get('/', [BillingController::class, 'otorisasi_pembelian'])->name('billing.otorisasi-pembelian');
                Route::prefix('keranjang')->group(function () {
                    Route::get('/{keranjang}', [BillingController::class, 'otorisasi_pembelian_keranjang'])->name('billing.otorisasi-pembelian.keranjang');
                    // Route::post('/checkout', [BillingController::class, 'otorisasi_pembelian_keranjang_checkout'])->name('billing.otorisasi-pembelian.keranjang.checkout');
                });
            });

            Route::prefix('uang-gantung')->group(function () {
                Route::get('/', [BillingController::class, 'uang_gantung'])->name('billing.uang-gantung');
                Route::get('/data', [BillingController::class, 'uang_gantung_data'])->name('billing.uang-gantung.data');
                Route::post('/void/{uangGantung}', [BillingController::class, 'uang_gantung_void'])->name('billing.uang-gantung.void');
                Route::post('/lunas/{uangGantung}', [BillingController::class, 'uang_gantung_lunas'])->name('billing.uang-gantung.lunas');

                Route::get('/form', [BillingController::class, 'uang_gantung_form'])->name('billing.uang-gantung.form');
                Route::post('/form/store', [BillingController::class, 'uang_gantung_form_store'])->name('billing.uang-gantung.form.store');
            });

            Route::prefix('form-barang-retur')->group(function () {
                Route::get('/', [BillingController::class, 'form_barang_retur'])->name('billing.form-barang-retur');
                Route::post('/store', [BillingController::class, 'form_barang_retur_store'])->name('billing.form-barang-retur.store');
                Route::post('/delete/{retur}', [BillingController::class, 'form_barang_retur_delete'])->name('billing.form-barang-retur.delete');

                Route::get('/detail/{retur}', [BillingController::class, 'form_barang_retur_detail'])->name('billing.form-barang-retur.detail');
                Route::get('/detail/{retur}/data', [BillingController::class, 'form_barang_retur_detail_datatable'])->name('billing.form-barang-retur.detail.data');
                Route::post('/detail/{retur}/store', [BillingController::class, 'form_barang_retur_detail_store'])->name('billing.form-barang-retur.detail.store');
                Route::post('/detail/{retur}/empty', [BillingController::class, 'form_barang_retur_detail_empty'])->name('billing.form-barang-retur.detail.empty');
                Route::post('/detail/{retur}/lanjutkan', [BillingController::class, 'form_barang_retur_detail_lanjutkan'])->name('billing.form-barang-retur.detail.lanjutkan');

                Route::get('/detail/{retur}/preview', [BillingController::class, 'form_barang_retur_detail_preview'])->name('billing.form-barang-retur.detail.preview');
                Route::post('/detail/preview/delete', [BillingController::class, 'form_barang_retur_detail_preview_delete'])->name('billing.form-barang-retur.detail.preview.delete');

            });

            Route::prefix('barang-retur')->group(function () {
                Route::get('/', [BillingController::class, 'barang_retur'])->name('billing.barang-retur');
                Route::get('/data', [BillingController::class, 'barang_retur_data'])->name('billing.barang-retur.data');
                Route::get('/detail/{retur}', [BillingController::class, 'barang_retur_detail'])->name('billing.barang-retur.detail');
                Route::post('/void/{retur}', [BillingController::class, 'barang_retur_void'])->name('billing.barang-retur.void');

                Route::post('/kirim/{retur}', [BillingController::class, 'barang_retur_kirim'])->name('billing.barang-retur.kirim');
                Route::get('/cetak/{retur}', [BillingController::class, 'barang_retur_cetak'])->name('billing.barang-retur.cetak');
                // Route::get('/preview/{retur}', [BillingController::class, 'barang_retur_preview'])->name('billing.barang-retur.preview');
                Route::post('/selesaikan/{retur}', [BillingController::class, 'barang_retur_selesaikan'])->name('billing.barang-retur.selesaikan');

                Route::post('/terima/{retur}', [BillingController::class, 'barang_retur_terima'])->name('billing.barang-retur.terima');
                Route::get('/cetak-diterima/{retur}', [BillingController::class, 'barang_retur_cetak_diterima'])->name('billing.barang-retur.cetak_diterima');
            });

            Route::prefix('barang-retur-proses')->group(function () {
                Route::Get('/', [BillingController::class, 'barang_retur_proses'])->name('billing.barang-retur-proses');
            });

            Route::prefix('stok-retur')->group(function () {
                Route::get('/', [BillingController::class, 'stok_retur'])->name('billing.stok-retur');
                Route::get('/keranjang', [ReturController::class, 'cartIndex'])->name('billing.stok-retur.cart');
                Route::post('/add-to-cart', [ReturController::class, 'addToCart'])->name('billing.stok-retur.add-to-cart');
                Route::post('/empty-cart', [ReturController::class, 'emptyCart'])->name('billing.stok-retur.empty-cart');
                Route::get('/cart-info', [ReturController::class, 'getCartInfo'])->name('billing.stok-retur.cart-info');
                Route::get('/data', [ReturController::class, 'stok_retur_data'])->name('billing.stok-retur.data');
                Route::post('/cart-update', [ReturController::class, 'updateCart'])->name('billing.stok-retur.cart-update');
                Route::post('/checkout', [ReturController::class, 'processCheckout'])->name('billing.stok-retur.checkout');
                Route::get('/cart-delete/{id}', [ReturController::class, 'deleteCart'])->name('billing.stok-retur.cart-delete');
                Route::get('/history/{id}', [BillingController::class, 'stok_retur_sumber'])->name('billing.stok-retur.history');

            });

            Route::prefix('penyelesaian-retur')->group(function () {
                Route::get('/', [ReturController::class, 'invoiceIndex'])->name('billing.penyelesaian-retur.index');
                Route::get('/data', [ReturController::class, 'invoiceData'])->name('billing.penyelesaian-retur.data');
                Route::get('/detail/{id}', [ReturController::class, 'invoiceShow'])->name('billing.penyelesaian-retur.detail');

                Route::get('/print/{id}', [ReturController::class, 'printPdf'])->name('billing.penyelesaian-retur.print');

                // Halaman UI Verifikasi
                Route::get('/{id}/verify', [ReturController::class, 'verifyShow'])->name('billing.penyelesaian-retur.verify');

                // Untuk proses submitnya (nanti kita buat di Step 4)
                Route::post('/{id}/verify', [ReturController::class, 'verifySubmit'])->name('billing.penyelesaian-retur.verify.submit');
            });

            // Routing Sales Order
            Route::prefix('sales-order')->group(function () {
                Route::get('/', [BillingController::class, 'sales_order'])->name('billing.sales-order');
                Route::get('/detail/{order}', [BillingController::class, 'sales_order_detail'])->name('billing.sales-order.detail');
                Route::post('/void/{order}', [BillingController::class, 'sales_order_void'])->name('billing.sales-order.void');

                Route::get('/lanjutkan/{order}', [BillingController::class, 'sales_order_lanjutkan'])->name('billing.sales-order.lanjutkan');

                Route::post('/detail/update/{order}', [BillingController::class, 'sales_order_update'])->name('billing.sales-order.detail.update');
                Route::post('/detail/delete/{orderDetail}', [BillingController::class, 'sales_order_delete'])->name('billing.sales-order.detail.delete');
            });

            Route::prefix('pre-order')->group(function () {
                Route::get('/', [BillingController::class, 'preorder'])->name('billing.pre-order');
                Route::get('/{preorder}', [BillingController::class, 'preorder_detail'])->name('billing.pre-order.detail');
                Route::post('/update/{preorder}', [BillingController::class, 'preorder_detail_update'])->name('billing.pre-order.update');
                Route::post('/void/{preorder}', [BillingController::class, 'preorder_void'])->name('billing.pre-order.void');
                Route::post('/finish/{preorder}', [BillingController::class, 'preorder_finish'])->name('billing.pre-order.finish');
                Route::post('/detail/delete/{orderDetail}', [BillingController::class, 'preorder_detail_delete'])->name('billing.pre-order.detail.delete');
            });

            Route::prefix('bunga-investor')->group(function () {
                Route::get('/', [BillingController::class, 'bunga_investor'])->name('billing.bunga-investor');
                Route::post('/store', [BillingController::class, 'bunga_investor_store'])->name('billing.bunga-investor.store');
            });

            Route::prefix('ganti-rugi')->group(function () {
                Route::get('/', [BillingController::class, 'ganti_rugi'])->name('billing.ganti-rugi');
                Route::post('/bayar/{rugi}', [BillingController::class, 'ganti_rugi_bayar'])->name('billing.ganti-rugi.bayar');
                Route::post('/void/{rugi}', [BillingController::class, 'ganti_rugi_void'])->name('billing.ganti-rugi.void');
            });

            Route::get('/lihat-stok', [BillingController::class, 'lihat_stok'])->name('billing.lihat-stok');

            Route::prefix('form-inventaris')->group(function () {
                Route::get('/', [BillingController::class, 'form_inventaris'])->name('billing.form-inventaris');
                Route::get('/get-jenis', [FormInventaris::class, 'getJenis'])->name('billing.form-inventaris.get-jenis');
                Route::get('/beli', [FormInventaris::class, 'index'])->name('billing.form-inventaris.beli');
                Route::post('/beli/store', [FormInventaris::class, 'store'])->name('billing.form-inventaris.beli.store');

                Route::prefix('hutang')->group(function () {
                    Route::get('/', [FormInventaris::class, 'hutang'])->name('billing.form-inventaris.hutang');
                    Route::post('/pelunasan/{invoice}', [FormInventaris::class, 'pelunasan'])->name('billing.form-inventaris.hutang.pelunasan');
                    Route::post('/void/{invoice}', [FormInventaris::class, 'void'])->name('billing.form-inventaris.hutang.void');
                });
            });
            Route::prefix('form-cost-operational')->group(function () {
                Route::view('/', 'billing.form-cost-operational.index')->name('billing.form-cost-operational');
                Route::prefix('cost-operational')->group(function () {
                    Route::get('/', [BillingController::class, 'cost_operational'])->name('billing.form-cost-operational.cost-operational');
                    Route::post('/store', [BillingController::class, 'cost_operational_store'])->name('billing.form-cost-operational.cost-operational.store');
                });

                Route::prefix('form-gaji')->group(function () {
                    Route::get('/preview-pdf', [PayrollPreviewController::class, 'download'])->defaults('format', 'pdf')->name('billing.form-cost-operational.gaji.preview-pdf');
                    Route::get('/preview-excel', [PayrollPreviewController::class, 'download'])->defaults('format', 'excel')->name('billing.form-cost-operational.gaji.preview-excel');
                    Route::post('/absensi/perbarui', [PayrollAttendanceController::class, 'refresh'])->middleware('role:su,admin')->name('billing.form-cost-operational.gaji.absensi.refresh');
                    Route::get('/absensi', [PayrollAttendanceController::class, 'preview'])->name('billing.form-cost-operational.gaji.absensi.detail');
                    Route::get('/komisi', [PayrollCommissionController::class, 'preview'])->name('billing.form-cost-operational.gaji.komisi');
                    Route::get('/', [BillingController::class, 'gaji'])->name('billing.form-cost-operational.gaji');
                    Route::post('/store', [BillingController::class, 'gaji_store'])->name('billing.form-cost-operational.gaji.store');
                });
            });

            Route::prefix('form-deposit')->group(function () {
                Route::get('/getRekening', [FormDepositController::class, 'getRekening'])->name('form-deposit.get-rekening');
                Route::get('/masuk', [FormDepositController::class, 'masuk'])->name('form-deposit.masuk');
                Route::post('/masuk/store', [FormDepositController::class, 'masuk_store'])->name('form-deposit.masuk.store');
                Route::get('/keluar', [FormDepositController::class, 'keluar'])->name('form-deposit.keluar');
                Route::post('/keluar/store', [FormDepositController::class, 'keluar_store'])->name('form-deposit.keluar.store');
                Route::get('/keluar-all', [FormDepositController::class, 'keluar_all'])->name('form-deposit.keluar-all');
                Route::post('/keluar-all/store', [FormDepositController::class, 'keluar_all_store'])->name('form-deposit.keluar-all.store');
            });

            Route::prefix('form-kas-kecil')->group(function () {
                Route::get('/masuk', [FormKasKecilController::class, 'masuk'])->name('form-kas-kecil.masuk');
                Route::post('/masuk/store', [FormKasKecilController::class, 'masuk_store'])->name('form-kas-kecil.masuk.store');
                Route::get('/keluar', [FormKasKecilController::class, 'keluar'])->name('form-kas-kecil.keluar');
                Route::post('/keluar/store', [FormKasKecilController::class, 'keluar_store'])->name('form-kas-kecil.keluar.store');
            });

            Route::prefix('form-lain')->group(function () {
                Route::get('/masuk', [FormLainController::class, 'masuk'])->name('form-lain.masuk');
                Route::post('/masuk/store', [FormLainController::class, 'masuk_store'])->name('form-lain.masuk.store');
                Route::get('/keluar', [FormLainController::class, 'keluar'])->name('form-lain.keluar');
                Route::post('/keluar/store', [FormLainController::class, 'keluar_store'])->name('form-lain.keluar.store');
            });

            Route::prefix('form-dividen')->group(function () {
                Route::get('/', [BillingController::class, 'form_dividen'])->name('billing.form-dividen');
                Route::post('/store', [BillingController::class, 'form_dividen_store'])->name('billing.form-dividen.store');
            });

            // Route::get('form-beli/', [App\Http\Controllers\FormBeliController::class, 'keranjang'])->name('billing.form-beli.keranjang');
            // Route::post('form-beli/keranjang/checkout', [App\Http\Controllers\FormBeliController::class, 'keranjang_checkout'])->name('billing.form-beli.keranjang.checkout');

            Route::prefix('form-jual')->group(function () {
                Route::get('/', [FormJualController::class, 'index'])->name('billing.form-jual');
                Route::get('/get-stok/{id}/{barangPpn}', [FormJualController::class, 'get_stok'])->name('billing.form-jual.get-stok-ppn');

                Route::prefix('keranjang')->group(function () {
                    Route::post('/store', [FormJualController::class, 'keranjang_store'])->name('billing.form-jual.keranjang.store');
                    Route::post('/update', [FormJualController::class, 'keranjang_update'])->name('billing.form-jual.keranjang.update');
                    Route::post('/set-jumlah', [FormJualController::class, 'keranjang_set'])->name('billing.form-jual.keranjang.set-jumlah');
                    Route::post('/empty', [FormJualController::class, 'keranjang_empty'])->name('billing.form-jual.keranjang.empty');
                });

                Route::prefix('keranjang-jual')->group(function () {
                    Route::get('/', [FormJualController::class, 'keranjang'])->name('billing.form-jual.keranjang');
                    Route::post('/checkout', [FormJualController::class, 'keranjang_checkout'])->name('billing.form-jual.keranjang.checkout');
                    Route::get('/get-konsumen', [FormJualController::class, 'get_konsumen'])->name('billing.form-jual.keranjang.get-konsumen');
                });

                Route::get('/invoice/{invoice}', [FormJualController::class, 'invoice'])->name('billing.form-jual.invoice');
            });

            Route::prefix('invoice-supplier')->group(function () {
                Route::get('/ppn', [InvoiceController::class, 'invoice_supplier'])->name('billing.invoice-supplier');
                Route::get('/non-ppn', [InvoiceController::class, 'invoice_supplier_non_ppn'])->name('billing.invoice-supplier.non-ppn');
                Route::get('/detail/{invoice}', [InvoiceController::class, 'invoice_supplier_detail'])->name('billing.invoice-supplier.detail');
                Route::post('/bayar/{invoice}', [InvoiceController::class, 'invoice_supplier_bayar'])->name('billing.invoice-supplier.bayar');

                Route::post('/cicil/{invoice}', [InvoiceController::class, 'invoice_supplier_cicil'])->name('billing.invoice-supplier.cicil');

                Route::post('/void/{invoice}', [InvoiceController::class, 'invoice_supplier_void'])->name('billing.invoice-supplier.void');
            });

            Route::prefix('invoice-konsumen')->group(function () {
                Route::get('/ppn', [InvoiceController::class, 'invoice_konsumen'])->name('billing.invoice-konsumen');
                Route::get('/ppn/titipan', [InvoiceController::class, 'invoice_konsumen_titipan'])->name('billing.invoice-konsumen.titipan');
                Route::get('/non-ppn', [InvoiceController::class, 'invoice_konsumen_non_ppn'])->name('billing.invoice-konsumen.non-ppn');
                Route::get('/non-ppn/titipan', [InvoiceController::class, 'invoice_konsumen_titipan_non_ppn'])->name('billing.invoice-konsumen.non-ppn.titipan');

                Route::post('/bayar/{invoice}', [InvoiceController::class, 'invoice_konsumen_bayar'])->name('billing.invoice-konsumen.bayar');

                Route::post('/void/{invoice}', [InvoiceController::class, 'invoice_konsumen_void'])->name('billing.invoice-konsumen.void');

                Route::get('/invoice/{invoice}', [FormJualController::class, 'invoice_image'])->name('billing.invoice-konsumen.invoice-jpeg');

                Route::post('/cicil/{invoice}', [InvoiceController::class, 'invoice_konsumen_cicil'])->name('billing.invoice-konsumen.cicil');

                Route::get('/all/data', [CustomerInvoiceTableController::class, 'data'])->name('billing.invoice-konsumen.all.data');
                Route::get('/all/{invoice}/payment-data', [CustomerInvoiceTableController::class, 'paymentData'])->whereNumber('invoice')->name('billing.invoice-konsumen.all.payment-data');
                Route::get('/all/{invoice}/history', [CustomerInvoiceTableController::class, 'history'])->whereNumber('invoice')->name('billing.invoice-konsumen.all.history');
                Route::get('/all', [InvoiceController::class, 'invoice_konsumen_all'])->name('billing.invoice-konsumen.all');

                Route::get('/pdf', [InvoiceController::class, 'invoice_konsumen_download'])->name('billing.invoice-konsumen.pdf');
                Route::get('/pdf-all', [InvoiceController::class, 'invoice_konsumen_all_download'])->name('billing.invoice-konsumen.pdf-all');
            });

            Route::prefix('form-janji-bayar')->group(function () {
                Route::get('/', [BillingController::class, 'form_janji_bayar'])->name('billing.form-janji-bayar');
                Route::get('/data', [BillingController::class, 'form_janji_bayar_data'])->name('billing.form-janji-bayar.data');
                Route::get('/keranjang', [BillingController::class, 'form_janji_bayar_keranjang'])->name('billing.form-janji-bayar.keranjang');
                Route::post('/tambah-keranjang', [BillingController::class, 'form_janji_bayar_tambah_keranjang'])->name('billing.form-janji-bayar.tambah-keranjang');
                Route::post('/hapus-keranjang', [BillingController::class, 'form_janji_bayar_hapus_keranjang'])->name('billing.form-janji-bayar.hapus-keranjang');
                Route::post('/kosongkan-keranjang', [BillingController::class, 'form_janji_bayar_kosongkan_keranjang'])->name('billing.form-janji-bayar.kosongkan-keranjang');
                Route::post('/checkout', [BillingController::class, 'form_janji_bayar_checkout'])->name('billing.form-janji-bayar.checkout');
            });

            Route::prefix('invoice-janji-bayar')->group(function () {
                Route::get('/', [BillingController::class, 'invoice_janji_bayar'])->name('billing.invoice-janji-bayar');
                Route::get('/data', [BillingController::class, 'invoice_janji_bayar_data'])->name('billing.invoice-janji-bayar.data');
                Route::post('/complete/{id}', [BillingController::class, 'invoice_janji_bayar_complete'])->name('billing.invoice-janji-bayar.complete');
                Route::get('/detail/{id}', [BillingController::class, 'invoice_janji_bayar_detail'])->name('billing.invoice-janji-bayar.detail');
                Route::post('/void/{id}', [BillingController::class, 'invoice_janji_bayar_void'])->name('billing.invoice-janji-bayar.void');

            });

            Route::prefix('nota-ppn-masukan')->group(function () {
                Route::get('/', [BillingController::class, 'nota_ppn_masukan'])->name('nota-ppn-masukan');
                Route::post('/claim/{invoice}', [BillingController::class, 'claim_ppn'])->name('nota-ppn-masukan.claim');

            });

            Route::prefix('invoice-tagihan')->group(function () {
                Route::get('/', [BillingController::class, 'invoice_tagihan'])->name('invoice-tagihan');
            });

            Route::prefix('invoice-ppn')->group(function () {
                Route::get('/', [BillingController::class, 'invoice_ppn'])->name('invoice-ppn');
                Route::post('/bayar/{invoice}', [BillingController::class, 'invoice_ppn_bayar'])->name('invoice-ppn.bayar');
            });

            Route::prefix('ppn-susulan')->group(function () {
                Route::get('/', [BillingController::class, 'ppn_masuk_susulan'])->name('ppn-susulan');
                Route::post('/store', [BillingController::class, 'ppn_masuk_susulan_store'])->name('ppn-susulan.store');
            });

        });

    });

});
