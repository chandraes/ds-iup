<?php

namespace Tests\Feature;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\User;
use Illuminate\Support\ViewErrorBag;
use PHPUnit\Framework\Attributes\TestWith;
use Tests\TestCase;

class UserStockPriceTest extends TestCase
{
    #[TestWith([1, null, '111.000'])]
    #[TestWith([2, null, '100.000'])]
    #[TestWith([1, 120000, '133.200'])]
    #[TestWith([2, 120000, '120.000'])]
    public function test_user_stock_displays_selling_price_with_the_same_tax_and_proposal_rules_as_admin(int $jenis, ?int $proposedPrice, string $expected): void
    {
        $this->withoutVite();
        $this->actingAs(new User(['name' => 'User Uji', 'role' => 'user']));
        $stock = (new BarangStokHarga)->forceFill(['id' => 1, 'harga' => 100000, 'stok' => 0, 'stok_awal' => 0]);
        $product = (new Barang)->forceFill(['id' => 1, 'jenis' => $jenis, 'kode' => 'UJI', 'merk' => 'Uji']);
        $product->setRelation('satuan', null);
        $stock->setRelation('barang', $product);
        foreach (['unit', 'type', 'kategori', 'barang_nama'] as $relation) {
            $stock->setRelation($relation, (object) ['nama' => 'Uji']);
        }
        $stock->setRelation('harga_temp', $proposedPrice === null ? null : (object) ['harga_ajuan' => $proposedPrice]);
        foreach (['unitRowspan', 'typeRowspan', 'kategoriRowspan', 'namaRowspan', 'barangRowspan'] as $attribute) {
            $stock->$attribute = 1;
        }

        $view = $this->view('user.stok-all.classic', [
            'data' => [[[[[collect([$stock])]]]]], 'units' => [], 'selectType' => [],
            'selectKategori' => [], 'selectBarangNama' => [], 'ppnRate' => 11, 'errors' => new ViewErrorBag,
        ]);

        $view->assertSee('Harga + PPN')->assertSee($expected);
    }
}
