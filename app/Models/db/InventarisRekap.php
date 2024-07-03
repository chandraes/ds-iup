<?php

namespace App\Models\db;

use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\Rekening;
use App\Models\transaksi\InventarisInvoice;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class InventarisRekap extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_harga_satuan', 'nf_jumlah', 'nf_total', 'tanggal'];

    public function invoice()
    {
        return $this->hasOne(InventarisInvoice::class, 'inventaris_id');
    }

    public function jenis()
    {
        return $this->belongsTo(InventarisJenis::class, 'inventaris_jenis_id');
    }

    public function getNfHargaSatuanAttribute()
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

    public function getTanggalAttribute()
    {
        return $this->created_at->format('d-m-Y');
    }

    public function createRekap($data, $id)
    {
        $data['jumlah'] = str_replace('.', '', $data['jumlah']);
        $data['harga_satuan'] = str_replace('.', '', $data['harga_satuan']);

        $data['total'] = $data['jumlah'] * $data['harga_satuan'];

        try {
            DB::beginTransaction();

            $rekap = $this->find($id);

            $jumlahNegatif = $data['jumlah'] * -1;
            $total = $jumlahNegatif * $rekap->harga_satuan;

            $inventaris = $this->create([
                'inventaris_jenis_id' => $rekap->inventaris_jenis_id,
                'jenis' => 0,
                'status' => 'pengurangan',
                'uraian' => $data['uraian'],
                'jumlah' => $jumlahNegatif,
                'harga_satuan' => $rekap->harga_satuan,
                'total' => $total,
            ]);

            // Increase $rekap->pengurangan by $data['jumlah']
            $rekap->increment('pengurangan', $data['jumlah']);

            if ($data['total'] > 0) {
                $kas = new KasBesar();

                $rekening = Rekening::where('untuk', 'kas-besar')->first();

                $store = $kas->create([
                    'uraian' => $data['uraian'],
                    'jenis' => 1,
                    'nominal' => $data['total'],
                    'saldo' => $kas->saldoTerakhir() + $data['total'],
                    'nama_rek' => $rekening->nama_rek,
                    'no_rek' => $rekening->no_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $kas->modalInvestorTerakhir()
                ]);

                $inv = InventarisJenis::find($data[$rekap->inventaris_jenis_id]);
                $inv_tot = InventarisRekap::sum('total');

                $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*FORM INVENTARIS*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
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
                        "Sub total ".$inv->kategori->nama." : \n".
                        "Rp. ".number_format($inv->kategori->sum_total, 0, ',', '.')."\n\n".
                        "Grand Total Inventaris: \n".
                        "Rp. ".number_format($inv_tot, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

                // Retrieve the group name once, as it's the same for both conditions
                $group = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

                // Send the message
                $kas->sendWa($group, $pesan);

            }

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Berhasil membuat Aksi Inventaris!!'
            ];


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal membuat Aksi Inventaris!! '. $th->getMessage()
            ];
        }
    }

}
