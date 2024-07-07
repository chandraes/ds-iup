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

    public function detail(InvoiceBelanja $invoice)
    {
        return view('rekap.invoice-belanja.detail', [
            'data' => $invoice->load(['rekap', 'rekap.bahan_baku', 'rekap.satuan', 'rekap.bahan_baku.kategori']),
        ]);
    }
}
