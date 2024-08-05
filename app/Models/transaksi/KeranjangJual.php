<?php

namespace App\Models\transaksi;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Konsumen;
use App\Models\db\Pajak;
use App\Models\GroupWa;
use App\Models\KasBesar;
use App\Models\KasKonsumen;
use App\Models\KonsumenTemp;
use App\Models\PpnKeluaran;
use App\Models\Rekening;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class KeranjangJual extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
    protected $appends = ['nf_harga', 'nf_jumlah', 'nf_total'];

    public function barang()
    {
        return $this->belongsTo(Barang::class);
    }

    public function getNfHargaAttribute()
    {
        return number_format($this->harga_satuan, 0, ',', '.');
    }

    public function getNfJumlahAttribute()
    {
        return number_format($this->jumlah, 0, ',', '.');
    }

    public function getNfTotalAttribute()
    {
        return number_format($this->total, 0, ',', '.');
    }

    public function checkout($data)
    {
        try {
            DB::beginTransaction();

            $keranjang = $this->where('user_id', auth()->user()->id)->get();

            if ($keranjang->isEmpty()) {
                return [
                    'status' => 'error',
                    'message' => 'Keranjang masih kosong'
                ];
            }

            $dbPajak = new Pajak();
            $dbInvoice = new InvoiceJual();
            $data['total'] = $keranjang->sum('total');
            $ppnVal = $dbPajak->where('untuk', 'ppn')->first()->persen;
            $pphVal = $dbPajak->where('untuk', 'pph')->first()->persen;
            $data['ppn'] = $keranjang->where('barang_ppn', 1)->first() ? ($data['total'] * $ppnVal / 100) : 0;
            $data['pph'] = $data['apa_pph'] == '1' ? ($data['total'] * $pphVal / 100) : 0;

            $data['nomor'] = $dbInvoice->generateNomor();
            $data['kode'] = $data['nomor'] . '/' . date('m/Y').'/PT-IUP';

            $data['dp'] = isset($data['dp']) ? str_replace('.', '', $data['dp']) : 0;

            $data['dp_ppn'] = $data['dp'] > 0 ? $data['dp'] * $ppnVal / 100 : 0;

            unset($data['apa_pph']);

            $data['grand_total'] = $data['total'] + $data['ppn'] - $data['pph'];


            if ($data['konsumen_id'] == '*') {
                $konsumen = KonsumenTemp::create([
                    'nama' => $data['nama'],
                    'no_hp' => isset($data['no_hp']) ?? null,
                    'npwp' => isset($data['npwp']) ?? null,
                    'alamat' => isset($data['alamat']) ?? null,
                ]);
                unset($data['konsumen_id']);
                $data['konsumen_temp_id'] = $konsumen->id;
                $data['lunas'] = 1;
            } else {
                $konsumen = Konsumen::find($data['konsumen_id']);

                $data['lunas'] = $konsumen->pembayaran == 1 ? 1 : 0;

                if($konsumen->pembayaran == 2) {
                    $sisaTerakhir = KasKonsumen::where('konsumen_id', $konsumen->id)->orderBy('id', 'desc')->first()->sisa ?? 0;
                    if ($sisaTerakhir + $data['grand_total'] > $konsumen->plafon) {
                        return [
                            'status' => 'error',
                            'message' => 'Plafon konsumen sudah melebihi batas.'
                        ];
                    }
                    $checkInvoice = InvoiceJual::where('konsumen_id', $konsumen->id)
                        ->where('lunas', 0)
                        ->where('jatuh_tempo', '>', now())
                        ->exists();

                    if ($checkInvoice) {
                        return [
                            'status' => 'error',
                            'message' => 'Konsumen memiliki tagihan yang telah jatuh tempo.'
                        ];
                    }

                    $data['jatuh_tempo'] = now()->addDays($konsumen->tempo_hari);

                }

            }

            $stateBayar = $data['lunas'] == 1 ? 1 : 0;
            $stateDP = 0;

            $invoice = $dbInvoice->create($data);

            foreach ($keranjang as $item) {
                $invoice->invoice_detail()->create([
                    'barang_id' => $item->barang_id,
                    'barang_stok_harga_id' => $item->barang_stok_harga_id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $item->harga_satuan,
                    'total' => $item->total
                ]);
            }

            if ($data['ppn'] > 0 && $stateBayar == 1) {
                $this->ppn_keluaran($invoice->id, $data['ppn']);
            }

            if ($data['dp_ppn'] > 0) {
                $this->ppn_keluaran($invoice->id, $data['dp_ppn']);
            }

            if ($invoice->lunas == 0 && $invoice->dp > 0) {
                $stateDP = 1;
            }

            $this->update_stok($keranjang);

            if (isset($data['konsumen_id'])) {
                $dbKasKonsumen = new KasKonsumen();
                $sisaTerakhirKonsumen = $dbKasKonsumen->sisaTerakhir($konsumen->id);
                $isPembayaranTunai = $konsumen->pembayaran == 1;

                $sisaTerakhirKonsumen = $isPembayaranTunai
                    ? $sisaTerakhirKonsumen - $data['grand_total']
                    : $sisaTerakhirKonsumen + $data['grand_total'];

                $sisa = $isPembayaranTunai && $sisaTerakhirKonsumen < 0
                    ? 0
                    : $sisaTerakhirKonsumen;

                $dbKasKonsumen->create([
                    'konsumen_id' => $konsumen->id,
                    'uraian' => $invoice->kode,
                    $isPembayaranTunai ? 'bayar' : 'hutang' => $data['grand_total'],
                    'sisa' => $sisa,
                ]);
            }

            $waState = 0;
            $dbKas = new KasBesar();

            if ($stateBayar == 1) {

                $ppn_kas = $data['ppn'] > 0 ? 1 : 0;
                $untukRekening = $ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $rekening = Rekening::where('untuk', $untukRekening)->first();

                $store = $dbKas->create([
                    'ppn_kas' => $ppn_kas,
                    'invoice_jual_id' => $invoice->id,
                    'nominal' => $data['grand_total'],
                    'uraian' => 'Penjualan '.$invoice->kode,
                    'jenis' => 1,
                    'saldo' => $dbKas->saldoTerakhir($ppn_kas) + $data['grand_total'],
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $dbKas->modalInvestorTerakhir($ppn_kas),
                ]);

                $waState = 1;
            }

            if ($stateDP == 1) {
                $ppn_kas = $data['ppn'] > 0 ? 1 : 0;
                $totalDP = $data['dp'] + $data['dp_ppn'];
                $untukRekening = $ppn_kas == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
                $rekening = Rekening::where('untuk', $untukRekening)->first();

                $store = $dbKas->create([
                    'ppn_kas' => $ppn_kas,
                    'invoice_jual_id' => $invoice->id,
                    'nominal' => $totalDP,
                    'uraian' => 'DP '.$invoice->kode,
                    'jenis' => 1,
                    'saldo' => $dbKas->saldoTerakhir($ppn_kas) + $totalDP,
                    'no_rek' => $rekening->no_rek,
                    'nama_rek' => $rekening->nama_rek,
                    'bank' => $rekening->bank,
                    'modal_investor_terakhir' => $dbKas->modalInvestorTerakhir($ppn_kas),
                ]);

                $waState = 1;
            }

            $this->where('user_id', auth()->user()->id)->delete();
            DB::commit();

            if ($waState == 1) {
                $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                            "*FORM PENJUALAN*\n".
                            "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                            "Uraian : *".$store->uraian."*\n\n".
                            "Konsumen : *".$konsumen->nama."*\n".
                            "Nilai :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            "Bank      : ".$store->bank."\n".
                            "Nama    : ".$store->nama_rek."\n".
                            "No. Rek : ".$store->no_rek."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar : \n".
                            "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

                $group = GroupWa::where('untuk', $untukRekening)->first()->nama_group;
                $dbKas->sendWa($group, $pesan);
            }

        } catch (\Throwable $th) {
            DB::rollBack();
            return [
                'status' => 'error',
                'message' => $th->getMessage()
            ];
        }

        return [
            'status' => 'success',
            'message' => 'Transaksi berhasil'
        ];
    }

    private function update_stok($keranjang)
    {
        foreach ($keranjang as $item) {
            $barang = BarangStokHarga::find($item->barang_stok_harga_id);
            $barang->stok -= $item->jumlah;
            $barang->save();
        }

        return true;
    }

    private function ppn_keluaran($invoice_id, $ppn)
    {
        $db = new PpnKeluaran();

        $saldo = $db->saldoTerakhir();
        $invoice = InvoiceJual::find($invoice_id);

        $uraianPrefix = $invoice->lunas == 0 ? 'DP PPN ' : 'PPN ';
        $db->create([
            'invoice_jual_id' => $invoice_id,
            'uraian' => $uraianPrefix . $invoice->kode,
            'nominal' => $ppn,
            'saldo' => $saldo + $ppn
        ]);


        return true;
    }
}
