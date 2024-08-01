<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangHistory;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Pajak;
use App\Models\db\Supplier;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\PpnMasukan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Keranjang extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['nf_harga', 'nf_jumlah', 'nf_total'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function checkout($data)
    {
        $keranjang = $this->where('user_id', auth()->user()->id)
                        ->where('jenis', $data['jenis'])
                        ->where('tempo', $data['tempo'])->get();


        $ppnRate = Pajak::where('untuk', 'ppn')->first()->persen;

        $total = $keranjang->sum('total');
        $kodeKas = $data['kas_ppn'] == 1 ? 'Kas Besar PPN' : 'Kas Besar Non PPN';
        $data['add_fee'] = str_replace('.', '', $data['add_fee']);
        $data['diskon'] = str_replace('.', '', $data['diskon']);

        $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

        $data['dp_ppn'] = isset($data['dp_ppn']) && $data['dp_ppn'] == 1 ? $data['dp'] * $ppnRate/100 : 0;

        $data['ppn'] = $data['kas_ppn'] == 1 ? ($total-$data['diskon']) * $ppnRate / 100 : 0;

        $data['total'] = $total + $data['add_fee'] + $data['ppn'] - $data['diskon'];

        $data['sisa_ppn'] = $data['dp_ppn'] > 0 ? $data['ppn'] - $data['dp_ppn'] : 0;

        $data['sisa'] = $data['tempo'] == 1 ? $data['total'] - $data['dp'] - $data['dp_ppn'] : 0;

        if ($data['tempo'] == 1 && $data['jatuh_tempo']) {
            $data['jatuh_tempo'] = Carbon::createFromFormat('d-m-Y', $data['jatuh_tempo'])->format('Y-m-d');
        } else {
            $data['jatuh_tempo'] = null;
        }

        // dd($data);
        $kas = new KasBesar();
        $saldo = $kas->saldoTerakhir($data['kas_ppn']);

        if($saldo < $data['total']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas tidak mencukupi!',
            ];
        }

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
            } elseif($data['tempo'] == 1) {
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

            if ($data['tempo'] == 0) {
                $this->update_barang($data);
            }


            if ($data['kas_ppn'] == 1) {
                $this->store_ppn($store_inv);
            }

            $this->where('user_id', auth()->user()->id)->where('jenis', $data['jenis'])
                    ->where('tempo', $data['tempo'])->delete();

            DB::commit();
            // check if there is $store
            if ($state == 1) {
                $dbPPn = new PpnMasukan();
                $ppnMasukan = $dbPPn->saldoTerakhir();

                $getKas = $kas->getKas();

                if ($data['kas_ppn'] == 1) {
                    $addPesan = "Sisa Saldo Kas Besar: \n".
                                "Rp. ".number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                                "Total Modal Investor PPN: \n".
                                "Rp. ".number_format($getKas['modal_investor_ppn'], 0, ',', '.')."\n\n";
                } else {
                    $addPesan = "Sisa Saldo Kas Besar: \n".
                                "Rp. ".number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n".
                                "Total Modal Investor Non PPN: \n".
                                "Rp. ".number_format($getKas['modal_investor_non_ppn'], 0, ',', '.')."\n\n";
                }

                $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*FORM BELI BARANG*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                             "*".$kodeKas."*\n".
                            "Uraian :  *".$store->uraian."*\n\n".
                            "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            "Bank      : ".$store->bank."\n".
                            "Nama    : ".$store->nama_rek."\n".
                            "No. Rek : ".$store->no_rek."\n\n".
                            "==========================\n".
                            $addPesan.
                            "Grand Total Modal Investor : \n".
                            "Rp. ".number_format($getKas['modal_investor_terakhir'], 0, ',', '.')."\n\n".
                            "Total PPn Masukan : \n".
                            "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                $groupName = $data['kas_ppn'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

                $group = GroupWa::where('untuk', $groupName)->first()->nama_group;

                $kas->sendWa($group, $pesan);
            }


            return [
                'status' => 'success',
                'message' => 'Transaksi berhasil!',
                'data' => $store,
            ];


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

    }

    private function store_ppn($store)
    {
        $ppn = new PpnMasukan();

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

    private function invoice_checkout($data)
    {
        $db = new InvoiceBelanja();
        $supplier = Supplier::find($data['supplier_id']);

        $invoice = [
            'nomor' => $db->generateNomor(),
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

        $keranjang = $this->with('barang')->where('user_id', auth()->user()->id)->where('jenis', $data['jenis'])->where('tempo', $data['tempo'])->get();

        foreach ($keranjang as $item) {

            $baseRekap = [
                'invoice_belanja_id' => $store->id,
                'jenis' => 1, //Pembelian
                'uraian' => $data['uraian'],
                'jumlah' => $item->jumlah,
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
        $keranjangItems = $this->where('user_id', auth()->user()->id)
            ->where('jenis', $data['jenis'])
            ->where('tempo', $data['tempo'])
            ->get();

        // dd($keranjangItems);
        foreach($keranjangItems as $item){
            $barang = Barang::find($item->barang_id);
            BarangStokHarga::create([
                'barang_unit_id' => $barang->barang_unit_id,
                'barang_type_id' => $barang->barang_type_id,
                'barang_kategori_id' => $barang->barang_kategori_id,
                'barang_nama_id' => $barang->barang_nama_id,
                'barang_id' => $item->barang_id,
                'stok' => $item->jumlah,
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
}
