<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class BarangDataTableTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        config(['database.default' => 'barang_datatable_test', 'database.connections.barang_datatable_test' => ['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']]);
        foreach (['barang_units', 'barang_types', 'barang_kategoris', 'barang_namas', 'satuans', 'subpgs'] as $name) {
            Schema::create($name, function (Blueprint $table): void {
                $table->id();
                $table->string('nama');
                $table->timestamps();
            });
        }
        Schema::create('barangs', function (Blueprint $table): void {
            $table->id();
            foreach (['barang_unit_id', 'barang_type_id', 'barang_kategori_id', 'barang_nama_id', 'satuan_id', 'subpg_id'] as $column) {
                $table->integer($column)->nullable();
            }
            $table->integer('jenis')->default(1);
            $table->boolean('is_active')->default(true);
            $table->boolean('is_grosir')->default(false);
            $table->string('kode')->nullable();
            $table->string('merk');
            $table->string('foto')->nullable();
            $table->decimal('diskon')->default(0);
            $table->date('diskon_mulai')->nullable();
            $table->date('diskon_selesai')->nullable();
            $table->text('keterangan')->nullable();
            $table->timestamps();
        });
        DB::table('barang_kategoris')->insert([['id' => 6, 'nama' => 'BAHAN BANGUNAN'], ['id' => 120, 'nama' => 'ISOLASI']]);
        DB::table('barang_namas')->insert([['id' => 96, 'nama' => 'ENGSEL TIPIS STANLIS 2"'], ['id' => 1227, 'nama' => 'ISOLASI HIJAU A 12 x 60 Y']]);
        // Barang ids deliberately collide with the kategori/nama ids of OTHER rows.
        DB::table('barangs')->insert([
            ['id' => 120, 'barang_kategori_id' => 6, 'barang_nama_id' => 96, 'merk' => '-', 'foto' => 'barang/120.jpg'],
            ['id' => 1568, 'barang_kategori_id' => 120, 'barang_nama_id' => 1227, 'merk' => 'TM', 'foto' => null],
        ]);
        $this->actingAs(new User(['name' => 'Admin Uji', 'role' => 'admin']));
    }

    #[TestWith([3])]
    #[TestWith([4])]
    public function test_sorting_by_relation_column_keeps_each_row_own_barang_id(int $orderColumn): void
    {
        $columns = collect(['no', 'unit', 'barang_type_id', 'kategori.nama', 'barang_nama.nama', 'merk'])
            ->map(fn ($name) => ['data' => $name, 'name' => $name, 'searchable' => 'true', 'orderable' => 'true', 'search' => ['value' => '']])
            ->all();

        $rows = $this->getJson(route('db.barang.data', ['draw' => 1, 'start' => 0, 'length' => 10, 'columns' => $columns, 'order' => [['column' => $orderColumn, 'dir' => 'asc']]]))
            ->assertOk()
            ->json('data');

        $this->assertEqualsCanonicalizing([[120, '-'], [1568, 'TM']], array_map(fn ($row) => [$row['id'], $row['merk']], $rows));
        $isolasi = collect($rows)->firstWhere('merk', 'TM');
        $this->assertStringContainsString('/db/barang/delete/1568', $isolasi['aksi']);
        $this->assertStringNotContainsString('barang/120.jpg', $isolasi['upload_barang']);
    }

    public function test_row_buttons_escape_quotes_so_the_onclick_handler_still_runs(): void
    {
        DB::table('barangs')->where('id', 1568)->update(['merk' => "O'NEIL", 'kode' => null]);

        $row = collect($this->getJson(route('db.barang.data', ['draw' => 1, 'start' => 0, 'length' => 10]))->json('data'))->firstWhere('id', 1568);

        foreach (['diskon_view', 'grosir_view'] as $column) {
            $this->assertStringNotContainsString("'O'NEIL'", $row[$column]);
            $this->assertStringContainsString('O\\u0027NEIL', $row[$column]);
        }
    }
}
