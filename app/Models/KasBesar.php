<?php

namespace App\Models;

use App\Models\db\CostOperational;
use App\Models\transaksi\InvoiceBelanja;
use App\Services\StarSender;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class KasBesar extends Model
{
    use HasFactory;
    protected $guarded = ['id'];

    protected $appends = ['nf_nominal', 'tanggal', 'kode_deposit', 'kode_kas_kecil', 'nf_saldo', 'nf_modal_investor'];

    public function invoice_belanja()
    {
        return $this->belongsTo(InvoiceBelanja::class, 'invoice_belanja_id');
    }

    public function investorModal()
    {
        return $this->belongsTo(InvestorModal::class);
    }

    public function dataTahun()
    {
        return $this->selectRaw('YEAR(created_at) as tahun')->groupBy('tahun')->get();
    }

    public function getKodeDepositAttribute()
    {
        return $this->nomor_deposit != null ? 'D'.str_pad($this->nomor_deposit, 2, '0', STR_PAD_LEFT) : '';
    }

    public function getNfModalInvestorAttribute()
    {
        return $this->modal_investor != null ?  number_format($this->modal_investor, 0, ',', '.') : 0;
    }

    public function getNfSaldoAttribute()
    {
        return number_format($this->saldo, 0, ',', '.');
    }

    public function getKodeKasKecilAttribute()
    {
        return $this->nomor_kode_kas_kecil != null ? 'KK'.str_pad($this->nomor_kode_kas_kecil, 2, '0', STR_PAD_LEFT) : '';
    }

    public function getNfNominalAttribute()
    {
        return number_format($this->nominal, 0, ',', '.');
    }

    public function getTanggalAttribute()
    {
        return date('d-m-Y', strtotime($this->created_at));
    }

    public function saldoTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function modalInvestorTerakhir()
    {
        return $this->orderBy('id', 'desc')->first()->modal_investor_terakhir ?? 0;
    }

    public function kasBesar($month, $year)
    {
        return $this->with(['invoice_belanja'])->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function kasBesarByMonth($month, $year)
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

    public function deposit($data)
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['nomor_deposit'] = $this->max('nomor_deposit') + 1;
        $data['saldo'] = $this->saldoTerakhir() + $data['nominal'];
        $data['modal_investor'] = -$data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir() - $data['nominal'];
        $data['jenis'] = 1;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        DB::beginTransaction();

        try {

            $store = $this->create($data);

            $this->tambahModal($store->nominal, $store->investor_modal_id);

            $pesan =    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*Form Permintaan Deposit*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                        "*".$store->kode_deposit."*\n\n".
                        "Investor : ".$store->investorModal->nama."\n".
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

            DB::commit();

            $result = [
                'status' => "success",
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {

            DB::rollback();

            $result = [
                'status' => "error",
                'message' => 'Gagal menambahkan data',
                'data' => $th->getMessage(),
            ];
        }


        $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

        $this->sendWa($tujuan, $pesan);

        return $result;
    }


    public function withdraw($data)
    {
        $rekening = InvestorModal::find($data['investor_modal_id']);

        $data['uraian'] = "Withdraw";
        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal'];
        $data['modal_investor'] = $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir() + $data['nominal'];
        $data['jenis'] = 0;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        DB::beginTransaction();

        try {

            $store = $this->create($data);

            $this->kurangModal($store->nominal, $store->investor_modal_id);

            DB::commit();

            $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Pengembalian Deposit*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Investor : ".$store->investorModal->nama."\n".
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

            $result = [
                'status' => "success",
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {

                DB::rollback();
                $result = [
                    'status' => "error",
                    'message' => 'Gagal menambahkan data',
                    'data' => $th->getMessage(),
                ];
        }


        $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

        $this->sendWa($tujuan, $pesan);

        return $result;
    }

    public function keluarKasKecil()
    {
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();
        $data['nominal'] = 1000000;
        $data['nomor_kode_kas_kecil'] = $this->max('nomor_kode_kas_kecil') + 1;
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();
        $data['jenis'] = 0;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        $store = $this->create($data);

        return $store;
    }

    public function lainMasuk($data)
    {
        $rekening = Rekening::where('untuk', 'kas-besar')->first();

        $data['saldo'] = $this->saldoTerakhir() + $data['nominal'];
        $data['jenis'] = 1;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;
        $data['lain_lain'] = 1;
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();

        try {
            DB::beginTransaction();

            $store = $this->create($data);

            DB::commit();

            $group = GroupWa::where('untuk', 'kas-besar')->first();

            $pesan ="🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Lain2 (Dana Masuk)*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    "Uraian :  ".$store->uraian."\n".
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

            $this->sendWa($group->nama_group, $pesan);

            return [
                'status' => "success",
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {
            //throw $th;
            DB::rollBack();

            return [
                'status' => "error",
                'message' => $th->getMessage(),
            ];
        }
    }

    public function lainKeluar($data)
    {

        $data['saldo'] = $this->saldoTerakhir() - $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();
        $data['jenis'] = 0;
        $data['lain_lain'] = 1;

        $store = $this->create($data);

        return $store;
    }

    public function sendWa($tujuan, $pesan)
    {
        $send = new StarSender($tujuan, $pesan);
        $res = $send->sendGroup();

        $status = ($res == 'true') ? 1 : 0;

        PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $tujuan,
            'status' => $status,
        ]);
    }

    private function tambahModal($nominal, $investor_id)
    {
        $investor = InvestorModal::find($investor_id);
        $investor->update([
            'modal' => $investor->modal + $nominal
        ]);

        $this->hitungPersentase();
    }

    public function kurangModal($nominal, $investor_id)
    {
        $investor = InvestorModal::find($investor_id);
        $investor->update([
            'modal' => $investor->modal - $nominal
        ]);

        $this->hitungPersentase();
    }

    private function hitungPersentase()
    {
        $investors = InvestorModal::all();
        $totalModal = $investors->sum('modal');

        $percentages = $investors->mapWithKeys(function ($investor) use ($totalModal) {
            return [$investor->id => ($investor->modal / $totalModal) * 100];
        });

        $totalPercentage = $percentages->sum();

        if ($totalPercentage !== 100) {
            $percentages[$investors->first()->id] += 100 - $totalPercentage;
        }

        foreach ($percentages as $id => $percentage) {
            InvestorModal::where('id', $id)->update(['persentase' => $percentage]);
        }

    }

    public function withdrawAll($data)
    {

        $investor = InvestorModal::all();
        $nominalInvestor = $data['nominal'];
        $d = [];
        $pesan = [];

        foreach($investor as $i)
        {
            if ($i->persentase > 0) {
                $d[] = [
                    'uraian' => 'Withdraw '. $i->nama,
                    'nominal' => $data['nominal'] * $i->persentase / 100,
                    'jenis' => 0,
                    'investor_modal_id' => $i->id,
                    'no_rek' => $i->no_rek,
                    'bank' => $i->bank,
                    'nama_rek' => $i->nama_rek,
                ];
            }
        }

        $total = array_sum(array_column($d, 'nominal'));
        if ($total > $nominalInvestor) {
            $d[0]['nominal'] -= $total - $nominalInvestor;
        } elseif ($total < $nominalInvestor) {
            $d[0]['nominal'] += $nominalInvestor - $total;
        }

        try {
            DB::beginTransaction();

            $db = new KasBesar();

            foreach($d as $data)
            {
                $store = $db->create([
                    'uraian' => $data['uraian'],
                    'nominal' => $data['nominal'],
                    'jenis' => $data['jenis'],
                    'investor_modal_id' => $data['investor_modal_id'],
                    'no_rek' => $data['no_rek'],
                    'bank' => $data['bank'],
                    'nama_rek' => $data['nama_rek'],
                    'saldo' => $db->saldoTerakhir() - $data['nominal'],
                    'modal_investor' => $data['nominal'],
                    'modal_investor_terakhir' => $db->modalInvestorTerakhir() + $data['nominal'],
                ]);

                $db->kurangModal($store->nominal, $store->investor_modal_id);

                $pesan[] = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Form Pengembalian Deposit*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            "Investor : ".$store->investorModal->nama."\n".
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

            }

            DB::commit();

            $result = [
                'status' => "success",
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {

            DB::rollback();

            $result = [
                'status' => "error",
                'message' => $th->getMessage(),
            ];

            return $result;

        }

        $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

        foreach($pesan as $p)
        {
            $this->sendWa($tujuan, $p);
        }

        return $result;

    }

    public function ppn_masuk_susulan($nominal)
    {

        $nominal = str_replace('.', '', $nominal);


        $persenInvestor = Investor::where('nama', 'investor')->first()->persentase;
        $persenPengelola = Investor::where('nama', 'pengelola')->first()->persentase;
        $rekeningPengelola = Rekening::where('untuk', 'pengelola')->first();
        $investor = InvestorModal::where('persentase', '>', 0)->get();

        $saldoTerakhir = $this->saldoTerakhir();
        $nominalPengelola = $nominal * ($persenPengelola / 100);
        $nominalInvestor = $nominal * ($persenInvestor / 100);

        $pesan = [];
        $pembagian = [];

        if ($saldoTerakhir < $nominal) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($saldoTerakhir, 0, ',', '.'),
            ];
        }

        DB::beginTransaction();

        try {

            $store = $this->create([
                        'uraian' => 'PPN Masukan Susulan Pengelola',
                        'nominal' => $nominalPengelola,
                        'saldo' => $this->saldoTerakhir() - $nominalPengelola,
                        'modal_investor_terakhir' => $this->modalInvestorTerakhir(),
                        'jenis' => 0,
                        'no_rek' => $rekeningPengelola->no_rek,
                        'bank' => $rekeningPengelola->bank,
                        'nama_rek' => $rekeningPengelola->nama_rek,
                    ]);

            $p1 =   "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                    "*PPN Masukan Susulan*\n".
                    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                    "Uraian : ".$store['uraian']."\n\n".
                    "Nilai :  *Rp. ".number_format($store['nominal'], 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    "Bank      : ".$rekeningPengelola->bank."\n".
                    "Nama    : ".$rekeningPengelola->nama_rek."\n".
                    "No. Rek : ".$rekeningPengelola->no_rek."\n\n".
                    "==========================\n".
                    "Sisa Saldo Kas Besar : \n".
                    "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
                    "Total Modal Investor : \n".
                    "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
                    "Terima kasih 🙏🙏🙏\n";

            array_push($pesan, $p1);

            foreach($investor as $i)
            {
                $pembagian[] = [
                    'uraian' => 'PPn Masukan Susulan '. $i->nama,
                    'nominal' => $nominalInvestor * $i->persentase / 100,
                    'jenis' => 0,
                    'investor_modal_id' => $i->id,
                    'no_rek' => $i->no_rek,
                    'bank' => $i->bank,
                    'nama_rek' => $i->nama_rek,
                ];
            }

            $total = array_sum(array_column($pembagian, 'nominal'));
            if ($total > $nominalInvestor) {
                $pembagian[0]['nominal'] -= $total - $nominalInvestor;
            } elseif ($total < $nominalInvestor) {
                $pembagian[0]['nominal'] += $nominalInvestor - $total;
            }

            foreach($pembagian as $p)
            {
                $store = $this->create([
                    'uraian' => $p['uraian'],
                    'nominal' => $p['nominal'],
                    'jenis' => $p['jenis'],
                    'investor_modal_id' => $p['investor_modal_id'],
                    'no_rek' => $p['no_rek'],
                    'bank' => $p['bank'],
                    'nama_rek' => $p['nama_rek'],
                    'saldo' => $this->saldoTerakhir() - $p['nominal'],
                    'modal_investor_terakhir' => $this->modalInvestorTerakhir(),
                ]);

                $p2 =   "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*PPN Masukan Susulan*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
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

                array_push($pesan, $p2);

            }

            DB::commit();



        } catch (\Throwable $th) {

            DB::rollback();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }

        $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

        foreach($pesan as $p)
        {
            $this->sendWa($tujuan, $p);
        }

        return [
            'status' => 'success',
            'message' => 'Berhasil menambahkan data',
        ];


    }

    public function cost_operational($data)
    {
        $data['cost_operational'] = 1;

        $data['uraian'] = CostOperational::find($data['cost_operational_id'])->nama;

        unset($data['cost_operational_id']);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['jenis'] = 0;
        $data['saldo'] = $this->saldoTerakhir() - $data['nominal'];

        if ($data['saldo'] < $data['nominal']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($this->saldoTerakhir(), 0, ',', '.'),
            ];
        }

        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir();

        try {
            DB::beginTransaction();

            $store = $this->create($data);

            $pesan =    "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Cost Operational*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        "Uraian : ".$store->uraian."\n".
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

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

            $this->sendWa($tujuan, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {

                DB::rollback();

                return [
                    'status' => 'error',
                    'message' => $th->getMessage(),
                ];

        }
    }

    public function pphBadan($tahun)
    {
        return [];
    }
}
