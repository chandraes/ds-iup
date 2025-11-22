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

        // if ($this->status == 3) { // Diproses
        //     $actions .= '<button type="button" class="btn btn-success btn-sm" data-bs-toggle="tooltip" title="Selesaikan Retur" onclick="selesaikanOrder('.$this->id.')">
        //                     <i class="fa fa-check-double"></i> Selesaikan
        //                  </button>';
        // }

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

    private function update_stok($details)
    {
        // Loop setiap item dalam retur
        foreach ($details as $detail) {
            $sisa_qty_butuh_ganti = $detail->qty;

            // ---------------------------------------------------------
            // LANGKAH 1: Cari Stok Pengganti (Good Stock) - Metode FILO
            // ---------------------------------------------------------
            // Kita ambil dari barang_stok_hargas dimana stok > 0
            // Order by ID DESC (ID terbesar = Barang paling baru masuk = First Out)
            $list_stok_gudang = BarangStokHarga::where('barang_id', $detail->barang_id)
                ->where('stok', '>', 0)
                ->orderBy('id', 'asc')
                ->lockForUpdate() // Kunci baris agar tidak diambil transaksi lain saat proses
                ->get();

            // ---------------------------------------------------------
            // LANGKAH 2: Validasi Kecukupan Stok
            // ---------------------------------------------------------
            if ($list_stok_gudang->sum('stok') < $sisa_qty_butuh_ganti) {
                // Jika total stok di semua batch tidak cukup, batalkan & beri tahu ID detailnya
                return [
                    'status' => false,
                    'id' => $detail->id,
                    'message' => 'Stok pengganti tidak mencukupi untuk barang ID: ' . $detail->barang_id
                ];
            }

            // ---------------------------------------------------------
            // LANGKAH 3: Eksekusi Pemotongan (Split Stock Logic)
            // ---------------------------------------------------------
            foreach ($list_stok_gudang as $stok_batch) {
                if ($sisa_qty_butuh_ganti <= 0) break;

                // Ambil qty sebanyak yang dibutuhkan atau sebanyak yang tersedia di batch ini
                $qty_potong = min($sisa_qty_butuh_ganti, $stok_batch->stok);

                // A. Kurangi Stok Bagus (Good Stock)
                $stok_batch->decrement('stok', $qty_potong);

                // B. Update/Buat Gudang Karantina (Bad Stock Agregat)
                // Karena struktur baru Unique hanya per barang_id, ini akan menyatukan stok.
                $bad_stok = StokRetur::firstOrCreate(
                    ['barang_id' => $detail->barang_id],
                    [
                        'total_qty_karantina' => 0,
                        'total_qty_diproses' => 0,
                        'status' => 0
                    ]
                );
                // Tambah stok ke gudang karantina
                $bad_stok->increment('total_qty_karantina', $qty_potong);

                // C. Simpan Jejak Asal (Traceability)
                // Disini kita simpan barang_stok_harga_id agar tau bad stock ini
                // "menggantikan" atau "berasal" dari batch yang mana.
                StokReturSource::create([
                    'stok_retur_id'          => $bad_stok->id,
                    'barang_retur_detail_id' => $detail->id,
                    'barang_stok_harga_id'   => $stok_batch->id, // <--- Disimpan di sini
                    'qty_diterima'           => $qty_potong,
                ]);

                // Kurangi sisa yang harus dicari
                $sisa_qty_butuh_ganti -= $qty_potong;
            }
        }

        return ['status' => true];
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
            $pesan .= "🔸🔸🔸🔸🔸🔸🔸🔸🔸🔸"."\n"."*TERIMA BARANG RETUR*\n"."🔸🔸🔸🔸🔸🔸🔸🔸🔸🔸\n\n";

            if ($data['tipe'] == 2) {
                $kota = $data->konsumen->kabupaten_kota ? $data->konsumen->kabupaten_kota->nama_wilayah : '';

                $pesan .= "*".$data->konsumen->kode_toko->kode. ' '.$data->konsumen->nama."*\n".
                    $data->konsumen->alamat."\n".
                    $kota."\n\n";
            } else {
                $pesan .= "\n";
            }

            $pesan .= "*Tanggal* : ".$tanggal."\n\n";

            //  $pesan = "Barang A: \n";

            $n = 1;
            foreach ($data->load(['details.barang.satuan'])->details as $d) {
                $pesan .= $n++.'. '.$d->barang->barang_nama->nama." ".$d->barang->kode.""."\n".$d->barang->merk." "."....... ". $d->qty.' ('.$d->barang->satuan->nama.")";
                $pesan .= "\n\n";
            }

            $pesan .= "=======================\n".
                        "•⁠ Sales : ".$data->karyawan->nama."\n".
                        "⁠•⁠ CP : ".$data->karyawan->no_hp."\n\n";

            $pesan .= "No Kantor: *0853-3939-3918* \n";

            $tujuan = $dbWa->where('untuk', 'terima-barang-retur')->first()->nama_group;

            $dbWa->sendWa($tujuan, $pesan);

            $no_konsumen = $data->konsumen->no_hp;
            $no_konsumen = str_replace('-', '', $no_konsumen);

            // check length no hp
            if (strlen($no_konsumen) > 10) {
                $dbWa->sendWa($no_konsumen, $pesan);
            }

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

        if ($data->status == 3) {
            return ['status' => 'error', 'message' => 'Fitur void pada status ini sedang tahap pengembangan!'];
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
