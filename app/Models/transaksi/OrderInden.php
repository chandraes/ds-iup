<?php

namespace App\Models\transaksi;

use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use App\Models\GroupWa;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class OrderInden extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function detail()
    {
        return $this->hasMany(OrderIndenDetail::class);
    }

    public function getTanggalAttribute()
    {
        return $this->created_at->translatedFormat('Y-m-d');
    }

    public function order_void($id)
    {
        $order = $this->with(['konsumen.kode_toko', 'konsumen.kabupaten_kota', 'detail.barang.barang_nama', 'detail.barang.satuan'])->where('id',$id)->first();

        $pesan = $order->konsumen->kode_toko->kode. ' '.$order->konsumen->nama."\n".
                    $order->konsumen->alamat."\n".
                    $order->konsumen->kota."\n\n";

        $pesan .= "==========================\n";
        $pesan .= "\n*Pre order*: \n";

        $no = 1;
        foreach ($order->detail as $d) {
            $pesan .= $no++.'. ['.$d->barang->barang_nama->nama." (".$d->barang->kode.")"."\n(".$d->barang->merk.")"."]....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
        }

        $pesan .= "\n==========================\n";
        $pesan .= "Note: VOID\n";

        try {
            DB::beginTransaction();

            $order->delete();

            DB::commit();

            $dbWa = new GroupWa;

            $tujuan = $dbWa->where('untuk', 'sales-order')->first()->nama_group;

            $dbWa->sendWa($tujuan, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil menghapus order inden'
            ];

        } catch (\Throwable $th) {

            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage()
            ];
        }
    }

    public function update_order($id)
    {
        $order = $this->find($id);

        $order = $this->with(['konsumen.kode_toko', 'konsumen.kabupaten_kota', 'detail.barang.barang_nama', 'detail.barang.satuan'])->where('id', $id)->first();

        $detail = $order->detail->where('deleted', 1);

        try {
            DB::beginTransaction();

            foreach ($detail as $d) {
                $d->delete();
            }
            // Reload the relation to get the updated count after deletion
            $order->load('detail');
            $count = $order->detail->count();

            $order->update([
                'jumlah' => $count,
            ]);

            DB::commit();

            $dbWa = new GroupWa;
            $tujuan = $dbWa->where('untuk', 'sales-order')->first()->nama_group;

             $pesan = "*".$order->konsumen->kode_toko->kode. ' '.$order->konsumen->nama."*\n".
                    $order->konsumen->alamat."\n".
                    $order->konsumen->kota."\n\n";

            $pesan .= "==========================\n";
            $pesan .= "\n*Pre order*: \n";

            $no = 1;
            foreach ($order->detail as $d) {
                $pesan .= $no++.'. '.$d->barang->barang_nama->nama." (".$d->barang->kode.")"."\n(".$d->barang->merk.")"."....... ". $d->jumlah.' ('.$d->barang->satuan->nama.")\n\n";
            }

            $pesan .= "\n==========================\n";
            $pesan .= "Note: Edited\n";

            $dbWa->sendWa($tujuan, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil mengupdate order inden'
            ];


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $th->getMessage()
            ];
        }




    }
}
