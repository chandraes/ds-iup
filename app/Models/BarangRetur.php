<?php

namespace App\Models;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Konsumen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangRetur extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tipe_text', 'status_text', 'tanggal_en'];

    public function getTanggalEnAttribute()
    {
        return Carbon::parse($this->created_at)->translatedFormat('Y-m-d');
    }

    public function getTipeTextAttribute()
    {
        return $this->tipe == 1 ? "Dari Supplier" : "Dari Konsumen";
    }

    public function getStatusTextAttribute()
    {
        // 1 = diajukan, 2 = diproses, 3 = selesai, 4 = void
        return match ($this->status) {
            1 => 'Diajukan',
            2 => 'Diproses',
            3 => 'Selesai',
            4 => 'Void',
            default => 'Draft',
        };

    }

    public function barang_unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function details()
    {
        return $this->hasMany(BarangReturDetail::class);
    }

     private function update_stok($keranjang)
    {
        foreach ($keranjang as $item) {
            $barang = BarangStokHarga::find($item->barang_stok_harga_id);

            if ($barang->stok < $item->qty) {

                return [
                    'id' => $item->id,
                    'status' => false,
                ];
            }

            $barang->stok -= $item->qty;
            $barang->save();
        }

        return true;
    }

    public function checkout_retur($id)
    {
        $stok_update = null;
        $data = $this->where('id', $id)->with(['details.stok', 'barang_unit', 'konsumen'])->first();

        if ($data->details->count() <= 0) {
            return ['status' => 'error', 'message' => 'Keranjang masih kosong'];
        }

        if ($data->status > 0) {
            return ['status' => 'error', 'message' => 'Retur sudah diproses/selesai'];
        }


        try {
             DB::beginTransaction();

             $calculate_stok = $this->update_stok($data->details);

            if (isset($calculate_stok['status']) && $calculate_stok['status'] == false) {
                $stok_update = $calculate_stok;
                throw new \Exception('Terdapat stok yang kurang dari barang yang dijual');
            }

            $data->update([
                'status' => 1,
            ]);

            DB::commit();

            $dbWa = new GroupWa;
            $pesan = '';
            $tanggal = Carbon::now()->translatedFormat('d F Y');

            $pesan = "*".$data->barang_unit->nama."*\n";

            if ($data['tipe'] == 2) {
                $kota = $data->konsumen->kabupaten_kota ? $data->konsumen->kabupaten_kota->nama_wilayah : '';

                $pesan .= "*".$data->konsumen->kode_toko->kode. ' '.$data->konsumen->nama."*\n".
                    $data->konsumen->alamat."\n".
                    $kota."\n\n";
            } else {
                $pesan .= "\n";
            }

            $pesan .= "*Barang Retur* : ".$tanggal."\n\n";

            //  $pesan = "Barang A: \n";

            $n = 1;
            foreach ($data->load(['details.stok.barang.satuan'])->details as $d) {
                $pesan .= $n++.'. '.$d->stok->barang_nama->nama." ".$d->stok->barang->kode.""."\n".$d->stok->barang->merk." "."....... ". $d->qty.' ('.$d->stok->barang->satuan->nama.")";
                $pesan .= "\n\n";
            }

            $tujuan = $dbWa->where('untuk', 'barang-retur')->first()->nama_group;

            $dbWa->sendWa($tujuan, $pesan);

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            if (isset($stok_update['status']) && $stok_update['status'] == false) {
                BarangReturDetail::where('id', $stok_update['id'])->update([
                    'stok_kurang' => 1,
                ]);
            }

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        return ['status' => 'success', 'message' => 'Retur berhasil diproses'];
    }
}
