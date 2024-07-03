<?php

namespace App\Models\transaksi;

use App\Models\db\InventarisJenis;
use App\Models\db\InventarisRekap;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\PesanWa;
use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventarisInvoice extends Model
{
    use HasFactory;
    protected $guarded = ['id'];
    protected $appends = ['tanggal', 'nf_jumlah', 'nf_harga_satuan', 'nf_ppn', 'nf_total', 'nf_dp', 'id_tanggal_jatuh_tempo', 'nf_sisa_bayar', 'nf_diskon', 'nf_add_fee'];


    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
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

    public function getIdTanggalJatuhTempoAttribute()
    {
        return date('d-m-Y', strtotime($this->tanggal_jatuh_tempo));
    }

    public function getSisaBayarAttribute()
    {
        return $this->total - $this->dp;
    }

    public function getNfSisaBayarAttribute()
    {
        return number_format($this->sisa_bayar, 0, ',', '.');
    }

    public function inventaris()
    {
        return $this->belongsTo(InventarisRekap::class, 'inventaris_id');
    }

    public function rekapInvoice($month, $year)
    {
        return $this->where('lunas', 1)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function beliInventaris($data)
    {
        try {
            DB::beginTransaction();

            $kas = new KasBesar();
            $saldo = $kas->saldoTerakhir();

            $total = ($data['jumlah'] * $data['harga_satuan']) + $data['ppn'] + $data['add_fee'] - $data['diskon'];

            $checker = $data['pembayaran'] == 1 ? $total : str_replace('.', '', $data['dp']);

            isset($data['dp']) ? $data['dp'] = str_replace('.', '', $data['dp']) : 0;

            if($saldo < $checker){
                return [
                    'status' => 'error',
                    'message' => 'Saldo kas besar tidak mencukupi! Sisa saldo: Rp. '.number_format($saldo, 0, ',', '.'),
                ];
            }

            $inventaris = InventarisRekap::create([
                'inventaris_jenis_id' => $data['inventaris_jenis_id'],
                'status' => $data['status'],
                'jenis' => $data['jenis'],
                'uraian' => $data['uraian'],
                'jumlah' => $data['jumlah'],
                'harga_satuan' => $data['harga_satuan'],
                'total' => $data['jumlah'] * $data['harga_satuan'],
            ]);

            $invoiceData = [
                'inventaris_id' => $inventaris->id,
                'uraian' => $data['uraian'],
                'pembayaran' => $data['pembayaran'],
                'jumlah' => $data['jumlah'],
                'harga_satuan' => $data['harga_satuan'],
                'ppn' => $data['ppn'],
                'add_fee' => $data['add_fee'],
                'diskon' => $data['diskon'],
                'total' => $total,
                'nama_rek' => $data['nama_rek'],
                'no_rek' => $data['no_rek'],
                'bank' => $data['bank'],
                // Set 'lunas' based on 'pembayaran' value
                'lunas' => $data['pembayaran'] == 1 ? 1 : 0,
            ];

            // Additional handling for 'pembayaran' == 2
            if ($data['pembayaran'] == 2) {
                $invoiceData['dp'] = str_replace('.', '', $data['dp']);
                $invoiceData['tanggal_jatuh_tempo'] = date('Y-m-d', strtotime($data['tanggal_jatuh_tempo']));
            }

            // Create invoice
            $invoice = $this->create($invoiceData);

            $nominal = $data['pembayaran'] == 2 && $data['dp'] > 0 ? $data['dp'] : $total;

            // Create the store entry outside the conditional, as it's mostly similar
            $store = $kas->create([
                'uraian' => $data['uraian'],
                'jenis' => 0,
                'nominal' => $nominal,
                'saldo' => $saldo - $nominal,
                'no_rek' => $data['no_rek'],
                'nama_rek' => $data['nama_rek'],
                'bank' => $data['bank'],
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir(),
                'invoice_inventaris_id' => $invoice->id,
            ]);

            $inv = InventarisJenis::find($data['inventaris_jenis_id']);
            $inv_tot = InventarisRekap::sum('total');

            // Construct the message dynamically
            $uraianText = $data['pembayaran'] == 2 && $data['dp'] > 0 ? "DP ".$store->uraian : $store->uraian;

            // tampilkan setiap sub total per kategori

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                     "*FORM INVENTARIS*\n".
                     "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                     "Kategori : ".$inv->kategori->nama."\n\n".
                     "Uraian :  *".$uraianText."*\n\n".
                     "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                     "Ditransfer ke rek:\n\n".
                     "Bank      : ".$store->bank."\n".
                     "Nama    : ".$store->nama_rek."\n".
                     "No. Rek : ".$store->no_rek."\n\n".
                     "==========================\n".
                     "Sisa Saldo Kas Besar : \n".
                     "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                     "Total Modal Investor : \n".
                     "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                     "Sub total ".$inv->kategori->nama." : \n".
                     "Rp. ".number_format($inv->kategori->sum_total, 0, ',', '.')."\n\n".
                     "Grand Total Inventaris: \n".
                     "Rp. ".number_format($inv_tot, 0, ',', '.')."\n\n".
                     "Terima kasih 🙏🙏🙏\n";

            // Retrieve the group name once, as it's the same for both conditions
            $group = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            // Send the message
            $this->sendWa($group, $pesan);

            DB::commit();

            $res = [
                'status' => 'success',
                'message' => 'Data berhasil disimpan',
            ];

            return $res;

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            $res = [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];

            return $res;
        }
    }

    public function pelunasan($id)
    {
        $invoice = $this->find($id);

        $kas = new KasBesar();

        $saldo = $kas->saldoTerakhir();

        if($saldo < $invoice->sisa_bayar){
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi! Sisa saldo: Rp. '.number_format($saldo, 0, ',', '.'),
            ];
        }

        try {
            DB::beginTransaction();

            $store = $kas->create([
                'uraian' => 'Pelunasan '.$invoice->uraian,
                'jenis' => 0,
                'nominal' => $invoice->sisa_bayar,
                'saldo' => $saldo - $invoice->sisa_bayar,
                'no_rek' => $invoice->no_rek,
                'nama_rek' => $invoice->nama_rek,
                'bank' => $invoice->bank,
                'modal_investor_terakhir' => $kas->modalInvestorTerakhir(),
                'invoice_inventaris_id' => $invoice->id,
            ]);

            $invoice->update([
                'lunas' => 1,
            ]);

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                     "*FORM INVENTARIS*\n".
                     "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                     "Uraian :  *".$store->uraian."*\n\n".
                     "Nilai    :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                     "Ditransfer ke rek:\n\n".
                     "Bank      : ".$store->bank."\n".
                     "Nama    : ".$store->nama_rek."\n".
                     "No. Rek : ".$store->no_rek."\n\n".
                     "==========================\n".
                     "Sisa Saldo Kas Besar : \n".
                     "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                     "Total Modal Investor : \n".
                     "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                     "Terima kasih 🙏🙏🙏\n";

            // Retrieve the group name once, as it's the same for both conditions
            $group = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            // Send the message
            $this->sendWa($group, $pesan);

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Pelunasan berhasil',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal melakukan pelunasan, '.$th->getMessage(),
            ];
        }

    }

    private function sendWa($tujuan, $pesan)
    {
        $send = new StarSender($tujuan, $pesan);
        $res = $send->sendGroup();

        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $tujuan,
            'status' => $status,
        ]);
    }
}
