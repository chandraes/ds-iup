<?php

namespace App\Http\Controllers;

use App\Models\db\Supplier;
use App\Models\transaksi\InvoiceBelanja;
use Carbon\Carbon;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function invoice_supplier(Request $request)
    {
        $data = InvoiceBelanja::with(['supplier'])->where('tempo', 1)->where('void', 0)->get();
        // get unique supplier_id from $data
        $supplierIds = $data->pluck('supplier_id')->unique();

        $supplier = Supplier::where('status', 1)->whereIn('id', $supplierIds)->get();

        return view('billing.invoice-supplier.index', [
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
}
