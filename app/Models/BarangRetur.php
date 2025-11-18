<?php

namespace App\Models;

use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Barang\BarangUnit;
use App\Models\db\Karyawan;
use App\Models\db\Konsumen;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class BarangRetur extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tipe_text', 'status_text', 'tanggal_en', 'kode', 'status_badge', 'action'];

    public function generateNomor()
    {
        $lastNomor = self::max('nomor');
        return $lastNomor ? $lastNomor + 1 : 1;
    }

    public function getKodeAttribute()
    {
        return 'BR-' . str_pad($this->nomor, 3, '0', STR_PAD_LEFT);
    }

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
        // 1 = diajukan, 2 = Diterima, 3 = Diproses, 4 = Selesai, 99 = void
        return match ($this->status) {
            1 => 'Diajukan',
            2 => 'Diterima',
            3 => 'Diproses',
            4 => 'Selesai',
            99 => 'Void',
            default => 'Draft',
        };

    }

    public function getStatusBadgeAttribute()
    {
        return match ((int)$this->status) {
            0 => '<span class="badge bg-secondary">Draft</span>',
            1 => '<span class="badge bg-info">Diajukan</span>',
            2 => '<span class="badge bg-warning text-dark">Diterima</span>', // <-- Badge Baru
            3 => '<span class="badge bg-primary">Diproses</span>',
            4 => '<span class="badge bg-success">Selesai</span>',
            99 => '<span class="badge bg-danger">Void</span>',
            default => '<span class="badge bg-dark">Unknown</span>',
        };
    }

    public function getActionAttribute()
    {
        $actions = '';

        if ($this->status == 1) { // Diajukan
            $actions .= '<button type="button" class="btn btn-warning btn-sm me-1" data-bs-toggle="tooltip" title="Terima Retur" onclick="terimaOrder('.$this->id.')">
                            <i class="fa fa-check-circle"></i> Terima
                         </button>';

        }

        if ($this->status == 2) { // Diterima
            $actions .= '<button type="button" class="btn btn-primary btn-sm me-1" data-bs-toggle="tooltip" title="Proses Retur" onclick="lanjutkanOrder('.$this->id.')">
                            <i class="fa fa-truck"></i> Proses
                         </button>';
        }

        if ($this->status == 3) { // Diproses
            $actions .= '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Selesaikan Retur" onclick="selesaikanOrder('.$this->id.')">
                            <i class="fa fa-check-double"></i> Selesaikan
                         </button>';
        }

        // Tombol cetak bisa untuk semua status yang sudah diajukan
        if ($this->status >= 3 && $this->status != 99) {
             $actions .= ' <a href="'.route('billing.barang-retur.cetak', $this->id).'" target="_blank" class="btn btn-secondary btn-sm" data-bs-toggle="tooltip" title="Cetak PDF Retur">
                            <i class="fa fa-print"></i>
                          </a>';
        }

        // Tombol cetak PDF Diterima (jika sudah diterima atau lebih)
        if ($this->status >= 2 && $this->status != 99) {
             $actions .= ' <a href="'.route('billing.barang-retur.cetak_diterima', $this->id).'" target="_blank" class="btn btn-info btn-sm me-2" data-bs-toggle="tooltip" title="Cetak Bukti Diterima">
                            <i class="fa fa-download"></i>
                          </a>';
        }

        if ($this->status < 3) {
            $actions .= '<button type="button" class="btn btn-danger btn-sm" data-bs-toggle="tooltip" title="Void" onclick="voidOrder('.$this->id.')">
                            <i class="fa fa-times-circle"></i> Void
                         </button>';
        }


        return $actions;
    }

    public function barang_unit()
    {
        return $this->belongsTo(BarangUnit::class);
    }

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class);
    }

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function details()
    {
        return $this->hasMany(BarangReturDetail::class);
    }

     private function update_stok($keranjang)
    {
        $result = ['status' => 'success'];
        $stok_retur = null;

        foreach ($keranjang as $d) {

            if ($this->tipe == 1) {
                // TIPE 1 (Ke Supplier) - Logika Berbasis STOK

                // 1. Kurangi Stok Utama (JIKA DIPERLUKAN - saat ini tidak ada)
                // Jika Anda ingin mengurangi stok jual saat retur ke supplier,
                // tambahkan logikanya di sini.
                // $stok = $d->stok;
                // $stok->decrement('stok', $d->qty);

                // 2. Tambah Stok Karantina (Berbasis Stok)
                $stok_retur = StokRetur::firstOrCreate(
                    [
                        'barang_stok_harga_id' => $d->barang_stok_harga_id,
                        'barang_id' => $d->barang_id
                    ],
                    ['total_qty_karantina' => 0]
                );

            } else {
                // TIPE 2 (Dari Konsumen) - Logika Berbasis BARANG

                // 1. Kurangi Stok Utama (TIDAK ADA)
                // Kita tidak mengurangi stok jual

                // 2. Tambah Stok Karantina (Berbasis Barang)
                $stok_retur = StokRetur::firstOrCreate(
                    [
                        'barang_id' => $d->barang_id,
                        'barang_stok_harga_id' => null // Pastikan ini null
                    ],
                    ['total_qty_karantina' => 0]
                );
            }

            // 3. Increment Karantina & Catat Sumber (Berlaku untuk kedua Tipe)
            $stok_retur->increment('total_qty_karantina', $d->qty);

            StokReturSource::create([
                'stok_retur_id' => $stok_retur->id,
                'barang_retur_detail_id' => $d->id,
                'qty_diterima' => $d->qty,
            ]);

            $d->update(['stok_kurang' => 0]);
        }

        return $result;

        // foreach ($keranjang as $item) {
        //     $barang = BarangStokHarga::find($item->barang_stok_harga_id);

        //     if ($barang->stok < $item->qty) {

        //         return [
        //             'id' => $item->id,
        //             'status' => false,
        //         ];
        //     }

        //     $barang->stok -= $item->qty;
        //     $barang->save();

        //     $stokKarantina = StokRetur::firstOrCreate(
        //         ['barang_stok_harga_id' => $item->barang_stok_harga_id],
        //         ['total_qty_karantina' => 0] // Default jika baru dibuat
        //     );

        //     $stokKarantina->total_qty_karantina = DB::raw("total_qty_karantina + {$item->qty}");
        //     $stokKarantina->save();

        //     StokReturSource::create([
        //         'stok_retur_id'          => $stokKarantina->id, // Link ke tabel agregat
        //         'barang_retur_detail_id' => $item->id, // Link ke item retur asli
        //         'qty_diterima'           => $item->qty,
        //     ]);

        // }

        // return true;
    }

    public function terima_retur($id)
    {
        $data = $this->find($id);
        if ($data->status != 1) {
            return ['status' => 'error', 'message' => 'Hanya retur yang "Diajukan" yang bisa diterima.'];
        }

        $data->update(['status' => 2, 'waktu_diterima' => now()]); // 2 = Diterima
        return ['status' => 'success', 'message' => 'Retur Berhasil Diterima. Silahkan Cetak Bukti Terima!'];
    }

    public function proses_retur($id)
    {
        $stok_update = null;
        $data = $this->where('id', $id)->with(['details.stok', 'barang_unit', 'konsumen'])->first();

        if ($data->status > 3) {
            return ['status' => 'error', 'message' => 'Retur sudah diproses/selesai'];
        }

        if ($data->status != 2) {
            return ['status' => 'error', 'message' => 'Hanya retur yang "Diterima" yang bisa diproses.'];
        }
        try {
            DB::beginTransaction();

            $calculate_stok = $this->update_stok($data->details);

            if (isset($calculate_stok['status']) && $calculate_stok['status'] == false) {
                $stok_update = $calculate_stok;
                throw new \Exception('Terdapat stok yang kurang dari barang yang akan diproses. Silahkan lihat di detail barang.');
            }

            $data->update(['status' => 3,
                            'waktu_diproses' => now()]); // 3 = Diproses


            DB::commit();

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


        return ['status' => 'success', 'message' => 'Barang Berhasil Diproses. Silahkan Cetak Bukti Kirim!'];
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

            $data->update([
                'status' => 1,
            ]);

            DB::commit();

            $dbWa = new GroupWa;
            $pesan = '';
            $tanggal = Carbon::now()->translatedFormat('d F Y');

            // $pesan = "*".$data->barang_unit->nama."*\n";

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
            foreach ($data->load(['details.barang.satuan'])->details as $d) {
                $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->qty.' ('.$d->barang->satuan->nama.")";
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

    public function void_retur($id)
    {
        $data = $this->where('id', $id)->first();

        if ($data->status == 99) {
            return ['status' => 'error', 'message' => 'Retur sudah selesai, tidak dapat di void'];
        }

        $detail = $data->details;

        try {
            DB::beginTransaction();

            $data->update([
                'status' => 99,
            ]);

            DB::commit();


        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        return ['status' => 'success', 'message' => 'Retur berhasil di void'];

    }
}
