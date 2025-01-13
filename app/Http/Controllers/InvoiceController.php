<?php

namespace App\Http\Controllers;

use App\Models\db\Supplier;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoice_supplier(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier'])->where('kas_ppn', 1)->where('tempo', 1)->where('void', 0);
        // get unique supplier_id from $data
        if ($request->has('supplier_id')) {
            $data->where('supplier_id', $request->supplier_id);
        }

        $data = $data->get();

        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();

        return view('billing.invoice-supplier.index', [
            'data' => $data,
            'supplier' => $supplier
        ]);
    }

    public function invoice_supplier_non_ppn(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier'])->where('kas_ppn', 0)->where('tempo', 1)->where('void', 0);

        if ($request->has('supplier_id')) {
            $data->where('supplier_id', $request->supplier_id);
        }

        $data = $data->get();
        // get unique supplier_id from $data
        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();

        return view('billing.invoice-supplier.index-non-ppn', [
            'data' => $data,
            'supplier' => $supplier
        ]);
    }

    public function invoice_supplier_void(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja();

        $res = $db->void($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_supplier_bayar(InvoiceBelanja $invoice)
    {
        $db = new InvoiceBelanja();
        // dd($invoice);
        $res = $db->bayar($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }

    public function invoice_supplier_detail(InvoiceBelanja $invoice)
    {
        return view('billing.invoice-supplier.detail', [
            'data' => $invoice->load(['items.barang.type.unit', 'items.barang.kategori']),
        ]);
    }

    public function invoice_konsumen(Request $request)
    {
        $data = InvoiceJual::with('konsumen')->where('void', 0)->where('titipan', 0)->where('lunas', 0)->where('kas_ppn', 1)->get();

        return view('billing.invoice-konsumen.index', [
            'data' => $data
        ]);
    }

    public function invoice_konsumen_titipan(Request $request)
    {
        $data = InvoiceJual::with('konsumen')->where('void', 0)->where('titipan', 1)->where('lunas', 0)->where('kas_ppn', 1)->get();
        $titipan = 1;

        return view('billing.invoice-konsumen.index', [
            'data' => $data,
            'titipan' => $titipan
        ]);
    }

    public function invoice_konsumen_non_ppn(Request $request)
    {
        $data = InvoiceJual::with('konsumen')->where('void', 0)->where('titipan', 0)->where('lunas', 0)->where('kas_ppn', 0)->get();

        return view('billing.invoice-konsumen.index-non-ppn', [
            'data' => $data
        ]);
    }

    public function invoice_konsumen_titipan_non_ppn(Request $request)
    {
        $data = InvoiceJual::with('konsumen')->where('void', 0)->where('titipan', 1)->where('lunas', 0)->where('kas_ppn', 0)->get();
        $titipan = 1;
        return view('billing.invoice-konsumen.index-non-ppn', [
            'data' => $data,
            'titipan' => $titipan
        ]);
    }

    public function invoice_konsumen_detail(InvoiceJual $invoice)
    {
        $data = $invoice->load(['konsumen', 'invoice_detail.stok.type', 'invoice_detail.stok.barang', 'invoice_detail.stok.unit', 'invoice_detail.stok.kategori', 'invoice_detail.stok.barang_nama']);
        $jam = CarbonImmutable::parse($data->created_at)->translatedFormat('H:i');
            $tanggal = CarbonImmutable::parse($data->created_at)->translatedFormat('d F Y');

        return view('billing.invoice-konsumen.detail', [
            'data' => $data,
            'jam' => $jam,
            'tanggal' => $tanggal,
        ]);
    }

    public function invoice_konsumen_bayar(InvoiceJual $invoice)
    {
        $db = new InvoiceJual();

        $res = $db->bayar($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }


    public function invoice_konsumen_void(InvoiceJual $invoice)
    {
        $db = new InvoiceJual();

        $res = $db->void($invoice->id);

        return redirect()->back()->with($res['status'], $res['message']);
    }
}
