<?php

namespace App\Models;

use App\Models\db\Barang\Barang;
use Illuminate\Database\Eloquent\Model;

class ReturSupplierReceiptDetail extends Model
{
    protected $guarded = [];

    public function receipt()
    {
        return $this->belongsTo(ReturSupplierReceipt::class, 'receipt_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }
}
