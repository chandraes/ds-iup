<?php

namespace App\Models;

use App\Models\db\Konsumen;
use App\Models\transaksi\InvoiceJual;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class KasKonsumen extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal'];

    public function konsumen()
    {
        return $this->belongsTo(Konsumen::class, 'konsumen_id');
    }

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class, 'invoice_jual_id');
    }

    public function sisaTerakhir($konsumen_id)
    {
        return $this->where('konsumen_id', $konsumen_id)->orderBy('id', 'desc')->first()->sisa ?? 0;
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function kas($konsumen_id, $month, $year)
    {
        return $this->with(['invoice_jual'])->where('konsumen_id', $konsumen_id)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function kasByMonth($konsumen_id, $month, $year)
    {
        $data = $this->where('konsumen_id', $konsumen_id)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$data) {
            
        $data = $this->where('konsumen_id', $konsumen_id)
                ->where('created_at', '<', Carbon::create($year, $month, 1))
                ->orderBy('id', 'desc')
                ->first();
        }

        return $data;
    }
}
