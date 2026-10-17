<?php

namespace App\Models\db;

use App\Models\GantiRugi;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class Karyawan extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function deletionBlockReason(): ?string
    {
        if (Schema::hasTable('attendance_employee_syncs') && DB::table('attendance_employee_syncs')->where('source_employee_id', (string) $this->id)->where('delivered_version', '>', 0)->exists()) {
            return 'Pegawai sudah disinkronkan ke aplikasi absensi. Gunakan Nonaktifkan untuk mempertahankan keterkaitan riwayat.';
        }
        foreach (Schema::getTables() as $table) {
            $name = $table['name'];
            if (Schema::hasColumn($name, 'karyawan_id') && DB::table($name)->where('karyawan_id', $this->id)->exists()) {
                return 'Pegawai sudah terhubung dengan data lain. Gunakan Nonaktifkan; penghapusan permanen ditolak.';
            }
        }
        if (Schema::hasTable('rekap_gaji_details') && DB::table('rekap_gaji_details')->where('nama', $this->nama)->exists()) {
            return 'Terdapat rekap gaji atas nama pegawai ini. Gunakan Nonaktifkan.';
        }

        return null;
    }

    public function jabatan()
    {
        return $this->belongsTo(Jabatan::class);
    }

    public function ganti_rugi()
    {
        return $this->hasMany(GantiRugi::class);
    }
}
