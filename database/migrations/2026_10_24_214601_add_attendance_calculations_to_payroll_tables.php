<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payroll_attendance_periods', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            $table->unsignedTinyInteger('month');
            $table->date('from_date');
            $table->date('to_date');
            $table->uuid('request_id')->unique();
            $table->json('snapshot')->nullable();
            $table->timestamp('fetched_at')->nullable();
            $table->timestamp('lock_attempted_at')->nullable();
            $table->timestamp('remote_locked_at')->nullable();
            $table->timestamp('sealed_at')->nullable();
            $table->foreignId('rekap_gaji_id')->nullable()->constrained('rekap_gajis')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['year', 'month']);
        });
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->foreignId('karyawan_id')->nullable()->constrained('karyawans')->restrictOnDelete();
            $table->unsignedBigInteger('potongan_absensi')->default(0);
            $table->json('rincian_absensi')->nullable();
            $table->string('attendance_revision', 64)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('rekap_gaji_details', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('karyawan_id');
            $table->dropColumn(['potongan_absensi', 'rincian_absensi', 'attendance_revision']);
        });
        Schema::dropIfExists('payroll_attendance_periods');
    }
};
