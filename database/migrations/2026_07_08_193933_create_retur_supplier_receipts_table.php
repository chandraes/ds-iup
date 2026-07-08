<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('retur_supplier_receipts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('retur_supplier_id')->constrained('retur_suppliers')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete(); // Siapa yang verifikasi/terima
            $table->text('catatan')->nullable(); // Misal: "Barang kurang 2, diganti barang A"
            $table->timestamps();
        });

        // Tabel Detail (Mencatat barang apa saja yang masuk ke stok di penerimaan ini)
        Schema::create('retur_supplier_receipt_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('receipt_id')->constrained('retur_supplier_receipts')->cascadeOnDelete();

            // Referensi ke detail barang retur awal (opsional/nullable jika murni barang pengganti tambahan)
            $table->foreignId('retur_supplier_detail_id')->nullable()->constrained('retur_supplier_details')->nullOnDelete();

            // Barang yang SEBENARNYA diterima (Bisa barang asli, bisa barang pengganti)
            $table->foreignId('barang_id')->constrained('barangs')->cascadeOnDelete();

            $table->integer('qty_terima');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('retur_supplier_receipt_details');
        Schema::dropIfExists('retur_supplier_receipts');
    }
};
