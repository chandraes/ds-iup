<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\BarangHistory;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Konsumen;
use App\Models\db\Supplier;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\PpnMasukan;
use App\Models\Rekening;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceBelanja extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [
        'nf_diskon',
        'nf_ppn',
        'nf_add_fee',
        'nf_total',
        'nf_dp',
        'nf_dp_ppn',
        'nf_sisa',
        'nf_sisa_ppn',
        'id_jatuh_tempo',
        'kode',
        'tanggal',
        'dpp',
        'raw_dpp',
        'dpp_setelah_diskon'
    ];

    public function getDppAttribute()
    {
        $dpp = $this->total + $this->diskon - $this->ppn - $this->add_fee;

        return number_format($dpp, 0, ',', '.');
    }

    public function getRawDppAttribute()
    {
        return $this->total + $this->diskon - $this->ppn - $this->add_fee;
    }

    public function getDppSetelahDiskonAttribute()
    {
        return number_format($this->raw_dpp - $this->diskon, 0, ',', '.');
    }

    public function generateNomor()
    {
        return $this->max('nomor') + 1;
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function detail()
    {
        return $this->hasMany(InvoiceBelanjaDetail::class, 'invoice_belanja_id');
    }

    public function items()
    {
        return $this->hasManyThrough(
            BarangHistory::class,
            InvoiceBelanjaDetail::class,
            'invoice_belanja_id',
            'id',
            'id',
            'barang_history_id'
        );
    }

    public function getKodeAttribute()
    {
        return 'BB' . str_pad($this->nomor, 2, '0', STR_PAD_LEFT);
    }

    public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfAddFeeAttribute()
    {
        return number_format($this->add_fee, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getNfDpAttribute()
    {
        return number_format($this->dp, 0, ',', '.');
    }

    public function getNfDpPpnAttribute()
    {
        return number_format($this->dp_ppn, 0, ',', '.');
    }

    public function getNfSisaAttribute()
    {
        return number_format($this->sisa, 0, ',', '.');
    }

    public function getNfSisaPpnAttribute()
    {
        return number_format($this->sisa_ppn, 0, ',', '.');
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getIdJatuhTempoAttribute()
    {
        // use Carbon\Carbon;
        return $this->jatuh_tempo ? Carbon::parse($this->jatuh_tempo)->format('d-m-Y') : '';
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(tanggal) as tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'desc')
            ->get();
    }

    public function invoiceByMonth($bulan, $tahun)
    {
        return $this->whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->orderBy('tanggal', 'desc')
            ->get();
    }

    public function void($id)
    {
        $inv = $this->find($id);

        try {
            DB::beginTransaction();

            $inv->update([
                'void' => 1
            ]);
            // dd($inv);
            if ($inv->dp_ppn > 0) {
                $this->store_ppn($inv, 1);
            }

            // delete detail
            $inv->detail()->delete();

            $kas = new KasBesar();

            if ($inv->dp > 0) {
                $kasMana = $inv->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $rekening = Rekening::where('untuk', $kasMana)->first();
                $total = $inv->dp + $inv->dp_ppn;
                $store = $kas->create([
                    'invoice_belanja_id' => $inv->id,
                    'ppn_kas' => $inv->kas_ppn,
                    'uraian' => 'Void ' . $inv->uraian,
                    'jenis' => '1',
                    'nominal' => $total,
                    'saldo' => $kas->saldoTerakhir($inv->kas_ppn) + $total,
                    'nama_rek' => $rekening->nama_rek,
                    'no_rek' => $rekening->no_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $kas->modalInvestorTerakhir($inv->kas_ppn),
                ]);
            }

            DB::commit();

            if ($inv->dp_ppn > 0) {

                $dbPPn = new PpnMasukan();
                $ppnMasukan = $dbPPn->saldoTerakhir();

                $getKas = $kas->getKas();

                $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                            "*VOID BELI BARANG*\n".
                            "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                            "Uraian :  *".$store->uraian."*\n\n".
                            "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            "Bank      : ".$store->bank."\n".
                            "Nama    : ".$store->nama_rek."\n".
                            "No. Rek : ".$store->no_rek."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar PPN: \n".
                            "Rp. ".number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                            "Sisa Saldo Kas Besar  NON PPN: \n".
                            "Rp. ".number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            "Rp. ".number_format($getKas['modal_investor_terakhir'], 0, ',', '.')."\n\n".
                            "Total PPn Masukan : \n".
                            "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                $groupName = $inv->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $group = GroupWa::where('untuk', $groupName)->first()->nama_group;

                $kas->sendWa($group, $pesan);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil membatalkan invoice'
            ];


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal membatalkan invoice. '.$th->getMessage()
            ];
        }
    }

    private function store_ppn($store, $jenis)
    {
        $ppn = new PpnMasukan();
        $nominal = $jenis == 1 ? -$store->dp_ppn : $store->sisa_ppn;
        $uraian = ($jenis == 1 ? "Void " : "Pelunasan ") . $store->uraian;
        $saldo = $ppn->saldoTerakhir() + ($jenis == 1 ? -$store->dp_ppn : $store->sisa_ppn);

        if ($nominal > 0) {
            $ppn->create([
                'invoice_belanja_id' => $store->id,
                'nominal' => $nominal,
                'saldo' => $saldo,
                'uraian' => $uraian,
            ]);
        }

        return true;
    }

    public function bayar($id)
    {
        $kas = new KasBesar();
        $inv = $this->find($id);
        // dd($inv);
        if ($kas->saldoTerakhir($inv->kas_ppn) < $inv->sisa) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas tidak mencukupi!!'
            ];
        }

        try {
            DB::beginTransaction();

            $inv->update([
                'tempo' => 0
            ]);
            // dd($inv);
            if ($inv->sisa_ppn > 0) {
                $this->store_ppn($inv, 2);
            }

            $kasMana = $inv->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

            $rekening = Supplier::find($inv->supplier_id);

            $total = $inv->sisa;

            $store = $kas->create([
                'invoice_belanja_id' => $inv->id,
                'ppn_kas' => $inv->kas_ppn,
                'uraian' => 'Pelunasan ' . $inv->uraian,
                'jenis' => '0',
                'nominal' => $total,
                'saldo' => $kas->saldoTerakhir($inv->kas_ppn) - $total,
                'nama_rek' => $rekening->nama_rek,
                'no_rek' => $rekening->no_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir($inv->kas_ppn),
            ]);

            $this->update_stok($inv->id);

            DB::commit();

            $dbPPn = new PpnMasukan();
            $ppnMasukan = $dbPPn->saldoTerakhir();

            $getKas = $kas->getKas();

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*PELUNASAN BELI BARANG*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian :  *".$store->uraian."*\n\n".
                        "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->nama_rek."\n".
                        "No. Rek : ".$store->no_rek."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar PPN: \n".
                        "Rp. ".number_format($getKas['saldo_ppn'], 0, ',', '.')."\n\n".
                        "Sisa Saldo Kas Besar  NON PPN: \n".
                        "Rp. ".number_format($getKas['saldo_non_ppn'], 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($getKas['modal_investor_terakhir'], 0, ',', '.')."\n\n".
                        "Total PPn Masukan : \n".
                        "Rp. ".number_format($ppnMasukan, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            $group = GroupWa::where('untuk', $kasMana)->first()->nama_group;

            $kas->sendWa($group, $pesan);


            return [
                'status' => 'success',
                'message' => 'Berhasil melunasi invoice!!'
            ];


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal melunasi invoice. '.$th->getMessage()
            ];
        }

    }

    private function update_stok($id)
    {
        $inv = $this->find($id);

        $items = $inv->items;
        $tipe = $inv->kas_ppn == 1 ? 'ppn' : 'non-ppn';
        // dd($items);
        foreach ($items as $item) {

            // dd($item);
            BarangStokHarga::create([
                'barang_id' => $item->barang_id,
                'stok' => $item->jumlah,
                'harga_beli' => $item->harga,
            ]);
        }

        return true;
    }


}
