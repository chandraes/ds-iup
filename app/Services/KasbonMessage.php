<?php

namespace App\Services;

use App\Models\KasBesar;
use App\Models\Kasbon;

class KasbonMessage
{
    public function format(Kasbon $loan, KasBesar $cash, bool $returned): string
    {
        $heading = $returned ? "🔵🔵🔵🔵🔵🔵🔵🔵🔵\n" : "🔴🔴🔴🔴🔴🔴🔴🔴🔴\n";
        $title = $returned ? 'Form Void Kasbon Staff' : 'Form Kasbon Staff';
        $description = $returned ? $cash->uraian : ($loan->jenis === 'cicilan' ? 'Cicilan '.$loan->cicil_kali.'X' : 'Potong Gaji');
        $schedule = ! $returned && $loan->jenis === 'cicilan'
            ? 'Mulai : '.$loan->mulai_periode->copy()->locale('id')->translatedFormat('F Y')."\n"
            : '';

        return $heading.'*'.$title."*\n".$heading."\n".
            'Nama : '.$loan->karyawan->nama."\n".
            'Uraian : '.$description."\n".$schedule."\n".
            'Nilai :  *Rp. '.number_format($cash->nominal, 0, ',', '.')."*\n\n".
            "Ditransfer ke rek:\n\n".
            'Bank     : '.$cash->bank."\n".
            'Nama    : '.$cash->nama_rek."\n".
            'No. Rek : '.$cash->no_rek."\n\n".
            "==========================\n".
            "Sisa Saldo Kas Besar PPN : \n".
            'Rp. '.number_format($cash->saldo, 0, ',', '.')."\n\n".
            "Total Modal Investor PPN : \n".
            'Rp. '.number_format($cash->modal_investor_terakhir, 0, ',', '.')."\n\n".
            "Terima kasih 🙏🙏🙏\n";
    }
}
