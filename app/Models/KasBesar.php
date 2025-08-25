<?php

namespace App\Models;

use App\Models\db\CostOperational;
use App\Models\db\Kreditor;
use App\Models\db\Pajak;
use App\Models\Legalitas\LegalitasDokumen;
use App\Models\Rekap\BungaInvestor;
use App\Models\transaksi\InvoiceBelanja;
use App\Models\transaksi\InvoiceJual;
use App\Services\StarSender;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class KasBesar extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected $appends = ['nf_nominal', 'tanggal', 'kode_deposit', 'kode_kas_kecil', 'nf_saldo', 'nf_modal_investor'];

    public function invoice_belanja()
    {
        return $this->belongsTo(InvoiceBelanja::class);
    }

    public function invoice_jual()
    {
        return $this->belongsTo(InvoiceJual::class);
    }

    public function investorModal()
    {
        return $this->belongsTo(InvestorModal::class);
    }

    public function generateNomorDeposit($ppn)
    {
        return $this->where('ppn_kas', $ppn)->max('nomor_deposit') + 1;
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
        return $this->modal_investor != null ? number_format($this->modal_investor, 0, ',', '.') : 0;
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

    public function saldoTerakhir($ppn)
    {
        return $this->where('ppn_kas', $ppn)->orderBy('id', 'desc')->first()->saldo ?? 0;
    }

    public function modalInvestorTerakhir($ppn)
    {
        return $this->where('ppn_kas', $ppn)->orderBy('id', 'desc')->first()->modal_investor_terakhir ?? 0;
    }

    public function kasBesar($month, $year, $ppn)
    {
        return $this->where('ppn_kas', $ppn)->whereMonth('created_at', $month)->whereYear('created_at', $year)->get();
    }

    public function costOperational($month, $year, $co = '')
    {
        $data = $this->where('ppn_kas', 1)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->where('cost_operational', 1)
            ->when($co != '', function ($query) use ($co) {
                $query->where('uraian', 'like', '%'.$co.'%');
            })
            ->get();

        return $data;
    }

    public function kasBesarByMonth($month, $year, $ppn)
    {
        $data = $this->where('ppn_kas', $ppn)
            ->whereMonth('created_at', $month)
            ->whereYear('created_at', $year)
            ->orderBy('id', 'desc')
            ->first();

        if (! $data) {
            $data = $this->where('ppn_kas', $ppn)->where('created_at', '<', Carbon::create($year, $month, 1))
                ->orderBy('id', 'desc')
                ->first();
        }

        return $data;
    }

    public function deposit($data)
    {
        $kas = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
        $kodeKas = $data['ppn_kas'] == 1 ? 'Kas Besar PPN' : 'Kas Besar Non PPN';

        $rekening = Rekening::where('untuk', $kas)->first();

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['nomor_deposit'] = $this->generateNomorDeposit($data['ppn_kas']);
        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) + $data['nominal'];
        $data['modal_investor'] = -$data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']) - $data['nominal'];
        $data['jenis'] = 1;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        DB::beginTransaction();

        try {

            $store = $this->create($data);

            $kasPpn = [
                'saldo' => $this->saldoTerakhir(1),
                'modal_investor' => $this->modalInvestorTerakhir(1),
            ];

            $kasNonPpn = [
                'saldo' => $this->saldoTerakhir(0),
                'modal_investor' => $this->modalInvestorTerakhir(0),
            ];

            if ($data['ppn_kas'] == 1) {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor PPN: \n".
                            'Rp. '.number_format($kasPpn['modal_investor'], 0, ',', '.')."\n\n";
            } else {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['modal_investor'], 0, ',', '.')."\n\n";
            }

            // sum modal investor
            $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

            $this->tambahModal($store->nominal, $store->investor_modal_id);

            $checkLegalitas = LegalitasDokumen::whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '<', Carbon::now()->addDays(45))->get();
            $warningPesan = '';

            if ($checkLegalitas->count() > 0) {
                $warningPesan = "\n==========================\nWARNING : \n";
                $no = 1;
                foreach ($checkLegalitas as $legalitas) {
                    $warningPesan .= $no++.'. '.$legalitas->nama.' - '.date('d-m-Y', strtotime($legalitas->tanggal_expired))."\n";
                }
            }

            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                        "*Form Permintaan Deposit*\n".
                        "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                        '*'.$store->kode_deposit."*\n".
                        '*'.$kodeKas."*\n\n".
                        'Investor : '.$store->investorModal->nama."\n".
                        'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        'Bank      : '.$store->bank."\n".
                        'Nama    : '.$store->nama_rek."\n".
                        'No. Rek : '.$store->no_rek."\n\n".
                        "==========================\n".
                        $addPesan.
                        "Grand Total Modal Investor : \n".
                        'Rp. '.number_format($totalModal, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n".
                        $warningPesan;

            DB::commit();

            $result = [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {

            DB::rollback();

            $result = [
                'status' => 'error',
                'message' => 'Gagal menambahkan data',
                'data' => $th->getMessage(),
            ];
        }

        $tujuan = GroupWa::where('untuk', $kas)->first()->nama_group;

        $this->sendWa($tujuan, $pesan);

        return $result;
    }

    public function withdraw($data)
    {
        $rekening = InvestorModal::find($data['investor_modal_id']);
        $kas = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
        $kodeKas = $data['ppn_kas'] == 1 ? 'Kas Besar PPN' : 'Kas Besar Non PPN';
        $data['uraian'] = 'Withdraw';
        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) - $data['nominal'];
        $data['modal_investor'] = $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']) + $data['nominal'];
        $data['jenis'] = 0;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        DB::beginTransaction();

        try {

            $store = $this->create($data);

            $this->kurangModal($store->nominal, $store->investor_modal_id);

            $kasPpn = [
                'saldo' => $this->saldoTerakhir(1),
                'modal_investor' => $this->modalInvestorTerakhir(1),
            ];

            $kasNonPpn = [
                'saldo' => $this->saldoTerakhir(0),
                'modal_investor' => $this->modalInvestorTerakhir(0),
            ];

            if ($data['ppn_kas'] == 1) {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor PPN: \n".
                            'Rp. '.number_format($kasPpn['modal_investor'], 0, ',', '.')."\n\n";
            } else {
                $addPesan = "Sisa Saldo Kas Besar: \n".
                            'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['modal_investor'], 0, ',', '.')."\n\n";
            }

            // sum modal investor
            $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

            DB::commit();

            $checkLegalitas = LegalitasDokumen::whereNotNull('tanggal_expired')
                ->where('tanggal_expired', '<', Carbon::now()->addDays(45))->get();
            $warningPesan = '';

            if ($checkLegalitas->count() > 0) {
                $warningPesan = "\n==========================\nWARNING : \n";
                $no = 1;
                foreach ($checkLegalitas as $legalitas) {
                    $warningPesan .= $no++.'. '.$legalitas->nama.' - '.date('d-m-Y', strtotime($legalitas->tanggal_expired))."\n";
                }
            }

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Pengembalian Deposit*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                         '*'.$kodeKas."*\n\n".
                        'Investor : '.$store->investorModal->nama."\n".
                        'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        'Bank      : '.$store->bank."\n".
                        'Nama    : '.$store->nama_rek."\n".
                        'No. Rek : '.$store->no_rek."\n\n".
                        "==========================\n".
                        $addPesan.
                        "Grand Total Modal Investor : \n".
                        'Rp. '.number_format($totalModal, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n".
                        $warningPesan;

            $result = [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {

            DB::rollback();
            $result = [
                'status' => 'error',
                'message' => 'Gagal menambahkan data',
                'data' => $th->getMessage(),
            ];
        }

        $tujuan = GroupWa::where('untuk', $kas)->first()->nama_group;

        $this->sendWa($tujuan, $pesan);

        return $result;
    }

    public function keluarKasKecil()
    {
        $rekening = Rekening::where('untuk', 'kas-kecil')->first();
        $data['nominal'] = 1000000;
        $data['nomor_kode_kas_kecil'] = $this->max('nomor_kode_kas_kecil') + 1;
        $data['ppn_kas'] = 1;
        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) - $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']);
        $data['jenis'] = 0;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;

        $store = $this->create($data);

        return $store;
    }

    public function lainMasuk($data)
    {
        $kas = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

        $rekening = Rekening::where('untuk', $kas)->first();

        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) + $data['nominal'];
        $data['jenis'] = 1;
        $data['no_rek'] = $rekening->no_rek;
        $data['bank'] = $rekening->bank;
        $data['nama_rek'] = $rekening->nama_rek;
        $data['lain_lain'] = 1;
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']);

        try {
            DB::beginTransaction();

            $store = $this->create($data);

            $kasPpn = [
                'saldo' => $this->saldoTerakhir(1),
                'modal_investor' => $this->modalInvestorTerakhir(1),
            ];

            $kasNonPpn = [
                'saldo' => $this->saldoTerakhir(0),
                'modal_investor' => $this->modalInvestorTerakhir(0),
            ];

            // sum modal investor
            $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

            DB::commit();

            $group = GroupWa::where('untuk', $kas)->first();

            $pesan = "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n".
                    "*Form Lain2 (Dana Masuk)*\n".
                    "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n\n".
                    'Uraian :  '.$store->uraian."\n".
                    'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank      : '.$store->bank."\n".
                    'Nama    : '.$store->nama_rek."\n".
                    'No. Rek : '.$store->no_rek."\n\n".
                    "==========================\n";
            if ($data['ppn_kas'] == 1) {
                $pesan .= "Sisa Saldo Kas Besar PPN: \n".
                            'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                        "Total Modal Investor PPN: \n".
                        'Rp. '.number_format($kasPpn['modal_investor'], 0, ',', '.')."\n\n";
            } else {
                $pesan .= "Sisa Saldo Kas Besar Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor Non PPN: \n".
                            'Rp. '.number_format($kasNonPpn['modal_investor'], 0, ',', '.')."\n\n";
            }
            $pesan .= "Terima kasih 🙏🙏🙏\n";

            $this->sendWa($group->nama_group, $pesan);

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
                'data' => $store,
            ];

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }

    public function lainKeluar($data)
    {

        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) - $data['nominal'];
        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']);
        $data['jenis'] = 0;
        $data['lain_lain'] = 1;

        $store = $this->create($data);

        return $store;
    }

    public function sendWa($tujuan, $pesan)
    {
        $storeWa = PesanWa::create([
            'pesan' => $pesan,
            'tujuan' => $tujuan,
            'status' => 0,
        ]);

        $send = new StarSender($tujuan, $pesan);
        $res = $send->sendGroup();

        if ($res == 'true') {
            $storeWa->update(['status' => 1]);
        }

    }

    public function generateMessage($isIn, $title, $kasPpn, $uraian, $nominal, $rekening, $additionalMessage = null)
    {

        $lineHeading = $isIn == 1 ? "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n" : "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n";

        $kasPpn = [
            'saldo' => $this->saldoTerakhir(1),
            'modal_investor' => $this->modalInvestorTerakhir(1),
        ];

        $kasNonPpn = [
            'saldo' => $this->saldoTerakhir(0),
            'modal_investor' => $this->modalInvestorTerakhir(0),
        ];

        $sisaSaldo = '';

        if ($kasPpn == 1) {
            $sisaSaldo .= "Sisa Saldo Kas Besar PPN: \n".
                        'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                    "Total Modal Investor PPN: \n".
                    'Rp. '.number_format($kasPpn['modal_investor'], 0, ',', '.')."\n\n";
        } else {
            $sisaSaldo .= "Sisa Saldo Kas Besar Non PPN: \n".
                        'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                        "Total Modal Investor Non PPN: \n".
                        'Rp. '.number_format($kasNonPpn['modal_investor'], 0, ',', '.')."\n\n";
        }

        $pesan = $lineHeading.
                    '*'.$title."*\n".
                    $lineHeading.'\n'.
                    'Uraian : '.$uraian."\n".
                    'Nilai :  *Rp. '.number_format($nominal, 0, ',', '.')."*\n\n".
                    "Ditransfer ke rek:\n\n".
                    'Bank      : '.$rekening['bank']."\n".
                    'Nama    : '.$rekening['nama_rek']."\n".
                    'No. Rek : '.$rekening['no_rek']."\n\n".
                    "==========================\n".
                    $sisaSaldo.
                    $additionalMessage.
                    "Terima kasih 🙏🙏🙏\n";

        return $pesan;

    }

    private function tambahModal($nominal, $investor_id)
    {
        $investor = InvestorModal::find($investor_id);
        $investor->update([
            'modal' => $investor->modal + $nominal,
        ]);

        $this->hitungPersentase();
    }

    public function kurangModal($nominal, $investor_id)
    {
        $investor = InvestorModal::find($investor_id);
        $investor->update([
            'modal' => $investor->modal - $nominal,
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

        foreach ($investor as $i) {
            if ($i->persentase > 0) {
                $d[] = [
                    'uraian' => 'Withdraw '.$i->nama,
                    'nominal' => $data['nominal'] * $i->persentase / 100,
                    'ppn_kas' => $data['ppn_kas'], // Add this line
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

            $db = new KasBesar;

            foreach ($d as $data) {
                $store = $db->create([
                    'uraian' => $data['uraian'],
                    'nominal' => $data['nominal'],
                    'ppn_kas' => $data['ppn_kas'],
                    'jenis' => $data['jenis'],
                    'investor_modal_id' => $data['investor_modal_id'],
                    'no_rek' => $data['no_rek'],
                    'bank' => $data['bank'],
                    'nama_rek' => $data['nama_rek'],
                    'saldo' => $db->saldoTerakhir($data['ppn_kas']) - $data['nominal'],
                    'modal_investor' => $data['nominal'],
                    'modal_investor_terakhir' => $db->modalInvestorTerakhir($data['ppn_kas']) + $data['nominal'],
                ]);

                $db->kurangModal($store->nominal, $store->investor_modal_id);

                $kasPpn = [
                    'saldo' => $this->saldoTerakhir(1),
                    'modal_investor' => $this->modalInvestorTerakhir(1),
                ];

                $kasNonPpn = [
                    'saldo' => $this->saldoTerakhir(0),
                    'modal_investor' => $this->modalInvestorTerakhir(0),
                ];

                // sum modal investor
                $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

                $pesan[] = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Form Pengembalian Deposit*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            'Investor : '.$store->investorModal->nama."\n".
                            'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank      : '.$store->bank."\n".
                            'Nama    : '.$store->nama_rek."\n".
                            'No. Rek : '.$store->no_rek."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar PPN: \n".
                            'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                            "Sisa Saldo Kas Besar  NON PPN: \n".
                            'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            'Rp. '.number_format($totalModal, 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";

            }

            DB::commit();

            $result = [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {

            DB::rollback();

            $result = [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];

            return $result;

        }
        $groupName = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

        $tujuan = GroupWa::where('untuk', $groupName)->first()->nama_group;

        foreach ($pesan as $p) {
            $this->sendWa($tujuan, $p);
        }

        return $result;

    }

    public function getKas()
    {
        $kasPpn = [
            'saldo_ppn' => $this->saldoTerakhir(1),
            'saldo_non_ppn' => $this->saldoTerakhir(0),
            'modal_investor_terakhir' => $this->modalInvestorTerakhir(1) + $this->modalInvestorTerakhir(0),
            'modal_investor_ppn' => $this->modalInvestorTerakhir(1),
            'modal_investor_non_ppn' => $this->modalInvestorTerakhir(0),
        ];

        return $kasPpn;
    }

    // public function ppn_masuk_susulan($nominal)
    // {

    //     $nominal = str_replace('.', '', $nominal);

    //     $persenInvestor = Investor::where('nama', 'investor')->first()->persentase;
    //     $persenPengelola = Investor::where('nama', 'pengelola')->first()->persentase;
    //     $rekeningPengelola = Rekening::where('untuk', 'pengelola')->first();
    //     $investor = InvestorModal::where('persentase', '>', 0)->get();

    //     $saldoTerakhir = $this->saldoTerakhir();
    //     $nominalPengelola = $nominal * ($persenPengelola / 100);
    //     $nominalInvestor = $nominal * ($persenInvestor / 100);

    //     $pesan = [];
    //     $pembagian = [];

    //     if ($saldoTerakhir < $nominal) {
    //         return [
    //             'status' => 'error',
    //             'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($saldoTerakhir, 0, ',', '.'),
    //         ];
    //     }

    //     DB::beginTransaction();

    //     try {

    //         $store = $this->create([
    //                     'uraian' => 'PPN Masukan Susulan Pengelola',
    //                     'nominal' => $nominalPengelola,
    //                     'saldo' => $this->saldoTerakhir() - $nominalPengelola,
    //                     'modal_investor_terakhir' => $this->modalInvestorTerakhir(),
    //                     'jenis' => 0,
    //                     'no_rek' => $rekeningPengelola->no_rek,
    //                     'bank' => $rekeningPengelola->bank,
    //                     'nama_rek' => $rekeningPengelola->nama_rek,
    //                 ]);

    //         $p1 =   "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
    //                 "*PPN Masukan Susulan*\n".
    //                 "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
    //                 "Uraian : ".$store['uraian']."\n\n".
    //                 "Nilai :  *Rp. ".number_format($store['nominal'], 0, ',', '.')."*\n\n".
    //                 "Ditransfer ke rek:\n\n".
    //                 "Bank      : ".$rekeningPengelola->bank."\n".
    //                 "Nama    : ".$rekeningPengelola->nama_rek."\n".
    //                 "No. Rek : ".$rekeningPengelola->no_rek."\n\n".
    //                 "==========================\n".
    //                 "Sisa Saldo Kas Besar : \n".
    //                 "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
    //                 "Total Modal Investor : \n".
    //                 "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
    //                 "Terima kasih 🙏🙏🙏\n";

    //         array_push($pesan, $p1);

    //         foreach($investor as $i)
    //         {
    //             $pembagian[] = [
    //                 'uraian' => 'PPn Masukan Susulan '. $i->nama,
    //                 'nominal' => $nominalInvestor * $i->persentase / 100,
    //                 'jenis' => 0,
    //                 'investor_modal_id' => $i->id,
    //                 'no_rek' => $i->no_rek,
    //                 'bank' => $i->bank,
    //                 'nama_rek' => $i->nama_rek,
    //             ];
    //         }

    //         $total = array_sum(array_column($pembagian, 'nominal'));
    //         if ($total > $nominalInvestor) {
    //             $pembagian[0]['nominal'] -= $total - $nominalInvestor;
    //         } elseif ($total < $nominalInvestor) {
    //             $pembagian[0]['nominal'] += $nominalInvestor - $total;
    //         }

    //         foreach($pembagian as $p)
    //         {
    //             $store = $this->create([
    //                 'uraian' => $p['uraian'],
    //                 'nominal' => $p['nominal'],
    //                 'jenis' => $p['jenis'],
    //                 'investor_modal_id' => $p['investor_modal_id'],
    //                 'no_rek' => $p['no_rek'],
    //                 'bank' => $p['bank'],
    //                 'nama_rek' => $p['nama_rek'],
    //                 'saldo' => $this->saldoTerakhir() - $p['nominal'],
    //                 'modal_investor_terakhir' => $this->modalInvestorTerakhir(),
    //             ]);

    //             $p2 =   "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
    //                     "*PPN Masukan Susulan*\n".
    //                     "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
    //                     "Uraian : ".$store->uraian."\n".
    //                     "Nilai :  *Rp. ".number_format($store->nominal, 0, ',', '.')."*\n\n".
    //                     "Ditransfer ke rek:\n\n".
    //                     "Bank      : ".$store->bank."\n".
    //                     "Nama    : ".$store->nama_rek."\n".
    //                     "No. Rek : ".$store->no_rek."\n\n".
    //                     "==========================\n".
    //                     "Sisa Saldo Kas Besar : \n".
    //                     "Rp. ".number_format($store->saldo, 0, ',', '.')."\n\n".
    //                     "Total Modal Investor : \n".
    //                     "Rp. ".number_format($store->modal_investor_terakhir, 0, ',', '.')."\n\n".
    //                     "Terima kasih 🙏🙏🙏\n";

    //             array_push($pesan, $p2);

    //         }

    //         DB::commit();

    //     } catch (\Throwable $th) {

    //         DB::rollback();

    //         return [
    //             'status' => 'error',
    //             'message' => $th->getMessage(),
    //         ];
    //     }

    //     $tujuan = GroupWa::where('untuk', 'kas-besar')->first()->nama_group;

    //     foreach($pesan as $p)
    //     {
    //         $this->sendWa($tujuan, $p);
    //     }

    //     return [
    //         'status' => 'success',
    //         'message' => 'Berhasil menambahkan data',
    //     ];

    // }

    public function cost_operational($data)
    {
        $data['cost_operational'] = 1;

        $data['uraian'] = CostOperational::find($data['cost_operational_id'])->nama;

        unset($data['cost_operational_id']);

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $data['jenis'] = 0;
        $data['saldo'] = $this->saldoTerakhir($data['ppn_kas']) - $data['nominal'];

        if ($this->saldoTerakhir($data['ppn_kas']) < $data['nominal']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($this->saldoTerakhir($data['ppn_kas']), 0, ',', '.'),
            ];
        }

        $data['modal_investor_terakhir'] = $this->modalInvestorTerakhir($data['ppn_kas']);

        try {
            DB::beginTransaction();

            $store = $this->create($data);

            $kasPpn = [
                'saldo' => $this->saldoTerakhir(1),
                'modal_investor' => $this->modalInvestorTerakhir(1),
            ];

            $kasNonPpn = [
                'saldo' => $this->saldoTerakhir(0),
                'modal_investor' => $this->modalInvestorTerakhir(0),
            ];

            // sum modal investor
            $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

            $pesan = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Cost Operational*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        'Uraian : '.$store->uraian."\n".
                        'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        'Bank      : '.$store->bank."\n".
                        'Nama    : '.$store->nama_rek."\n".
                        'No. Rek : '.$store->no_rek."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar PPN: \n".
                        'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                        "Sisa Saldo Kas Besar  NON PPN: \n".
                        'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        'Rp. '.number_format($totalModal, 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            DB::commit();

            $tujuan = GroupWa::where('untuk', 'kas-besar-ppn')->first()->nama_group;

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

    public function dividen($data)
    {
        $persen = Investor::all();
        $pengelola = Pengelola::where('persentase', '>', 0)->get();
        $investor = InvestorModal::where('persentase', '>', 0)->get();

        $data['nominal'] = str_replace('.', '', $data['nominal']);
        $arrayPengelola = [];
        $arrayInvestor = [];

        $saldo = $this->saldoTerakhir($data['ppn_kas']);

        if ($saldo < $data['nominal']) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($saldo, 0, ',', '.'),
            ];
        }

        foreach ($persen as $p) {
            $nominal = $data['nominal'] * $p->persentase / 100;

            if ($p->nama == 'pengelola') {
                foreach ($pengelola as $peng) {
                    $arrayPengelola[] = [
                        'ppn_kas' => $data['ppn_kas'],
                        'uraian' => 'Dividen Pengelola '.$peng->nama,
                        'nominal' => $nominal * $peng->persentase / 100,
                        'jenis' => 0,
                        'investor_modal_id' => null,
                        'no_rek' => $peng->no_rek,
                        'bank' => $peng->bank,
                        'nama_rek' => $peng->nama_rek,
                        'pengelola_id' => $peng->id,
                        'modal_investor_terakhir' => $this->modalInvestorTerakhir($data['ppn_kas']),
                    ];
                }

                // check total nominal
                $total = array_sum(array_column($arrayPengelola, 'nominal'));

                if ($total > $nominal) {
                    $arrayPengelola[0]['nominal'] -= $total - $nominal;
                } elseif ($total < $nominal) {
                    $arrayPengelola[0]['nominal'] += $nominal - $total;
                }
            }

            if ($p->nama == 'investor') {
                foreach ($investor as $inv) {
                    $arrayInvestor[] = [
                        'ppn_kas' => $data['ppn_kas'],
                        'uraian' => 'Dividen Investor '.$inv->nama,
                        'nominal' => $nominal * $inv->persentase / 100,
                        'jenis' => 0,
                        'investor_modal_id' => $inv->id,
                        'no_rek' => $inv->no_rek,
                        'bank' => $inv->bank,
                        'nama_rek' => $inv->nama_rek,
                        'modal_investor_terakhir' => $this->modalInvestorTerakhir($data['ppn_kas']),
                    ];
                }

                // check total nominal
                $total = array_sum(array_column($arrayInvestor, 'nominal'));

                if ($total > $nominal) {
                    $arrayInvestor[0]['nominal'] -= $total - $nominal;
                } elseif ($total < $nominal) {
                    $arrayInvestor[0]['nominal'] += $nominal - $total;
                }
            }
        }

        // join array
        $dataAll = array_merge($arrayPengelola, $arrayInvestor);

        $arrayPesan = [];
        try {
            DB::beginTransaction();

            foreach ($dataAll as $da) {

                $store = $this->create([
                    'uraian' => $da['uraian'],
                    'nominal' => $da['nominal'],
                    'ppn_kas' => $da['ppn_kas'],
                    'jenis' => $da['jenis'],
                    'investor_modal_id' => $da['investor_modal_id'],
                    'no_rek' => $da['no_rek'],
                    'bank' => $da['bank'],
                    'nama_rek' => $da['nama_rek'],
                    'saldo' => $this->saldoTerakhir($da['ppn_kas']) - $da['nominal'],
                    'modal_investor_terakhir' => $da['modal_investor_terakhir'],
                ]);

                $kasPpn = [
                    'saldo' => $this->saldoTerakhir(1),
                    'modal_investor' => $this->modalInvestorTerakhir(1),
                ];

                $kasNonPpn = [
                    'saldo' => $this->saldoTerakhir(0),
                    'modal_investor' => $this->modalInvestorTerakhir(0),
                ];

                // sum modal investor
                $totalModal = $kasPpn['modal_investor'] + $kasNonPpn['modal_investor'];

                $arrayPesan[] = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                                        "*Form Dividen*\n".
                                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                                        'Uraian : '.$store->uraian."\n".
                                        'Nilai :  *Rp. '.number_format($store->nominal, 0, ',', '.')."*\n\n".
                                        "Ditransfer ke rek:\n\n".
                                        'Bank      : '.$store->bank."\n".
                                        'Nama    : '.$store->nama_rek."\n".
                                        'No. Rek : '.$store->no_rek."\n\n".
                                        "==========================\n".
                                        "Sisa Saldo Kas Besar PPN: \n".
                                        'Rp. '.number_format($kasPpn['saldo'], 0, ',', '.')."\n\n".
                                        "Sisa Saldo Kas Besar  NON PPN: \n".
                                        'Rp. '.number_format($kasNonPpn['saldo'], 0, ',', '.')."\n\n".
                                        "Total Modal Investor : \n".
                                        'Rp. '.number_format($totalModal, 0, ',', '.')."\n\n".
                                        "Terima kasih 🙏🙏🙏\n";
            }

            DB::commit();

            $groupName = $data['ppn_kas'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';
            $tujuan = GroupWa::where('untuk', $groupName)->first()->nama_group;

            foreach ($arrayPesan as $key => $value) {
                // dd($value);
                $this->sendWa($tujuan, $value);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil menyimpan data!!',
            ];

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollBack();

            return [
                'status' => 'error',
                'message' => $th->getMessage(),
            ];
        }
    }

    public function bunga_investor($data)
    {
        $kreditor = Kreditor::find($data['kreditor_id']);

        $pph_val = Pajak::where('untuk', 'pph-investor')->first()->persen / 100;
        $data['nominal'] = str_replace('.', '', $data['nominal_transaksi']);
        $data['pph'] = $kreditor->apa_pph == 1 ? $data['nominal'] * $pph_val : 0;
        $data['total'] = $data['nominal'] - $data['pph'];

        $saldo = $this->saldoTerakhir($data['kas_ppn']);
        $pesan = [];

        if ($data['total'] > $saldo) {
            return [
                'status' => 'error',
                'message' => 'Saldo kas besar tidak mencukupi!! Sisa Saldo : Rp. '.number_format($saldo, 0, ',', '.'),
            ];
        }

        try {
            DB::beginTransaction();

            $storeBunga = BungaInvestor::create([
                'kas_ppn' => $data['kas_ppn'],
                'kreditor_id' => $data['kreditor_id'],
                'nominal' => $data['nominal'],
                'pph' => $data['pph'],
                'total' => $data['total'],
            ]);

            $kas = [
                'ppn_kas' => $data['kas_ppn'],
                'uraian' => 'Bunga Kreditur '.$kreditor->nama,
                'jenis' => 0,
                'nominal' => $storeBunga->total,
                'saldo' => $this->saldoTerakhir($data['kas_ppn']) - $storeBunga->total,
                'nama_rek' => substr($data['transfer_ke'], 0, 15),
                'bank' => $data['bank'],
                'no_rek' => $data['no_rekening'],
                'modal_investor_terakhir' => $this->modalInvestorTerakhir($data['kas_ppn']),

            ];

            $storeKas = $this->create($kas);

            $pesan[] = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                        "*Form Bunga Kreditur*\n".
                        "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                        'Nama Kreditur : '.$kreditor->nama."\n\n".
                        'Nilai      : *Rp. '.number_format($storeBunga->total, 0, ',', '.')."*\n\n".
                        "Ditransfer ke rek:\n\n".
                        'Bank      : '.$storeKas->bank."\n".
                        'Nama    : '.$storeKas->nama_rek."\n".
                        'No. Rek : '.$storeKas->no_rek."\n\n".
                        "==========================\n".
                        "Sisa Saldo Kas Besar : \n".
                        'Rp. '.number_format($this->saldoTerakhir($data['kas_ppn']), 0, ',', '.')."\n\n".
                        "Total Modal Investor : \n".
                        'Rp. '.number_format($this->modalInvestorTerakhir($data['kas_ppn']), 0, ',', '.')."\n\n".
                        "Terima kasih 🙏🙏🙏\n";

            if ($kreditor->apa_pph == 1) {

                $kasPph = [
                    'ppn_kas' => $data['kas_ppn'],
                    'uraian' => 'PPH Bunga Kreditur '.$kreditor->nama,
                    'jenis' => 0,
                    'nominal' => $storeBunga->pph,
                    'saldo' => $this->saldoTerakhir($data['kas_ppn']) - $storeBunga->pph,
                    'nama_rek' => substr('Pajak', 0, 15),
                    'bank' => 'Pajak',
                    'no_rek' => 'Rekening Pajak',
                    'modal_investor_terakhir' => $this->modalInvestorTerakhir($data['kas_ppn']),

                ];

                $storePph = $this->create($kasPph);

                $pesan[] = "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n".
                            "*Form PPh Bunga Kreditur*\n".
                            "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n\n".
                            'Nilai PPH        : *Rp. '.number_format($storePph->nominal, 0, ',', '.')."*\n\n".
                            "Ditransfer ke rek:\n\n".
                            'Bank      : '.$storePph->bank."\n".
                            'Nama    : '.$storePph->nama_rek."\n".
                            'No. Rek : '.$storePph->no_rek."\n\n".
                            "==========================\n".
                            "Sisa Saldo Kas Besar : \n".
                            'Rp. '.number_format($this->saldoTerakhir($data['kas_ppn']), 0, ',', '.')."\n\n".
                            "Total Modal Investor : \n".
                            'Rp. '.number_format($this->modalInvestorTerakhir($data['kas_ppn']), 0, ',', '.')."\n\n".
                            "Terima kasih 🙏🙏🙏\n";
            }

            DB::commit();

            $untuk = $data['kas_ppn'] == 1 ? 'kas-besar-ppn' : 'kas-besar-non-ppn';

            $tujuan = GroupWa::where('untuk', $untuk)->first()->nama_group;

            foreach ($pesan as $key => $value) {
                $this->sendWa($tujuan, $value);
            }

            return [
                'status' => 'success',
                'message' => 'Berhasil menambahkan data',
            ];

        } catch (\Throwable $th) {
            // throw $th;
            DB::rollback();

            return [
                'status' => 'error',
                'message' => 'Gagal Menyimpan Data. '.$th->getMessage(),
            ];
        }
    }
}
