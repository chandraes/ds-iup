<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\GroupWa;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InvoiceJualSales extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'dpp', 'nf_ppn',
        'nf_grand_total', 'nf_dp', 'nf_dp_ppn', 'nf_sisa_ppn',
        'nf_sisa_tagihan',  'dpp_setelah_diskon', 'sistem_pembayaran_word', 'tanggal_en',
    ];

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getTanggalEnAttribute()
    {
        return Carbon::parse($this->created_at)->format('Y-m-d');
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function invoice_detail()
    {
        return $this->hasMany(InvoiceJualSalesDetail::class, 'invoice_jual_sales_id');
    }

    public function order_inden()
    {
        return $this->hasMany(OrderInden::class, 'invoice_jual_sales_id');
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function getDppAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getDppSetelahDiskonAttribute()
    {
        return number_format($this->total - $this->diskon, 0, ',', '.') ?? 0;
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getNfAddFeeAttribute()
    {
        return number_format($this->add_fee, 0, ',', '.');
    }

    public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getNfGrandTotalAttribute()
    {
        return number_format($this->grand_total, 0, ',', '.');
    }

    public function getNfDpAttribute()
    {
        return number_format($this->dp, 0, ',', '.') ?? 0;
    }

    public function getNfDpPpnAttribute()
    {
        return number_format($this->dp_ppn, 0, ',', '.') ?? 0;
    }

    public function getNfSisaPpnAttribute()
    {
        return number_format($this->sisa_ppn, 0, ',', '.');
    }

    public function getNfSisaTagihanAttribute()
    {
        return number_format($this->sisa_tagihan, 0, ',', '.');
    }

    public function getSistemPembayaranWordAttribute()
    {
        return match ($this->sistem_pembayaran) {
            1 => 'Cash',
            2 => 'Tempo',
            3 => 'Titipan',
            default => '-',
        };
    }

    public function pembayaran($val)
    {
        // Map numeric values to payment types:
        // 1 => Cash, 2 => Tempo, 3 => Titipan
        $paymentTypes = [
            1 => 'Cash',
            2 => 'Tempo',
            3 => 'Titipan',
        ];

        if (array_key_exists($val, $paymentTypes)) {
            return $paymentTypes[$val];
        } else {
            return 'Unknown';
        }


    }

    private function updateStok($data)
    {
        foreach ($data as $d) {
            // Temukan barang berdasarkan ID
            $barang = BarangStokHarga::find($d->barang_stok_harga_id);

            // Pastikan barang ditemukan sebelum melanjutkan
            if (!$barang) {
                throw new \Exception("Barang dengan ID {$d->barang_stok_harga_id} tidak ditemukan.");
            }

            // Update stok barang
            $barang->stok += $d->jumlah;

            // Jika barang disembunyikan, tampilkan kembali
            if ($barang->hide) {
                $barang->hide = 0;
            }

            // Simpan perubahan
            $barang->save();
        }

        return true;
    }

    public function order_void($id)
    {
        $invoice = InvoiceJualSales::find($id);

        $detail = $invoice->invoice_detail;
        $pesan = '';

        try {

            DB::beginTransaction();
             // Update stok barang
            $this->updateStok($detail);

            // Tambahkan Header Merah VOID - Sistem Pembayaran (Kalau Cash: Merah-merah, tempo: Merah-Biru)
            $header_bawah = $invoice->sistem_pembayaran == 1 ? "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n" : "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n";
            $pesan .= "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "VOID - ". $invoice->sistem_pembayaran_word.
                        $header_bawah;

            $kota = $invoice->konsumen->kabupaten_kota ? $invoice->konsumen->kabupaten_kota->nama_wilayah : '';
            $pesan = $invoice->konsumen->kode_toko->kode.' '.$invoice->konsumen->nama."\n".
                    $invoice->konsumen->alamat."\n".
                    $kota."\n\n";

            $n = 1;
            foreach ($invoice->load('invoice_detail.barang.barang_nama', 'invoice_detail.barang.satuan')->invoice_detail as $d) {
                $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""." \n".$d->barang->merk.""."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n";
            }

            // Hapus detail invoice
            $invoice->invoice_detail()->delete();

            // Hapus invoice
            $invoice->delete();

            DB::commit();

            $dbWa = new GroupWa;

            $tujuan = $dbWa->where('untuk', 'sales-order')->first()->nama_group;

            // $pesan .= "\nNote: \n".
            //         $invoice->sistem_pembayaran_word."\n".
            //         "*VOID";

            $dbWa->sendWa($tujuan, $pesan);

            $no_konsumen = $invoice->konsumen->no_hp;
            $no_konsumen = str_replace('-', '', $no_konsumen);

            // check length no hp
            if (strlen($no_konsumen) > 10) {
                $dbWa->sendWa($no_konsumen, $pesan);
            }

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Sales Order gagal dihapus',
            ];
        }


        return [
            'status' => 'success',
            'message' => 'Sales Order berhasil dihapus',
        ];
    }

    public function update_order($data)
    {
        $invoice = InvoiceJualSales::find($data['id']);

        try {

            $dipungut = isset($data['dipungut']) ? $data['dipungut'] : 1;

            $dbPajak = new Pajak();
            $dbInvoice = new InvoiceJualSales;

            $data['total'] = $invoice->invoice_detail->where('deleted', 0)->sum('total');
            $data['ppn'] = 0;
            $data['diskon'] = 0;
            $data['add_fee'] = 0;

            $data['titipan'] = $data['pembayaran'] == 3 ? 1 : 0;

            // $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;

            // $dppSetelahDiskon = $data['total'] - $data['diskon'];
            foreach ($invoice->invoice_detail->where('deleted', 0) as $detail) {
                $data['ppn'] += $detail->ppn * $detail->jumlah;
                $data['diskon'] += $detail->diskon * $detail->jumlah;
            }

            $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

            $data['dp_ppn'] = isset($data['dp_ppn']) ? str_replace('.', '', $data['dp_ppn']) : 0;

            if ($dipungut == 1) {
                $data['grand_total'] = $data['total'];
            } else {
                $data['grand_total'] = $data['total'];
            }

            $data['ppn_dipungut'] = $dipungut;

            $data['sisa_tagihan'] = $data['ppn_dipungut'] == 1 ? $data['grand_total'] - ($data['dp'] + $data['dp_ppn']) : $data['grand_total'] - $data['dp'];
            $data['sisa_ppn'] = $data['ppn'] - $data['dp_ppn'];

            $data['sistem_pembayaran'] = $data['pembayaran'];

            DB::beginTransaction();

            $invoice->update($data);

            foreach ($invoice->invoice_detail->where('deleted', 1) as $item) {
                $stok = BarangStokHarga::find($item->barang_stok_harga_id);
                $stok->stok += $item->jumlah;
                $stok->save();

                InvoiceJualSalesDetail::where('id', $item->id)->delete();
            }


            DB::commit();

            $dbWa = new GroupWa;
            $kota = $invoice->konsumen->kabupaten_kota ? $invoice->konsumen->kabupaten_kota->nama_wilayah : '';
            $pesan = "*".$invoice->konsumen->kode_toko->kode.' '.$invoice->konsumen->nama."*\n".
                    $invoice->konsumen->alamat."\n".
                    $kota."\n\n";

            // create tanggal from created_at invoice with format d F Y in indonesian
            $tanggal = Carbon::parse($invoice->created_at)->translatedFormat('d F Y');

            $barang = $invoice->kas_ppn == 1 ? 'Barang A' : 'Barang B';
            $pesan .= "*Order* : ".$tanggal."\n".
                    $barang.":\n";

            $grandTotal = $data['grand_total'];

            $n = 1;
            foreach ($invoice->load('invoice_detail.barang.barang_nama', 'invoice_detail.barang.satuan')->invoice_detail as $d) {
                $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
            }

            $pembayaran = $data['pembayaran'] == 1 ? 'Cash' :  $dbInvoice->pembayaran($data['sistem_pembayaran']).": ".$invoice->konsumen->tempo_hari . ' Hari';

            $sales = $invoice->karyawan->nama;

            $pesan .= "==========================\n";

            $pesan .= "Note: Edited\n".
                         "•⁠ *".$pembayaran."*\n".
                        "•⁠ Sales : ".$sales."\n".
                        "•⁠ CP : ".$invoice->karyawan->no_hp."\n";

            $pesan .= "•⁠ Order: *Rp. ". number_format($grandTotal, 0,',','.')."*\n\n";

            $pesan .= "No Kantor: *0853-3939-3918* \n";

            $tujuan = $dbWa->where('untuk', 'sales-order')->first()->nama_group;

            $dbWa->sendWa($tujuan, $pesan);

            $no_konsumen = $invoice->konsumen->no_hp;
            $no_konsumen = str_replace('-', '', $no_konsumen);

            // check length no hp
            if (strlen($no_konsumen) > 10) {
                $dbWa->sendWa($no_konsumen, $pesan);
            }
            // Update invoice
        } catch (\Throwable $th) {
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Sales Order gagal diupdate. '. $th->getMessage(),
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Sales Order berhasil diupdate',
        ];
    }

}
