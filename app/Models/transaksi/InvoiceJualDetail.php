<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InvoiceJualDetail extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = [
        'nf_jumlah',
        'nf_harga_satuan',
        'nf_total',
    ];

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')
            ->groupBy('tahun')
            ->orderBy('tahun', 'desc')
            ->get();
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfHargaSatuanAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function invoice()
    {
        return $this->belongsTo(InvoiceJual::class, 'invoice_jual_id');
    }

    public function stok()
    {
        return $this->belongsTo(BarangStokHarga::class, 'barang_stok_harga_id');
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class, 'barang_id');
    }

    public function sellingOut($month, $year, $perusahaan, $filters = [])
    {
        // if (isset($filters['barang_stok_harga_id'])) {
        //     $query->where('barang_stok_harga_id', $filters['barang_stok_harga_id']);
        // }

        // if (isset($filters['invoice_jual_id'])) {
        //     $query->where('invoice_jual_id', $filters['invoice_jual_id']);
        // }

        // if (isset($filters['tanggal_awal']) && isset($filters['tanggal_akhir'])) {
        //     $query->whereBetween('created_at', [$filters['tanggal_awal'], $filters['tanggal_akhir']]);
        // }
        $data_void = $this->with(['invoice' => function ($q) {
                $q->select('id', 'created_at', 'updated_at', 'void', 'konsumen_id', 'konsumen_temp_id', 'karyawan_id', 'kode', 'kas_ppn');
            }, 'invoice.konsumen' => function ($q) {
                $q->select('id', 'kode_toko_id', 'nama', 'alamat', 'kabupaten_kota_id', 'kode');
            }, 'barang' => function ($q) {
                $q->select('id', 'barang_unit_id', 'barang_nama_id', 'barang_kategori_id', 'kode');
            }, 'barang.barang_nama', 'barang.kategori',
             'invoice.konsumen_temp', 'invoice.konsumen.kode_toko', 'invoice.konsumen.kabupaten_kota', 'invoice.karyawan'])
            ->whereHas('barang', function ($q) use ($perusahaan) {
                $q->where('barang_unit_id', $perusahaan);
            })->whereHas('invoice', function ($q) use ($month, $year) {
            $q->where('void', 1)
              ->whereMonth('updated_at', $month)
              ->whereYear('updated_at', $year);
        })->get();

        $data_jual = $this->with(['invoice' => function ($q) {
                $q->select('id', 'created_at', 'updated_at', 'void', 'konsumen_id', 'konsumen_temp_id', 'karyawan_id', 'kode', 'kas_ppn');
            }, 'invoice.konsumen' => function ($q) {
                $q->select('id', 'kode_toko_id', 'nama', 'alamat', 'kabupaten_kota_id', 'kode');
            }, 'barang' => function ($q) {
                $q->select('id', 'barang_unit_id', 'barang_nama_id', 'barang_kategori_id', 'kode');
            }, 'barang.barang_nama', 'barang.kategori',
             'invoice.konsumen_temp', 'invoice.konsumen.kode_toko', 'invoice.konsumen.kabupaten_kota', 'invoice.karyawan'])
            ->whereHas('barang', function ($q) use ($perusahaan) {
                $q->where('barang_unit_id', $perusahaan);
            })->whereHas('invoice', function ($q) use ($month, $year) {
            $q->where('void', 0)
              ->whereMonth('created_at', $month)
              ->whereYear('created_at', $year);
        })->get();


        return [
            'data_jual' => $data_jual,
            'void' => $data_void,
        ];
    }
}
