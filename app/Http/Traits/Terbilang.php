<?php

namespace App\Http\Traits;

trait Terbilang
{
    /**
     * Mengubah angka menjadi teks dalam bahasa Indonesia.
     *
     * @param int|float $nilai
     * @return string
     */
    private function penyebut(int|float $nilai): string
    {
        $nilai = abs($nilai);
        $libs = [
            "", "satu", "dua", "tiga", "empat", "lima", "enam",
            "tujuh", "delapan", "sembilan", "sepuluh", "sebelas"
        ];

        if ($nilai < 12) {
            return " " . $libs[$nilai];
        }

        if ($nilai < 20) {
            return $this->penyebut($nilai - 10) . " belas";
        }

        if ($nilai < 100) {
            return $this->penyebut(intval($nilai / 10)) . " puluh" . $this->penyebut($nilai % 10);
        }

        if ($nilai < 200) {
            return " seratus" . $this->penyebut($nilai - 100);
        }

        if ($nilai < 1000) {
            return $this->penyebut(intval($nilai / 100)) . " ratus" . $this->penyebut($nilai % 100);
        }

        if ($nilai < 2000) {
            return " seribu" . $this->penyebut($nilai - 1000);
        }

        if ($nilai < 1_000_000) {
            return $this->penyebut(intval($nilai / 1000)) . " ribu" . $this->penyebut($nilai % 1000);
        }

        if ($nilai < 1_000_000_000) {
            return $this->penyebut(intval($nilai / 1_000_000)) . " juta" . $this->penyebut($nilai % 1_000_000);
        }

        if ($nilai < 1_000_000_000_000) {
            return $this->penyebut(intval($nilai / 1_000_000_000)) . " milyar" . $this->penyebut(fmod($nilai, 1_000_000_000));
        }

        if ($nilai < 1_000_000_000_000_000) {
            return $this->penyebut(intval($nilai / 1_000_000_000_000)) . " trilyun" . $this->penyebut(fmod($nilai, 1_000_000_000_000));
        }

        return '';
    }

    /**
     * Konversi angka menjadi kalimat pembilang (termasuk minus).
     *
     * @param int|float $nilai
     * @return string
     */
    public function pembilang(int|float $nilai): string
    {
        $hasil = $nilai < 0
            ? 'minus ' . trim($this->penyebut($nilai))
            : trim($this->penyebut($nilai));

        return $hasil;
    }
}
