<?php

namespace App\Models;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class GantiRugi extends Model
{
    use HasFactory;

    protected $guarded = [];

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class);
    }

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function barang_stok_harga()
    {
        return $this->belongsTo(BarangStokHarga::class);
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function getNfTotalBayarAttribute()
    {
        return number_format($this->total_bayar, 0, ',', '.');
    }

    public function getNfSisaAttribute()
    {
        return number_format($this->sisa, 0, ',', '.');
    }

    public function ganti_rugi($data)
    {
        try {
            DB::beginTransaction();
            $barang = BarangStokHarga::find($data['barang_stok_harga_id']);

            $db = new KasBesar();
            $untuk = $data['kas_ppn'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
            $rekening = Rekening::where('untuk', $untuk)->first();

            $store = $db->create([
                'ppn_kas' => $data['kas_ppn'],
                'uraian' => 'Ganti Rugi ' . $barang->barang_nama->nama,
                'jenis' => 1,
                'nominal' => $data['total'],
                'saldo' => $db->saldoTerakhir($data['kas_ppn']) + $data['total'],
                'no_rek' => $rekening->no_rek,
                'nama_rek' => $rekening->nama_rek,
                'bank' => $rekening->bank,
                'modal_investor_terakhir' => $db->saldoTerakhir($data['kas_ppn']),
            ]);

            $this->create($data);

            $barang->update([
                'stok' => $barang->stok - $data['jumlah'],
            ]);

            $karyawan = Karyawan::find($data['karyawan_id']);
            $satuan = $barang->barang->satuan ? $barang->barang->satuan->nama : '';

            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n" .
                    "*FORM GANTI RUGI*\n" .
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n" .
                    "Uraian :  *" . $store->uraian . "*\n" .
                    "Jumlah  :  *" . $data['jumlah']. " ". $satuan."*\n" .
                    "Oleh    :  *" . $karyawan->nama . "*\n\n" .
                    "Nilai    :  *Rp. " . number_format($store->nominal, 0, ',', '.') . "*\n\n" .
                    "Ditransfer ke rek:\n\n" .
                    "Bank      : " . $store->bank . "\n" .
                    "Nama    : " . $store->nama_rek . "\n" .
                    "No. Rek : " . $store->no_rek . "\n\n" .
                    "==========================\n";

            $sisaSaldoKas = "Sisa Saldo Kas Besar: \n" .
                            "Rp. " . number_format($db->saldoTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

            $totalModalInvestor = $db['kas_ppn'] == 1 ?
                                    "Total Modal Investor PPN: \n" .
                                    "Rp. " . number_format($db->modalInvestorTerakhir(1), 0, ',', '.') . "\n\n" :
                                    "Total Modal Investor Non PPN: \n" .
                                    "Rp. " . number_format($db->modalInvestorTerakhir(0), 0, ',', '.') . "\n\n";
            $pesan .= $sisaSaldoKas . $totalModalInvestor . "Terima kasih 🙏🙏🙏\n";

            $group = GroupWa::where('untuk', $untuk)->first()->nama_group;
            $db->sendWa($group, $pesan);

            if ($data['lunas'] == 0) {

                $storeKasbon = $db->create([
                    'ppn_kas' => $data['kas_ppn'],
                    'uraian' => 'Kasbon Ganti Rugi ' . $barang->barang_nama->nama,
                    'jenis' => 0,
                    'nominal' => $data['total'],
                    'saldo' => $db->saldoTerakhir($data['kas_ppn']) - $data['total'],
                    'no_rek' => "-",
                    'nama_rek' => "-",
                    'bank' => "-",
                    'modal_investor_terakhir' => $db->saldoTerakhir($data['kas_ppn']),
                ]);


                $pesanKasBon = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n" .
                                "*KASBON GANTI RUGI*\n" .
                                "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n" .
                                "Uraian  :  *" . $storeKasbon->uraian . "*\n" .
                                "Jumlah  :  *" . $data['jumlah']. " ". $satuan."*\n" .
                                "Oleh    :  *" . $karyawan->nama . "*\n\n" .
                                "Nilai    :  *Rp. " . number_format($storeKasbon->nominal, 0, ',', '.') . "*\n\n" .
                                "==========================\n";
                $sisaSaldoKas = "Sisa Saldo Kas Besar: \n" .
                                "Rp. " . number_format($db->saldoTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

                $totalModalInvestor = $db['kas_ppn'] == 1 ?
                                        "Total Modal Investor PPN: \n" .
                                        "Rp. " . number_format($db->modalInvestorTerakhir(1), 0, ',', '.') . "\n\n" :
                                        "Total Modal Investor Non PPN: \n" .
                                        "Rp. " . number_format($db->modalInvestorTerakhir(0), 0, ',', '.') . "\n\n";
                $pesanKasBon .= $sisaSaldoKas . $totalModalInvestor . "Terima kasih 🙏🙏🙏\n";

                $db->sendWa($group, $pesanKasBon);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => "Gagal!! " . $th->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Berhasil menyimpan data!!'];
    }

    public function bayar($data)
    {
        
    }
}
