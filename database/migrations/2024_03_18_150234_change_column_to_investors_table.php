<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use App\Models\Investor;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->dropColumn('bank');
            $table->dropColumn('no_wa');
            $table->dropColumn('no_rek');
            $table->dropColumn('nama_rek');
        });

        // truncate investor`
        DB::table('investors')->truncate();

        $data = [
            [
                'persentase' => 50,
                'nama' => 'pengelola',
            ],
            [
                'persentase' => 50,
                'nama' => 'investor',
            ],
        ];

        Investor::insert($data);

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('investors', function (Blueprint $table) {
            $table->string('bank')->nullable();
            $table->string('no_wa')->nullable();
            $table->string('no_rek')->nullable();
            $table->string('nama_rek')->nullable();
        });
    }
};
