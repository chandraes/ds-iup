<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Satuan;
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
        'nf_diskon',
        'nf_ppn',
        'nf_jumlah_grosir',
        'harga_diskon_dpp',
        'nf_harga_satuan_akhir',

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

    public function getNfJumlahGrosirAttribute()
    {
        return number_format($this->jumlah_grosir, 0, ',', '.');
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

     public function satuan_grosir()
    {
        return $this->belongsTo(Satuan::class, 'satuan_grosir_id');
    }

     public function getNfDiskonAttribute()
    {
        return number_format($this->diskon, 0, ',', '.');
    }

    public function getNfPpnAttribute()
    {
        return number_format($this->ppn, 0, ',', '.');
    }

    public function getHargaDiskonDppAttribute()
    {
        return number_format($this->harga_satuan - $this->diskon, 0, ',', '.');
    }

    public function getNfHargaSatuanAkhirAttribute()
    {
        return number_format($this->harga_satuan_akhir, 0, ',', '.');
    }

    public function sellingOut($month, $year, $perusahaan, $filters = [])
    {

        $relations = [
            'invoice' => function ($q) {
            $q->select('id', 'created_at', 'updated_at', 'void', 'konsumen_id', 'konsumen_temp_id', 'karyawan_id', 'kode', 'kas_ppn');
            },
            'invoice.konsumen' => function ($q) {
            $q->select('id', 'kode_toko_id', 'nama', 'alamat', 'kabupaten_kota_id', 'kode');
            },
            'barang' => function ($q) {
            $q->select('id', 'barang_unit_id', 'barang_nama_id', 'barang_kategori_id', 'kode', 'merk');
            },
            'barang.barang_nama',
            'barang.kategori',
            'invoice.konsumen_temp',
            'invoice.konsumen.kode_toko',
            'invoice.konsumen.kabupaten_kota',
            'invoice.karyawan'
        ];


        $baseQuery = $this->with($relations)
            ->whereHas('barang', function ($q) use ($perusahaan) {
            $q->where('barang_unit_id', $perusahaan);
            });

        $data_jual = (clone $baseQuery)
            ->whereHas('invoice', function ($q) use ($month, $year) {
            $q->whereMonth('created_at', $month)
              ->whereYear('created_at', $year);
            });

        $data_void = (clone $baseQuery)
            ->whereHas('invoice', function ($q) use ($month, $year) {
            $q->where('void', 1)
              ->whereMonth('updated_at', $month)
              ->whereYear('updated_at', $year);
            });

        if (isset($filters['barang_nama_id']) && $filters['barang_nama_id'] != '') {
            $data_jual->whereHas('barang', function ($q) use ($filters) {
                $q->where('barang_nama_id', $filters['barang_nama_id']);
            });

            $data_void->whereHas('barang', function ($q) use ($filters) {
                $q->where('barang_nama_id', $filters['barang_nama_id']);
            });
        }

        if (isset($filters['sales']) && $filters['sales'] != '') {
            $data_jual->whereHas('invoice', function ($q) use ($filters) {
                $q->where('karyawan_id', $filters['sales']);
            });

            $data_void->whereHas('invoice', function ($q) use ($filters) {
                $q->where('karyawan_id', $filters['sales']);
            });
        }

        if (isset($filters['kabupaten_kota']) && $filters['kabupaten_kota'] != '') {
            $data_jual->whereHas('invoice.konsumen', function ($q) use ($filters) {
                $q->where('kabupaten_kota_id', $filters['kabupaten_kota']);
            });

            $data_void->whereHas('invoice.konsumen', function ($q) use ($filters) {
                $q->where('kabupaten_kota_id', $filters['kabupaten_kota']);
            });
        }

        $data_jual = $data_jual->get();
        $data_void = $data_void->get();

        return [
            'data_jual' => $data_jual,
            'void' => $data_void,
        ];
    }
}
