<?php

namespace App\Models\Pajak;

use App\Models\PpnMasukan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class RekapPpn extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'nf_nominal', 'nf_saldo'];

    public function generateMasukanId()
    {
        $id = $this->max('masukan_id') + 1;
        return $id;
    }

    public function generateKeluaranId()
    {
        $id = $this->max('keluaran_id') + 1;
        return $id;
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function rekapByMonth($month, $year)
    {
        return $this->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function rekapByMonthSebelumnya($month, $year)
    {
        $data = $this->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$data) {
            $data = $this->where('created_at', '<', Carbon::create($year, $month, 1))
                    ->orderBy('id', 'desc')
                    ->first();
        }

        return $data;
    }

    public function rekapMasukanDetail()
    {
        return $this->hasMany(RekapMasukanDetail::class, 'masukan_id', 'masukan_id');
    }

    public function rekapKeluaranDetail()
    {
        return $this->hasMany(RekapKeluaranDetail::class, 'keluaran_id', 'keluaran_id');
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function keranjang_masukan_lanjut()
    {
        $db = new PpnMasukan();

        $data = $db->where('is_keranjang', 1)->where('is_finish', 0)->get();

        $total = $data->sum('nominal');

        try {
            DB::beginTransaction();

            $create = $this->create([
                'masukan_id' => $this->generateMasukanId(),
                'nominal' => $total,
                'saldo' => $this->saldoTerakhir() + $total,
                'jenis' => 1,
                'uraian' => 'PPN Masukan',
            ]);

            foreach ($data as $item) {

                $create->rekapMasukanDetail()->create([
                    'masukan_id' => $create->masukan_id,
                    'ppn_masukan_id' => $item->id,
                ]);

                $item->update([
                    'is_finish' => 1,
                    'is_keranjang' => 0,
                ]);
            }

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data. '. $th->getMessage(),
            ];
        }
    }
}
