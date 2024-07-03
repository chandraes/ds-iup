<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KasKecil extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_nominal', 'tanggal', 'kode', 'nf_saldo'];

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function getKodeAttribute()
    {
        return $this->nomor_kode_kas_kecil != null ? 'KK'.str_pad($this->nomor_kode_kas_kecil, 2, '0', STR_PAD_LEFT) : '';
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function kasKecil($month, $year)
    {
        return $this->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function kasKecilByMonth($month, $year)
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

    public function masukKasKecil()
    {
        $db = new KasBesar();

        DB::beginTransaction();

        $kb = $db->keluarKasKecil();

        $data['nominal'] = 1000000;
        $data['saldo'] = $this->saldoTerakhir() + $data['nominal'];
        $data['jenis'] = 1;
        $data['nomor_kode_kas_kecil'] = $kb->nomor_kode_kas_kecil;
        $data['nama_rek'] = $kb->nama_rek;
        $data['bank'] = $kb->bank;
        $data['no_rek'] = $kb->no_rek;
        $data['void'] = 1;

        $store = $this->create($data);

        DB::commit();

        return $store;

    }

    public function keluarKasKecil($data)
    {
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal'];
        $data['jenis'] = 0;

        $store = $this->create($data);

        return $store;
    }

    public function voidKasKecil($id)
    {
        $db = $this->find($id);
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();

        $db->update(['void' => 1]);

        $data['uraian'] = 'Void '.$db->uraian;
        $data['jenis'] = 1;
        $data['nominal'] = $db->nominal;
        $data['saldo'] = $this->saldoTerakhir() + $data['nominal'];
        $data['no_rek'] = $rekening->no_rek;
        $data['nama_rek'] = $rekening->nama_rek;
        $data['bank'] = $rekening->bank;
        $data['void'] = 1;

        $store = $this->create($data);

        return $store;
    }
}
