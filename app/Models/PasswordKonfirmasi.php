<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class PasswordKonfirmasi extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    public static function updatePassword($data)
    {
        DB::beginTransaction();
        try {
            $password = PasswordKonfirmasi::updateOrCreate(
                ['id' => 1],
                ['password' => $data['password']]
            );

            DB::commit();
            
            $response = [
                'status' => 'success',
                'message' => 'Password berhasil diubah'
            ];
        } catch (\Exception $e) {
            DB::rollBack();
            $response = [
                'status' => 'error',
                'message' => $e->getMessage()
            ];
        }

        return $response;

    }
}
