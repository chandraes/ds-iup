<?php

namespace App\Observers;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangAuditLog;

class BarangAuditObserver
{
    public function created(Barang $barang): void
    {
        BarangAuditLog::record('created', $barang->id, [], $barang->getAttributes());
    }

    public function updated(Barang $barang): void
    {
        $changes = collect($barang->getChanges())->except('updated_at');

        if ($changes->isEmpty()) {
            return;
        }

        BarangAuditLog::record('updated', $barang->id, collect($barang->getOriginal())->only($changes->keys())->all(), $changes->all());
    }

    public function deleted(Barang $barang): void
    {
        BarangAuditLog::record('deleted', $barang->id, $barang->getAttributes());
    }
}
