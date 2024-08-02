<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\KonsumenTemp;
use App\Models\PpnKeluaran;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KeranjangJual extends Model
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
        return number_format($this->harga_satuan, 0, ',', '.');
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
        try {
            DB::beginTransaction();

            $keranjang = $this->where('user_id', auth()->user()->id)->get();

            if ($keranjang->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => 'Keranjang masih kosong'
                ];
            }

            if ($data['konsumen_id'] == '*') {
                $konsumen = KonsumenTemp::create([
                    'nama' => $data['nama'],
                    'no_hp' => isset($data['no_hp']) ?? null,
                    'npwp' => isset($data['npwp']) ?? null,
                    'alamat' => isset($data['alamat']) ?? null,
                ]);
                unset($data['konsumen_id']);
                $data['konsumen_temp_id'] = $konsumen->id;
            } else {
                $konsumen = Konsumen::find($data['konsumen_id']);
            }

            $dbPajak = new Pajak();
            $dbInvoice = new InvoiceJual();
            $data['total'] = $keranjang->sum('total');
            $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;
            $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
            $data['ppn'] = $keranjang->where('barang_ppn', 1)->first() ? ($data['total'] * $ppnVal / 100) : 0;
            $data['pph'] = $data['apa_pph'] == '1' ? ($data['total'] * $pphVal / 100) : 0;

            $data['nomor'] = $dbInvoice->generateNomor();
            $data['kode'] = $data['nomor'] . '/' . date('m/Y').'/PT-IUP';

            unset($data['apa_pph']);

            $data['grand_total'] = $data['total'] + $data['ppn'] - $data['pph'];

            $invoice = $dbInvoice->create($data);

            foreach ($keranjang as $item) {
                $invoice->invoice_detail()->create([
                    'barang_id' => $item->barang_id,
                    'barang_stok_harga_id' => $item->barang_stok_harga_id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $item->harga_satuan,
                    'total' => $item->total
                ]);
            }

            if ($data['ppn'] > 0) {
                $this->ppn_keluaran($invoice->id, $data['ppn']);
            }

            $this->update_stok($keranjang);

            $this->where('user_id', auth()->user()->id)->delete();
            // DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $th->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Transaksi berhasil'
        ];
    }

    private function update_stok($keranjang)
    {
        foreach ($keranjang as $item) {
            $barang = BarangStokHarga::find($item->barang_stok_harga_id);
            $barang->stok -= $item->jumlah;
            $barang->save();
        }

        return true;
    }

    private function ppn_keluaran($invoice_id, $ppn)
    {
        $db = new PpnKeluaran();

        $saldo = $db->saldoTerakhir();

        $db->create([
            'invoice_jual_id' => $invoice_id,
            'nominal' => $ppn,
            'saldo' => $saldo + $ppn
        ]);

        return true;
    }
}
