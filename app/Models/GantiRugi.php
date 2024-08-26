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

    public function sisa($karyawan_id)
    {
        $total = $this->where('karyawan_id', $karyawan_id)->where('lunas', 0)->sum('sisa');
        return $total;
    }

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
                'modal_investor_terakhir' => $db->modalInvestorTerakhir($data['kas_ppn']),
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
                    "Uraian :  *Ganti Rugi*\n" .
                    "Nama   :  *" . $karyawan->nama . "*\n\n" .
                    "Nama Barang : *" . $barang->barang_nama->nama . "*\n" .
                    "Kode Barang  : *" . $barang->barang->kode . "*\n" .
                    "Merk Barang  : *" . $barang->barang->merk . "*\n\n" .
                    "Modal    :  *Rp. " . number_format($data['harga'], 0, ',', '.') . "*\n" .
                    "Jumlah  :  *" . $data['jumlah']. " ". $satuan."*\n\n" .
                    "Total    :  *Rp. " . number_format($data['total'], 0, ',', '.') . "*\n\n" .
                    "Ditransfer ke rek:\n\n" .
                    "Bank     : " . $store->bank . "\n" .
                    "Nama    : " . $store->nama_rek . "\n" .
                    "No. Rek : " . $store->no_rek . "\n\n" .
                    "==========================\n";
            $textKas = $data['kas_ppn'] == 1 ? "PPN" : "Non PPN";
            $sisaSaldoKas = "Sisa Saldo Kas Besar ".$textKas.": \n" .
                            "Rp. " . number_format($db->saldoTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

            $totalModalInvestor = "Total Modal Investor ".$textKas.": \n" .
                                    "Rp. " . number_format($db->modalInvestorTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

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
                    'modal_investor_terakhir' => $db->modalInvestorTerakhir($data['kas_ppn']),
                ]);


                $pesanKasBon = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n" .
                                "*KASBON GANTI RUGI*\n" .
                                "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n" .
                                "Uraian  :  *Kasbon Ganti Rugi*\n" .
                                "Nama    :  *" . $karyawan->nama . "*\n\n" .
                                "Nama Barang : *" . $barang->barang_nama->nama . "*\n" .
                                "Kode Barang : *" . $barang->barang->kode . "*\n" .
                                "Merk Barang : *" . $barang->barang->merk . "*\n\n" .
                                "Modal    :  *Rp. " . number_format($data['harga'], 0, ',', '.') . "*\n" .
                                "Jumlah  :  *" . $data['jumlah']. " ". $satuan."*\n\n" .
                                "Total    :  *Rp. " . number_format($data['total'], 0, ',', '.') . "*\n\n" .
                                "==========================\n".
                                "Grand total ganti rugi:\n".
                                "Rp. " . number_format($this->sisa($karyawan->id), 0, ',', '.') . "\n\n";

                $textKas = $data['kas_ppn'] == 1 ? "PPN" : "Non PPN";
                $sk = "Sisa Saldo Kas Besar ".$textKas.": \n" .
                                "Rp. " . number_format($db->saldoTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

                $tm = "Total Modal Investor ".$textKas.": \n" .
                                    "Rp. " . number_format($db->modalInvestorTerakhir($data['kas_ppn']), 0, ',', '.') . "\n\n";

                $pesanKasBon .= $sk . $tm . "Terima kasih 🙏🙏🙏\n";

            $db->sendWa($group, $pesanKasBon);
            }

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => "Gagal!! " . $th->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Berhasil menyimpan data!!'];
    }

    public function bayar($id, $data)
    {
        $inv = $this->find($id);
        $pesan = '';

        try {
            DB::beginTransaction();

            $db = new KasBesar();

            $untuk = $inv->kas_ppn == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

            $rekening = Rekening::where('untuk', $untuk)->first();

            if ($data['jenis'] == 0) {
                $store = $db->create([
                    'ppn_kas' => $inv->kas_ppn,
                    'uraian' => 'Pelunasan Ganti Rugi ' . $inv->barang->barang_nama->nama,
                    'jenis' => 1,
                    'nominal' => $inv->sisa,
                    'saldo' => $db->saldoTerakhir($inv->kas_ppn) + $inv->sisa,
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $db->modalInvestorTerakhir($inv->kas_ppn),
                ]);

                $inv->update([
                    'lunas' => 1,
                ]);
                $uraian = 'Pelunasan Ganti Rugi';

            } else {
                $data['nominal'] = str_replace('.', '', $data['nominal']);

                if ($data['nominal'] > $inv->sisa) {
                    return ['status' => 'error', 'message' => 'Nominal yang dibayar melebihi sisa!!'];
                }

                $nominal = $data['nominal'];
                $saldoTerakhir = $db->saldoTerakhir($inv->kas_ppn);
                $modalInvestorTerakhir = $db->modalInvestorTerakhir($inv->kas_ppn);

                $store = $db->create([
                    'ppn_kas' => $inv->kas_ppn,
                    'uraian' => 'Cicilan Ganti Rugi ' . $inv->barang->barang_nama->nama,
                    'jenis' => 1,
                    'nominal' => $nominal,
                    'saldo' => $saldoTerakhir + $nominal,
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $modalInvestorTerakhir,
                ]);

                $uraian = 'Cicilan Ganti Rugi';

                $inv->update([
                    'lunas' => $nominal == $inv->sisa ? 1 : $inv->lunas,
                    'total_bayar' => $inv->total_bayar + $nominal,
                    'sisa' => $inv->sisa - $nominal,
                ]);


            }

            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n" .
                    "*BAYAR GANTI RUGI*\n" .
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n" .
                    "Uraian :  *".$uraian."*\n" .
                    "Nama   :  *" . $inv->karyawan->nama . "*\n\n" .
                    "Nama Barang : *" . $inv->barang->barang_nama->nama . "*\n" .
                    "Kode Barang  : *" . $inv->barang->kode . "*\n" .
                    "Merk Barang  : *" . $inv->barang->merk . "*\n\n" .
                    "Nilai    :  *Rp. " . number_format($store['nominal'], 0, ',', '.') . "*\n\n" .
                    "Ditransfer ke rek:\n\n" .
                    "Bank     : " . $store->bank . "\n" .
                    "Nama    : " . $store->nama_rek . "\n" .
                    "No. Rek : " . $store->no_rek . "\n\n" .
                    "==========================\n";

                    $textKas = $inv->kas_ppn == 1 ? "PPN" : "Non PPN";
                    $sisaSaldoKas = "Sisa Saldo Kas Besar ".$textKas.": \n" .
                                    "Rp. " . number_format($db->saldoTerakhir($inv->kas_ppn), 0, ',', '.') . "\n\n";

                    $totalModalInvestor = "Total Modal Investor ".$textKas.": \n" .
                                            "Rp. " . number_format($db->modalInvestorTerakhir($inv->kas_ppn), 0, ',', '.') . "\n\n";

                    $pesan .= $sisaSaldoKas . $totalModalInvestor . "Terima kasih 🙏🙏🙏\n";

                    $group = GroupWa::where('untuk', $untuk)->first()->nama_group;
                    $db->sendWa($group, $pesan);

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => "Gagal!! " . $th->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Berhasil melakukan pembayaran!!'];

    }

    public function void($id)
    {
        $data = $this->find($id);

        if ($data->lunas == 1 || $data->total_bayar > 0) {
            return ['status' => 'error', 'message' => 'Data tidak bisa dihapus, karena sudah terdapat pembayaran!!'];
        }

        try {
            DB::beginTransaction();

            $barang = BarangStokHarga::find($data->barang_stok_harga_id);

            $barang->update([
                'stok' => $barang->stok + $data->jumlah,
            ]);

            $data->delete();

            DB::commit();
        } catch (\Throwable $th) {
            DB::rollBack();

            return ['status' => 'error', 'message' => "Gagal!! " . $th->getMessage()];
        }

        return ['status' => 'success', 'message' => 'Berhasil melakukan void Data!!'];
    }
}
