<?php

namespace App\Models\db;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Supplier extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['kode_supplier'];

    public function getKodeSupplierAttribute()
    {
        return 'S' . str_pad($this->kode, 2, '0', STR_PAD_LEFT);
    }

    public function generateKode()
    {
        $kode = $this->max('kode');
        $kode = $kode + 1;
        return $kode;
    }


    public function createSupplier($data)
    {
        $data['kode'] = $this->generateKode();

        $store = $this->create($data);

        $result = [
            'status' => 'success',
            'message' => 'Data berhasil ditambahkan',
            'data' => $store
        ];

        return $result;
    }
}
