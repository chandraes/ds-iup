<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangHistory;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Pajak;
use App\Models\db\Supplier;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Pajak\RekapPpn;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class KeranjangBeli extends Model
{
    protected $guarded = ['id'];

    protected $appends = ['kas_ppn_text', 'sistem_pembayaran_text'];

    public function details()
    {
        return $this->hasMany(KeranjangBeliDetail::class);
    }

    public function barang_unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getKasPpnTextAttribute()
    {
        return $this->kas_ppn == 1 ? 'Kas PPN' : 'Kas Non PPN';
    }

    public function getSistemPembayaranTextAttribute()
    {
        return $this->sistem_pembayaran == 1 ? 'Cash' : 'Tempo';
    }

     public function checkout($data)
    {
        $first = $this->find($data['keranjang_beli_id']);

        // dd($first, $data);
        $keranjang = $first->details();
        $data['kas_ppn'] = $first->kas_ppn;
        $data['tempo'] = $first->sistem_pembayaran == 2 ? 1 : 0;
        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $total = $keranjang->sum('total');
        $kodeKas = $data['kas_ppn'] == 1 ? 'Kas Besar PPN' : 'Kas Besar Non PPN';

        // dd($kodeKas, $data, $total);
        $data['add_fee'] = str_replace('.', '', $data['add_fee']);
        $data['diskon'] = str_replace('.', '', $data['diskon']);

        $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

        $data['dp_ppn'] = isset($data['dp_ppn']) && $data['dp_ppn'] == 1 ? floor($data['dp'] * $ppnRate / 100) : 0;

        $data['ppn'] = $data['kas_ppn'] == 1 ? floor(($total - $data['diskon']) * $ppnRate / 100) : 0;

        $data['total'] = $total + $data['add_fee'] + $data['ppn'] - $data['diskon'];

        $data['sisa_ppn'] = $data['dp_ppn'] > 0 ? $data['ppn'] - $data['dp_ppn'] : 0;

        $data['sisa'] = $data['tempo'] == 1 ? $data['total'] - $data['dp'] - $data['dp_ppn'] : 0;

        if ($data['tempo'] == 1 && $data['jatuh_tempo']) {
            $data['jatuh_tempo'] = Carbon::createFromFormat('Y-m-d', $data['jatuh_tempo'])->format('Y-m-d');
        } else {
            $data['jatuh_tempo'] = null;
        }

        // dd($data);
        $kas = new KasBesar;
        $saldo = $kas->saldoTerakhir($data['kas_ppn']);

        if ($data['dp'] > 0 && $saldo < $data['dp'] + $data['dp_ppn']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas tidak mencukupi!',
            ];
        }

        if ($data['tempo'] == 0 && $saldo < $data['total']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas tidak mencukupi!',
            ];
        }

        // if ((isset($data['dp']) && $data['dp'] > 0 && $saldo < $data['dp'] + $data['dp_ppn']) ||
        //     (!isset($data['dp']) || $data['dp'] <= 0) && $saldo < $data['total']) {
        //     return [
        //         'status' => 'error',
        //         'message' => 'Saldo kas tidak mencukupi!',
        //     ];
        // }

        try {
            DB::beginTransaction();

            $store_inv = $this->invoice_checkout($data);

            $state = 0;

            if ($data['tempo'] == 0) {
                $dataKas = [
                    'ppn_kas' => $data['kas_ppn'],
                    'uraian' => $data['uraian'],
                    'jenis' => 0,
                    'nominal' => $data['total'],
                    'saldo' => $kas->saldoTerakhir($data['kas_ppn']) - $data['total'],
                    'no_rek' => $store_inv->no_rek,
                    'nama_rek' => $store_inv->nama_rek,
                    'bank' => $store_inv->bank,
                    'modal_investor_terakhir' => $kas->modalInvestorTerakhir($data['kas_ppn']),
                    'invoice_belanja_id' => $store_inv->id,
                ];
                $store = $kas->create($dataKas);
                $state = 1;
            } elseif ($data['tempo'] == 1) {
                if (isset($data['dp']) && $data['dp'] > 0) {
                    $total = $data['dp'] + $data['dp_ppn'];
                    $dataKas = [
                        'ppn_kas' => $data['kas_ppn'],
                        'uraian' => 'DP '.$data['uraian'],
                        'jenis' => 0,
                        'nominal' => $total,
                        'saldo' => $kas->saldoTerakhir($data['kas_ppn']) - $total,
                        'no_rek' => $store_inv->no_rek,
                        'nama_rek' => $store_inv->nama_rek,
                        'bank' => $store_inv->bank,
                        'modal_investor_terakhir' => $kas->modalInvestorTerakhir($data['kas_ppn']),
                        'invoice_belanja_id' => $store_inv->id,
                    ];
                    $store = $kas->create($dataKas);
                    $state = 1;
                }
            }

            $this->update_barang($data);

            if ($data['kas_ppn'] == 1) {
                $this->store_ppn($store_inv);
            }

            $first->delete();

            DB::commit();
            // check if there is $store

            $dbInvoice = new InvoiceBelanja;

            $totalInvoiceSupplier = $dbInvoice->where('void', 0)->where('tempo', 1)->where('supplier_id', $data['supplier_id'])->sum('sisa');
            $grandTotalPpn = $dbInvoice->where('void', 0)->where('tempo', 1)->where('kas_ppn', 1)->sum('sisa');
            $grandTotalNonPpn = $dbInvoice->where('void', 0)->where('tempo', 1)->where('kas_ppn', 0)->sum('sisa');

            if ($state == 1) {
                $dbPPn = new PpnMasukan;
                $dbRekapPpn = new RekapPpn;
                $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
                $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
                $dbPpnKeluaran = new PpnKeluaran;
                $ppnKeluaran = $dbPpnKeluaran->where('is_finish', 0)->sum('nominal');

                $getKas = $kas->getKas();

                $addPesan = '';

                if (isset($data['dp']) && $data['dp'] > 0) {
                    $addPesan .= 'Invoice : Rp. '.number_format($data['dp'] + $data['dp_ppn'], 0, ',', '.')."\n".
                                'Sisa Invoice : Rp. '.number_format($data['sisa'], 0, ',', '.')."\n\n".
                                "==========================\n";
                }

                $addPesan .= "Sisa Saldo Kas Besar PPN: \n".
                            'Rp. '.number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                            "Sisa Saldo Kas Besar Non PPN: \n".
                            'Rp. '.number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n";

                if ($data['kas_ppn'] == 1) {
                    $addPesan .= "Total PPn Masukan : \n".
                                'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                                "Total PPn Keluaran : \n".
                                'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n";
                }

                $addPesan .= "Total Invoice Supplier: \n".
                            'Rp. '.number_format($totalInvoiceSupplier, 0, ',', '.')."\n\n".
                            "Grand Total Invoice PPn: \n".
                            'Rp. '.number_format($grandTotalPpn, 0, ',', '.')."\n\n".
                            "Grand Total Invoice Non PPn: \n".
                            'Rp. '.number_format($grandTotalNonPpn, 0, ',', '.')."\n\n";

                $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*FORM BELI BARANG*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                             '*'.$kodeKas."*\n".
                            'Uraian :  *'.$store->uraian."*\n\n".
                            'Nilai    :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank      : '.$store->bank."\n".
                            'Nama    : '.$store->nama_rek."\n".
                            'No. Rek : '.$store->no_rek."\n\n".
                            "==========================\n".
                            $addPesan.
                            "Terima kasih 🙏🙏🙏\n";

                $groupName = $data['kas_ppn'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

                $group = GroupWa::where('untuk', $groupName)->first()->nama_group;

                $kas->sendWa($group, $pesan);
            }

            if ($data['tempo'] == 1 && $data['dp'] == 0) {
                $dbPPn = new PpnMasukan;
                $dbRekapPpn = new RekapPpn;
                $saldoTerakhirPpn = $dbRekapPpn->saldoTerakhir();
                $ppnMasukan = $dbPPn->where('is_finish', 0)->sum('nominal') + $saldoTerakhirPpn;
                $dbPpnKeluaran = new PpnKeluaran;
                $ppnKeluaran = $dbPpnKeluaran->where('is_expired', 0)->where('is_finish', 0)->sum('nominal');

                $getKas = $kas->getKas();

                $addPesan = "Sisa Saldo Kas Besar PPN: \n".
                            'Rp. '.number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                            "Sisa Saldo Kas Besar Non PPN: \n".
                            'Rp. '.number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n";

                if ($data['kas_ppn'] == 1) {
                    $addPesan .= "Total PPn Masukan : \n".
                                'Rp. '.number_format($ppnMasukan, 0, ',', '.')."\n\n".
                                "Total PPn Keluaran : \n".
                                'Rp. '.number_format($ppnKeluaran, 0, ',', '.')."\n\n";
                }

                $addPesan .= "Total Invoice Supplier: \n".
                            'Rp. '.number_format($totalInvoiceSupplier, 0, ',', '.')."\n\n".
                            "Grand Total Invoice PPn: \n".
                            'Rp. '.number_format($grandTotalPpn, 0, ',', '.')."\n\n".
                            "Grand Total Invoice Non PPn: \n".
                            'Rp. '.number_format($grandTotalNonPpn, 0, ',', '.')."\n\n";

                $jatuhTempo = Carbon::createFromFormat('Y-m-d', $data['jatuh_tempo'])->format('d-m-Y');
                $pesan = "🟡🟡🟡🟡🟡🟡🟡🟡🟡\n".
                        "*FORM BELI BARANG*\n".
                        "🟡🟡🟡🟡🟡🟡🟡🟡🟡\n\n".
                            '*'.$kodeKas."*\n".
                        'Uraian :  *'.$data['uraian']."*\n\n".
                        'Invoice    :  *Rp. '.number_format($data['sisa'], 0, ',', '.')."*\n\n".
                        'Supplier  :  *'.$store_inv->supplier->nama."*\n".
                        'Jatuh Tempo :  *'.$jatuhTempo."*\n\n".
                        "==========================\n".
                        $addPesan.
                        "Terima kasih 🙏🙏🙏\n";

                $groupName = $data['kas_ppn'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

                $group = GroupWa::where('untuk', $groupName)->first()->nama_group;

                $kas->sendWa($group, $pesan);
            }

            return [
                'status' => 'success',
                'message' => 'Transaksi berhasil!',
                // 'data' => $store,
            ];

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

    }

    private function invoice_checkout($data)
    {
        $db = new InvoiceBelanja;
        $supplier = Supplier::find($data['supplier_id']);

        $invoice = [
            'nomor' => $db->generateNomor($data['kas_ppn']),
            'kas_ppn' => $data['kas_ppn'],
            'uraian' => $data['uraian'],
            'ppn' => $data['ppn'],
            'add_fee' => $data['add_fee'],
            'dp' => $data['dp'],
            'dp_ppn' => $data['dp_ppn'],
            'sisa' => $data['sisa'],
            'sisa_ppn' => $data['sisa_ppn'],
            'diskon' => str_replace('.', '', $data['diskon']),
            'total' => $data['total'],
            'tempo' => $data['tempo'],
            'nama_rek' => $supplier->nama_rek,
            'no_rek' => $supplier->no_rek,
            'bank' => $supplier->bank,
            'supplier_id' => $data['supplier_id'],
            'jatuh_tempo' => $data['jatuh_tempo'],
        ];

        $store = $db->create($invoice);

        $keranjang = $this->find($data['keranjang_beli_id']);
        $keranjang = $keranjang->details->load('barang');

        foreach ($keranjang as $item) {

            $baseRekap = [
                'invoice_belanja_id' => $store->id,
                'jenis' => 1, // Pembelian
                'uraian' => $data['uraian'],
                'jumlah' => $item->qty,
                'harga' => $item->harga,
                'barang_id' => $item->barang_id,
                'nama' => $item->barang->nama,
            ];

            $rekap = BarangHistory::create($baseRekap);

            $store->detail()->create([
                'invoice_belanja_id' => $store->id,
                'barang_history_id' => $rekap->id,
            ]);
        }

        return $store;

    }

    private function update_barang($data)
    {
        // Retrieve only necessary fields and group by barang_id and tipe
        $keranjang = $this->find($data['keranjang_beli_id']);
        $keranjangItems = $keranjang->details;

        // dd($keranjangItems);
        foreach ($keranjangItems as $item) {
            $barang = Barang::find($item->barang_id);
            BarangStokHarga::create([
                'barang_unit_id' => $barang->barang_unit_id,
                'barang_type_id' => $barang->barang_type_id,
                'barang_kategori_id' => $barang->barang_kategori_id,
                'barang_nama_id' => $barang->barang_nama_id,
                'barang_id' => $item->barang_id,
                'stok_awal' => $item->qty,
                'stok' => $item->qty,
                'harga_beli' => $item->harga,
            ]);
        }

        // Group by barang_id to reduce the number of queries
        // $updates = $keranjangItems->groupBy('barang_id')->map(function ($items) use ($data) {
        //     $totalJumlah = $items->sum('jumlah');
        //     $tipe = $data['kas_ppn'] == 1 ? 'ppn' : 'non-ppn';
        //     dd($items);
        //     return [
        //         'tipe' => $tipe,
        //         'totalJumlah' => $totalJumlah,
        //     ];
        // });

        // dd($updates);

        // foreach ($updates as $barang_id => $update) {
        // // Assuming BarangStokHarga has a method to increment stok in bulk or efficiently
        //     BarangStokHarga::create([
        //         'barang_id' => $barang_id,
        //         'stok' => $update['totalJumlah'],
        //         'harga_beli'
        //     ]);
        //         // BarangStokHarga::firstOrCreate(
        //         //     [
        //         //         'barang_id' => $barang_id,
        //         //         'tipe' => $update['tipe'],
        //         //     ],
        //         //     [
        //         //         'stok' => 0, // Default stok value if creating a new record
        //         //     ]
        //         // );

        //         // // Now increment the stok
        //         // BarangStokHarga::where('barang_id', $barang_id)
        //         //             ->where('tipe', $update['tipe'])
        //         //             ->increment('stok', $update['totalJumlah']);
        // }

        return true;
    }

    private function store_ppn($store)
    {
        $ppn = new PpnMasukan;

        $nominal = $store->tempo == 1 && $store->dp_ppn > 0 ? $store->dp_ppn : $store->ppn;
        $uraian = $store->tempo == 1 && $store->dp_ppn > 0 ? 'DP '.$store->uraian : $store->uraian;
        // Only proceed if nominal is greater than 0 to avoid unnecessary database entries.
        if ($nominal > 0) {
            $ppn->create([
                'invoice_belanja_id' => $store->id,
                'nominal' => $nominal,
                'saldo' => $ppn->saldoTerakhir() + $nominal,
                'uraian' => $uraian,
            ]);
        }

        return true;
    }
}
