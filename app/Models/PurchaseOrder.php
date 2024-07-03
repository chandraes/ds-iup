<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class PurchaseOrder extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = [ 'tanggal'];

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function generateNomor()
    {
        // check max nomor in this year
        $max = $this->whereYear('created_at', now()->year)->max('nomor');
        $max = $max ? $max + 1 : 1;

        return $max;
    }

    public function generateFullNomor()
    {
        $db = new Config();
        $data = $db->where('untuk', 'resmi')->first();
        $singkatan = $data->singkatan;
        $nomor = str_pad($this->generateNomor(), 3, '0', STR_PAD_LEFT);
        $tahun = now()->year;
        $bulan = now()->format('m');

        return "{$nomor}/{$singkatan}-PO/{$bulan}/{$tahun}";
    }



    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->locale('id')->translatedFormat('d F Y');
    }

    public function items()
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }

    public function notes()
    {
        return $this->hasMany(PurchaseOrderNote::class);
    }
}
