<?php

namespace App\Models\Pajak;

use App\Models\GroupWa;
use App\Models\Holding;
use App\Models\KasBesar;
use App\Models\PpnKeluaran;
use App\Models\PpnMasukan;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class RekapPpn extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['tanggal', 'nf_nominal', 'nf_saldo'];

    public function generateMasukanId()
    {
        $id = $this->max('masukan_id') + 1;
        return $id;
    }

    public function generateKeluaranId()
    {
        $id = $this->max('keluaran_id') + 1;
        return $id;
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function rekapByMonth($month, $year)
    {
        return $this->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function rekapByMonthSebelumnya($month, $year)
    {
        $data = $this->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (!$data) {
            $data = $this->where('created_at', '<', Carbon::create($year, $month, 1))
                    ->orderBy('id', 'desc')
                    ->first();
        }

        return $data;
    }

    public function rekapMasukanDetail()
    {
        return $this->hasMany(RekapMasukanDetail::class, 'masukan_id', 'masukan_id');
    }

    public function rekapKeluaranDetail()
    {
        return $this->hasMany(RekapKeluaranDetail::class, 'keluaran_id', 'keluaran_id');
    }

    public function getTanggalAttribute()
    {
        return Carbon::parse($this->created_at)->format('d-m-Y');
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function keranjang_masukan_lanjut($penyesuaian = 0)
    {
        $db = new PpnMasukan();

        $data = $db->where('is_keranjang', 1)->where('is_finish', 0)->get();

        $total = $data->sum('nominal') + $penyesuaian;

        $holding = Holding::first();

        try {
            DB::beginTransaction();

            $create = $this->create([
                'masukan_id' => $this->generateMasukanId(),
                'nominal' => $total,
                'saldo' => $this->saldoTerakhir() + $total,
                'penyesuaian' => $penyesuaian,
                'jenis' => 1,
                'uraian' => 'PPN Masukan',
            ]);

            if ($holding && $holding->status == 1) {
                // http post request to holding url/ppn-masukan dengan form-data masukan_id, uraian, dan nominal serta token
                $response = Http::withHeaders([
                    'Authorization' => 'Bearer ' . $holding->token,
                    'Referer' => env('APP_URL'),
                ])->asForm()->post($holding->holding_url.'/api/1.0/ppn-masukan', [
                    'masukan_id' => $create->masukan_id,
                    'uraian' => $create->uraian,
                    'nominal' => $create->nominal,
                ]);


                if ($response->status() != 200) {

                    return [
                        'status' => 'error',
                        'message' => 'Gagal mengirim data ke Holding. ' . $response['message'],
                    ];
                }
            }

            foreach ($data as $item) {

                $create->rekapMasukanDetail()->create([
                    'masukan_id' => $create->masukan_id,
                    'ppn_masukan_id' => $item->id,
                ]);

                $item->update([
                    'is_finish' => 1,
                    'is_keranjang' => 0,
                ]);
            }

            DB::commit();

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data. '. $th->getMessage(),
            ];
        }
    }

    public function keranjang_keluaran_lanjut($penyesuaian = 0)
    {
        $db = new PpnKeluaran();

        $data = $db->where('is_keranjang', 1)->where('is_finish', 0)->get();

        $total = $data->where('dipungut', 1)->sum('nominal') + $penyesuaian;

        $saldo = $this->saldoTerakhir() - $total;



        try {
            DB::beginTransaction();

            $dbKasBesar = new KasBesar();
            $waState = 0;

            $create = $this->create([
                'keluaran_id' => $this->generateKeluaranId(),
                'nominal' => $total,
                'saldo' => $saldo,
                'penyesuaian' => $penyesuaian,
                'jenis' => 0,
                'uraian' => 'PPN Keluaran',
            ]);

            if ($saldo < 0) {

                $nominalKasBesar = abs($saldo);

                $saldoKasBesar = $dbKasBesar->saldoTerakhir(1);

                if ($saldoKasBesar < $nominalKasBesar) {
                    return [
                        'status' => 'error',
                        'message' => 'Saldo Kas Besar tidak mencukupi',
                    ];
                }

                $store = $dbKasBesar->create([
                    'ppn_kas' => 1,
                    'uraian' => 'Pembayaran PPN',
                    'jenis' => 0,
                    'nominal' => $nominalKasBesar,
                    'saldo' => $dbKasBesar->saldoTerakhir(1) - $nominalKasBesar,
                    'no_rek' => 'Pajak',
                    'nama_rek' => 'Pajak',
                    'bank' => 'Pajak',
                    'modal_investor_terakhir' => $dbKasBesar->modalInvestorTerakhir(1),
                ]);

                $this->create([
                    'nominal' => $nominalKasBesar,
                    'saldo' => $this->saldoTerakhir() + $nominalKasBesar,
                    'jenis' => 1,
                    'uraian' => 'Kas Besar',
                ]);

                $waState = 1;

                $kasPpn = [
                    'saldo' => $dbKasBesar->saldoTerakhir(1),
                    'modal_investor' => $dbKasBesar->modalInvestorTerakhir(1),
                ];

                $kasNonPpn = [
                    'saldo' => $dbKasBesar->saldoTerakhir(0),
                    'modal_investor' => $dbKasBesar->modalInvestorTerakhir(0),
                ];

                // sum modal investor
                $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

                $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form PPN*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
                        "Nilai :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        "Bank      : ".$store->bank."\n".
                        "Nama    : ".$store->nama_rek."\n".
                        "No. Rek : ".$store->no_rek."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar PPN: \n".
                        "Rp. ".number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                        "Sisa Saldo Kas Besar  NON PPN: \n".
                        "Rp. ".number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        "Rp. ".number_format($totalModal, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            }

            foreach ($data as $item) {

                $create->rekapKeluaranDetail()->create([
                    'keluaran_id' => $create->keluaran_id,
                    'ppn_keluaran_id' => $item->id,
                ]);

                $item->update([
                    'is_finish' => 1,
                    'is_keranjang' => 0,
                ]);
            }

            DB::commit();

            if ($waState == 1) {
                $tujuan = GroupWa::where('untuk', 'kas-besar-ppn')->first()->nama_group;
                $dbKasBesar->sendWa($tujuan, $pesan);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data',
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => 'Gagal menyimpan data. '. $th->getMessage(),
            ];
        }
    }
}
