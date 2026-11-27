<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

/**
 * Sorting a DataTable by an eager-loaded relation column makes Yajra join that table;
 * without a table-qualified select the joined table's id overwrites the row id.
 */
class RelationSortDataTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'relation_sort_test', 'database.connections.relation_sort_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        $tables = [
            'barang_units' => ['nama'],
            'barang_types' => ['nama'],
            'barang_kategoris' => ['nama'],
            'barang_namas' => ['nama'],
            'satuans' => ['nama'],
            'kode_tokos' => ['kode'],
            'karyawans' => ['nama'],
            'barangs' => ['barang_unit_id', 'barang_type_id', 'barang_kategori_id', 'barang_nama_id', 'satuan_id', 'jenis', 'kode', 'merk'],
            'barang_stok_hargas' => ['barang_unit_id', 'barang_type_id', 'barang_kategori_id', 'barang_nama_id', 'barang_id', 'stok_awal', 'stok', 'harga', 'harga_beli', 'min_jual', 'hide'],
            'konsumens' => ['karyawan_id', 'kode_toko_id', 'kode', 'nama', 'pembayaran', 'plafon'],
            'barang_returs' => ['nomor', 'tipe', 'konsumen_id', 'barang_unit_id', 'karyawan_id', 'status'],
            'retur_suppliers' => ['nomor', 'barang_unit_id', 'user_id', 'tipe'],
            'retur_supplier_details' => ['retur_supplier_id', 'qty'],
            'retur_supplier_receipts' => ['retur_supplier_id'],
        ];
        foreach ($tables as $name => $columns) {
            Schema::create($name, function (Blueprint $table) use ($columns): void {
                $table->id();
                foreach ($columns as $column) {
                    $table->string($column)->nullable();
                }
                $table->timestamps();
            });
        }
        // Row ids deliberately collide with the related ids of the OTHER row.
        DB::table('barang_units')->insert([['id' => 10, 'nama' => 'B UNIT'], ['id' => 20, 'nama' => 'A UNIT']]);
        DB::table('karyawans')->insert([['id' => 10, 'nama' => 'B SALES'], ['id' => 20, 'nama' => 'A SALES']]);
        DB::table('konsumens')->insert([['id' => 10, 'karyawan_id' => 20, 'kode' => 1, 'nama' => 'B TOKO'], ['id' => 20, 'karyawan_id' => 10, 'kode' => 2, 'nama' => 'A TOKO']]);
        DB::table('barangs')->insert([['id' => 10, 'barang_unit_id' => 20, 'kode' => 'B-KODE', 'merk' => 'B'], ['id' => 20, 'barang_unit_id' => 10, 'kode' => 'A-KODE', 'merk' => 'A']]);
        DB::table('barang_stok_hargas')->insert([['id' => 20, 'barang_unit_id' => 20, 'barang_id' => 10, 'stok' => 1, 'harga' => 1, 'hide' => 0], ['id' => 10, 'barang_unit_id' => 10, 'barang_id' => 20, 'stok' => 1, 'harga' => 1, 'hide' => 0]]);
        DB::table('barang_returs')->insert([
            ['id' => 20, 'nomor' => 1, 'tipe' => 2, 'konsumen_id' => 10, 'barang_unit_id' => 10, 'karyawan_id' => 10, 'status' => 1],
            ['id' => 10, 'nomor' => 2, 'tipe' => 2, 'konsumen_id' => 20, 'barang_unit_id' => 20, 'karyawan_id' => 20, 'status' => 1],
        ]);
        DB::table('retur_suppliers')->insert([
            ['id' => 20, 'nomor' => 1, 'barang_unit_id' => 10, 'tipe' => 3, 'created_at' => '2026-11-01 10:00:00'],
            ['id' => 10, 'nomor' => 2, 'barang_unit_id' => 20, 'tipe' => 3, 'created_at' => '2026-11-02 10:00:00'],
        ]);
        $this->actingAs(new User(['name' => 'Admin Uji', 'role' => 'admin']));
    }

    /**
     * @param  list<string>  $names
     * @return array<string, mixed>
     */
    private function dataTableQuery(array $names, int $orderColumn, array $extra = []): array
    {
        $columns = array_map(fn ($name) => ['data' => $name, 'name' => $name, 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']], $names);

        return ['draw' => 1, 'start' => 0, 'length' => 10, 'columns' => $columns, 'order' => [['column' => $orderColumn, 'dir' => 'asc']], ...$extra];
    }

    #[TestWith([0])]
    #[TestWith([1])]
    public function test_stok_data_sorted_by_barang_column_keeps_stok_harga_id(int $orderColumn): void
    {
        $rows = $this->getJson(route('db.stok-data', $this->dataTableQuery(['barang.kode', 'barang.merk'], $orderColumn)))->assertOk()->json('data');

        $this->assertSame([[10, 20], [20, 10]], array_map(fn ($row) => [(int) $row['id'], (int) $row['barang_id']], $rows));
    }

    #[TestWith([0])]
    #[TestWith([1])]
    #[TestWith([2])]
    public function test_billing_barang_retur_sorted_by_relation_keeps_retur_id(int $orderColumn): void
    {
        $query = $this->dataTableQuery(['karyawan.nama', 'barang_unit.nama', 'konsumen.nama'], $orderColumn);
        $rows = $this->getJson(route('billing.barang-retur.data', $query))->assertOk()->json('data');

        $this->assertEqualsCanonicalizing([[20, 'B SALES'], [10, 'A SALES']], array_map(fn ($row) => [(int) $row['id'], $row['sales']], $rows));
        $this->assertStringContainsString('terimaOrder(20)', collect($rows)->firstWhere('sales', 'B SALES')['action']);

        $filtered = $this->getJson(route('billing.barang-retur.data', [...$query, 'sales' => 10]))->assertOk()->json('data');
        $this->assertSame([20], array_map(fn ($row) => (int) $row['id'], $filtered));
    }

    public function test_rekap_retur_supplier_sorted_by_unit_keeps_retur_id(): void
    {
        $query = $this->dataTableQuery(['barang_unit.nama'], 0, ['start_date' => '2026-11-01', 'end_date' => '2026-11-30']);
        $rows = $this->getJson(route('rekap.retur.data', $query), ['X-Requested-With' => 'XMLHttpRequest'])->assertOk()->json('data');

        $this->assertSame([[10, 'A UNIT', '2026-11-02'], [20, 'B UNIT', '2026-11-01']], array_map(fn ($row) => [(int) $row['id'], $row['supplier'], $row['created_at']], $rows));
        $this->assertStringContainsString('data-id="10"', $rows[0]['aksi']);
    }
}
