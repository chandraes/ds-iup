<?php

// @formatter:off
// phpcs:ignoreFile
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * @property int $id
 * @property int|null $nomor
 * @property int $tipe
 * @property int|null $konsumen_id
 * @property int|null $barang_unit_id
 * @property int|null $karyawan_id
 * @property int $status 0: pending, 1: diajukan, 2: diproses, 3: selesai
 * @property string|null $waktu_diproses
 * @property string|null $waktu_diterima
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\BarangUnit|null $barang_unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\BarangReturDetail> $details
 * @property-read int|null $details_count
 * @property-read mixed $action
 * @property-read mixed $kode
 * @property-read mixed $status_badge
 * @property-read mixed $status_text
 * @property-read mixed $tanggal_en
 * @property-read mixed $tipe_text
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @property-read \App\Models\db\Konsumen|null $konsumen
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereTipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereWaktuDiproses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangRetur whereWaktuDiterima($value)
 */
	class BarangRetur extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $barang_retur_id
 * @property int $barang_id
 * @property int|null $barang_stok_harga_id
 * @property int $qty
 * @property int $stok_kurang
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read \App\Models\BarangRetur $barang_retur
 * @property-read mixed $nf_qty
 * @property-read \App\Models\db\Barang\BarangStokHarga|null $stok
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereBarangReturId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereStokKurang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangReturDetail whereUpdatedAt($value)
 */
	class BarangReturDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $untuk
 * @property string|null $nama
 * @property string|null $singkatan
 * @property string|null $alamat
 * @property string|null $kode_pos
 * @property string $logo
 * @property string|null $nama_direktur
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereKodePos($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereLogo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereNamaDirektur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereSingkatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Config whereUpdatedAt($value)
 */
	class Config extends \Eloquent {}
}

namespace App\Models\Dokumen{
/**
 * @property int $id
 * @property int $jenis_dokumen 1 kontrak-supplier, 2 kontrak-konsumen, 3 sph, 4 dll
 * @property string $nama
 * @property string $file
 * @property string|null $tanggal_expired
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $id_tanggal_expired
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData companyProfil()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData kontrakTambang()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData kontrakVendor()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData sph()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereJenisDokumen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereTanggalExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DokumenData whereUpdatedAt($value)
 */
	class DokumenData extends \Eloquent {}
}

namespace App\Models\Dokumen{
/**
 * @property int $id
 * @property int $kas_ppn
 * @property string $tahun
 * @property int $bulan
 * @property string $file
 * @property int $checklist
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereChecklist($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|MutasiRekening whereUpdatedAt($value)
 */
	class MutasiRekening extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $kas_ppn
 * @property int $lunas
 * @property int|null $karyawan_id
 * @property int|null $barang_id
 * @property int|null $barang_stok_harga_id
 * @property int $jumlah
 * @property float $harga
 * @property float $total
 * @property float $total_bayar
 * @property float $sisa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang|null $barang
 * @property-read \App\Models\db\Barang\BarangStokHarga|null $barang_stok_harga
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_sisa
 * @property-read mixed $nf_total
 * @property-read mixed $nf_total_bayar
 * @property-read mixed $tanggal
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereSisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereTotalBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GantiRugi whereUpdatedAt($value)
 */
	class GantiRugi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $untuk
 * @property string $nama_group
 * @property string|null $group_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereGroupId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereNamaGroup($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|GroupWa whereUpdatedAt($value)
 */
	class GroupWa extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $barang_stok_harga_id
 * @property int $user_id
 * @property int $harga_ajuan
 * @property int $min_jual_ajuan
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_harga_ajuan
 * @property-read mixed $nf_min_jual_ajuan
 * @property-read \App\Models\db\Barang\BarangStokHarga|null $stok
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereHargaAjuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereMinJualAjuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|HargaSubmission whereUserId($value)
 */
	class HargaSubmission extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $status
 * @property string $holding_url
 * @property string $token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereHoldingUrl($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Holding whereUpdatedAt($value)
 */
	class Holding extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $persentase
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor wherePersentase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Investor whereUpdatedAt($value)
 */
	class Investor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property string $no_hp
 * @property string $no_rek
 * @property string $bank
 * @property string $nama_rek
 * @property int $persentase
 * @property int $modal
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $keuntungan
 * @property-read mixed $nf_keuntungan
 * @property-read mixed $nf_modal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KasBesar> $kasBesar
 * @property-read int|null $kas_besar_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereModal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal wherePersentase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvestorModal whereUpdatedAt($value)
 */
	class InvestorModal extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $ppn_kas
 * @property string|null $uraian
 * @property int|null $nomor_deposit
 * @property int|null $nomor_kode_kas_kecil
 * @property int $jenis
 * @property int $nominal
 * @property int $saldo
 * @property string|null $no_rek
 * @property string|null $nama_rek
 * @property string|null $bank
 * @property int|null $modal_investor
 * @property int $modal_investor_terakhir
 * @property int $lain_lain
 * @property int $cost_operational
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $investor_modal_id
 * @property int|null $pengelola_id
 * @property int|null $invoice_inventaris_id
 * @property int|null $invoice_belanja_id
 * @property int|null $invoice_jual_id
 * @property-read mixed $kode_deposit
 * @property-read mixed $kode_kas_kecil
 * @property-read mixed $nf_modal_investor
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_saldo
 * @property-read mixed $tanggal
 * @property-read \App\Models\InvestorModal|null $investorModal
 * @property-read \App\Models\transaksi\InvoiceBelanja|null $invoice_belanja
 * @property-read \App\Models\transaksi\InvoiceJual|null $invoice_jual
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereCostOperational($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereInvestorModalId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereInvoiceBelanjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereInvoiceInventarisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereInvoiceJualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereLainLain($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereModalInvestor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereModalInvestorTerakhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereNomorDeposit($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereNomorKodeKasKecil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar wherePengelolaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar wherePpnKas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasBesar whereUraian($value)
 */
	class KasBesar extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $nomor_kode_kas_kecil
 * @property string|null $uraian
 * @property int $jenis
 * @property int $nominal
 * @property int $saldo
 * @property string|null $nama_rek
 * @property string|null $bank
 * @property string|null $no_rek
 * @property int $void
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $kode
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_saldo
 * @property-read mixed $tanggal
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereNomorKodeKasKecil($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereUraian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKecil whereVoid($value)
 */
	class KasKecil extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $konsumen_id
 * @property int|null $invoice_jual_id
 * @property string $uraian
 * @property int|null $cash
 * @property int|null $bayar
 * @property int|null $hutang
 * @property int|null $titipan
 * @property int $sisa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_bayar
 * @property-read mixed $nf_cash
 * @property-read mixed $nf_hutang
 * @property-read mixed $nf_sisa
 * @property-read mixed $tanggal
 * @property-read \App\Models\transaksi\InvoiceJual|null $invoice_jual
 * @property-read \App\Models\db\Konsumen $konsumen
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereBayar($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereCash($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereHutang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereInvoiceJualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereSisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereTitipan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KasKonsumen whereUraian($value)
 */
	class KasKonsumen extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property string $slug
 * @property string|null $file
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog filter(array $filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereSlug($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Katalog whereUpdatedAt($value)
 */
	class Katalog extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property string|null $no_hp
 * @property string|null $npwp
 * @property string|null $alamat
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenTemp whereUpdatedAt($value)
 */
	class KonsumenTemp extends \Eloquent {}
}

namespace App\Models\Legalitas{
/**
 * @property int $id
 * @property int $legalitas_kategori_id
 * @property string $nama
 * @property string $file
 * @property string|null $tanggal_expired
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Legalitas\LegalitasKategori $kategori
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereFile($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereLegalitasKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereTanggalExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasDokumen whereUpdatedAt($value)
 */
	class LegalitasDokumen extends \Eloquent {}
}

namespace App\Models\Legalitas{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Legalitas\LegalitasDokumen> $dokumen
 * @property-read int|null $dokumen_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LegalitasKategori whereUpdatedAt($value)
 */
	class LegalitasKategori extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $id_level_wilayah
 * @property string $nama_level_wilayah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah whereIdLevelWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah whereNamaLevelWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|LevelWilayah whereUpdatedAt($value)
 */
	class LevelWilayah extends \Eloquent {}
}

namespace App\Models\Pajak{
/**
 * @property int $id
 * @property int $keluaran_id
 * @property int $ppn_keluaran_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail whereKeluaranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail wherePpnKeluaranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapKeluaranDetail whereUpdatedAt($value)
 */
	class RekapKeluaranDetail extends \Eloquent {}
}

namespace App\Models\Pajak{
/**
 * @property int $id
 * @property int $masukan_id
 * @property int $ppn_masukan_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail whereMasukanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail wherePpnMasukanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapMasukanDetail whereUpdatedAt($value)
 */
	class RekapMasukanDetail extends \Eloquent {}
}

namespace App\Models\Pajak{
/**
 * @property int $id
 * @property string $uraian
 * @property int|null $masukan_id
 * @property int|null $keluaran_id
 * @property int $penyesuaian
 * @property int $jenis 0: keluaran, 1: masukan
 * @property int $nominal
 * @property int $saldo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_saldo
 * @property-read mixed $tanggal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pajak\RekapKeluaranDetail> $rekapKeluaranDetail
 * @property-read int|null $rekap_keluaran_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Pajak\RekapMasukanDetail> $rekapMasukanDetail
 * @property-read int|null $rekap_masukan_detail_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereKeluaranId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereMasukanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn wherePenyesuaian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapPpn whereUraian($value)
 */
	class RekapPpn extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $password
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PasswordKonfirmasi whereUpdatedAt($value)
 */
	class PasswordKonfirmasi extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $untuk
 * @property int $nilai
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nilai
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan whereNilai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengaturan whereUpdatedAt($value)
 */
	class Pengaturan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $nama
 * @property string $no_hp
 * @property int $persentase
 * @property string $no_rek
 * @property string $bank
 * @property string $nama_rek
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola wherePersentase($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pengelola whereUpdatedAt($value)
 */
	class Pengelola extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $pesan
 * @property string $tujuan
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa wherePesan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa whereTujuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PesanWa whereUpdatedAt($value)
 */
	class PesanWa extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $invoice_jual_id
 * @property string|null $uraian
 * @property int $nominal
 * @property int $saldo
 * @property int $is_faktur
 * @property string|null $no_faktur
 * @property int $dipungut
 * @property int $is_expired
 * @property int $is_keranjang
 * @property int $is_finish
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_saldo
 * @property-read mixed $tanggal
 * @property-read mixed $tanggal_en
 * @property-read \App\Models\transaksi\InvoiceJual|null $invoiceJual
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereDipungut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereInvoiceJualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereIsExpired($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereIsFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereIsFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereIsKeranjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereNoFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnKeluaran whereUraian($value)
 */
	class PpnKeluaran extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $invoice_belanja_id
 * @property int|null $inventaris_invoice_id
 * @property string|null $uraian
 * @property int $nominal
 * @property int $saldo
 * @property int $is_faktur
 * @property string|null $no_faktur
 * @property int $is_keranjang
 * @property int $is_finish
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_saldo
 * @property-read mixed $tanggal
 * @property-read mixed $tanggal_en
 * @property-read \App\Models\transaksi\InvoiceBelanja|null $inventarisInvoice
 * @property-read \App\Models\transaksi\InvoiceBelanja|null $invoiceBelanja
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereInventarisInvoiceId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereInvoiceBelanjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereIsFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereIsFinish($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereIsKeranjang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereNoFaktur($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereSaldo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PpnMasukan whereUraian($value)
 */
	class PpnMasukan extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $supplier_id
 * @property int $nomor
 * @property string|null $full_nomor
 * @property string $kepada
 * @property string $alamat
 * @property string $telepon
 * @property int $apa_ppn
 * @property int $status
 * @property int|null $user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $tanggal
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderItem> $items
 * @property-read int|null $items_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\PurchaseOrderNote> $notes
 * @property-read int|null $notes_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereApaPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereFullNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereKepada($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereTelepon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrder whereUserId($value)
 */
	class PurchaseOrder extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property int $barang_id
 * @property int $jumlah
 * @property int $harga_satuan
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_harga_satuan
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderItem whereUpdatedAt($value)
 */
	class PurchaseOrderItem extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $purchase_order_id
 * @property string $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\PurchaseOrder $purchaseOrder
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote whereNote($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote wherePurchaseOrderId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|PurchaseOrderNote whereUpdatedAt($value)
 */
	class PurchaseOrderNote extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $bulan
 * @property string $tahun
 * @property string $uraian
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\RekapGajiDetail> $details
 * @property-read int|null $details_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereBulan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereTahun($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGaji whereUraian($value)
 */
	class RekapGaji extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $rekap_gaji_id
 * @property string $nik
 * @property string $nama
 * @property string $jabatan
 * @property int $gaji_pokok
 * @property int $tunjangan_jabatan
 * @property int $tunjangan_keluarga
 * @property int $bpjs_tk
 * @property int $bpjs_k
 * @property int $potongan_bpjs_tk
 * @property int $potongan_bpjs_kesehatan
 * @property int $pendapatan_kotor
 * @property int $pendapatan_bersih
 * @property string $no_rek
 * @property string $nama_rek
 * @property string $bank
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\RekapGaji $rekap_gaji
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereBpjsK($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereBpjsTk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereGajiPokok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail wherePendapatanBersih($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail wherePendapatanKotor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail wherePotonganBpjsKesehatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail wherePotonganBpjsTk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereRekapGajiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereTunjanganJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereTunjanganKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|RekapGajiDetail whereUpdatedAt($value)
 */
	class RekapGajiDetail extends \Eloquent {}
}

namespace App\Models\Rekap{
/**
 * @property int $id
 * @property int $kas_ppn
 * @property int $kreditor_id
 * @property int $nominal
 * @property int $pph
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_pph
 * @property-read mixed $nf_total
 * @property-read mixed $tanggal
 * @property-read \App\Models\db\Kreditor $kreditor
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereKreditorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor wherePph($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BungaInvestor whereUpdatedAt($value)
 */
	class BungaInvestor extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $untuk
 * @property string $bank
 * @property string $no_rek
 * @property string $nama_rek
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Rekening whereUpdatedAt($value)
 */
	class Rekening extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $nomor
 * @property int|null $barang_unit_id
 * @property string|null $keterangan
 * @property int $tipe 99: void
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\BarangUnit|null $barang_unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ReturSupplierDetail> $details
 * @property-read int|null $details_count
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereTipe($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplier whereUpdatedAt($value)
 */
	class ReturSupplier extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $retur_supplier_id
 * @property int $barang_id
 * @property int $qty
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_qty
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereReturSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|ReturSupplierDetail whereUpdatedAt($value)
 */
	class ReturSupplierDetail extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $barang_id
 * @property int $total_qty_karantina
 * @property int $total_qty_diproses
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\StokReturSource> $sources
 * @property-read int|null $sources_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereTotalQtyDiproses($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereTotalQtyKarantina($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokRetur whereUpdatedAt($value)
 */
	class StokRetur extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $user_id
 * @property int $stok_retur_id
 * @property int $barang_unit_id
 * @property int $qty
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\StokRetur $stok_retur
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereStokReturId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturCart whereUserId($value)
 */
	class StokReturCart extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int $stok_retur_id
 * @property int $barang_retur_detail_id
 * @property int|null $barang_stok_harga_id
 * @property int $qty_diterima
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\BarangReturDetail $detail
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereBarangReturDetailId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereQtyDiterima($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereStokReturId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|StokReturSource whereUpdatedAt($value)
 */
	class StokReturSource extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property int|null $karyawan_id
 * @property string $username
 * @property string $name
 * @property string $role
 * @property string|null $email
 * @property string|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $barang_unit_id
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\KeranjangBeli> $keranjangBeli
 * @property-read int|null $keranjang_beli_count
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereRole($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|User whereUsername($value)
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * @property int $id
 * @property string $id_wilayah
 * @property int $id_level_wilayah
 * @property string $id_negara
 * @property string $nama_wilayah
 * @property string|null $id_induk_wilayah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdIndukWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdLevelWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdNegara($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereIdWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereNamaWilayah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Wilayah whereUpdatedAt($value)
 */
	class Wilayah extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int|null $barang_unit_id
 * @property int $barang_kategori_id
 * @property int|null $barang_nama_id
 * @property int $barang_type_id
 * @property int|null $subpg_id
 * @property int|null $satuan_id
 * @property int|null $jenis 1: ppn, 2: non-ppn, 3: keduanya
 * @property int $detail 0: Tidak ada detail, 1: Ada detail
 * @property string|null $kode
 * @property string|null $merk
 * @property numeric $diskon Diskon dengan waktu berlaku
 * @property string|null $diskon_mulai Tanggal mulai diskon
 * @property string|null $diskon_selesai Tanggal selesai diskon
 * @property int $is_grosir Menandakan apakah barang ini memiliki harga grosir
 * @property string|null $keterangan
 * @property string|null $foto
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\BarangNama|null $barang_nama
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangDetailType> $detail_types
 * @property-read int|null $detail_types_count
 * @property-read mixed $text_jenis
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangGrosir> $grosir
 * @property-read int|null $grosir_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangHistory> $histories
 * @property-read int|null $histories_count
 * @property-read \App\Models\db\Barang\BarangKategori $kategori
 * @property-read \App\Models\db\Satuan|null $satuan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangStokHarga> $stok_harga
 * @property-read int|null $stok_harga_count
 * @property-read \App\Models\db\Barang\Subpg|null $subpg
 * @property-read \App\Models\db\Barang\BarangType $type
 * @property-read \App\Models\db\Barang\BarangUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang filter($filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang filterByKategori($kategori)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereBarangKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereBarangNamaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereBarangTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereDiskonMulai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereDiskonSelesai($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereFoto($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereIsGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereKeterangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereMerk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereSatuanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereSubpgId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Barang withHbLama()
 */
	class Barang extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int $barang_id
 * @property int|null $barang_type_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read \App\Models\db\Barang\BarangType|null $type
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType whereBarangTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangDetailType whereUpdatedAt($value)
 */
	class BarangDetailType extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int $barang_id
 * @property numeric $diskon Diskon khusus untuk grosir
 * @property int $qty Quantity of grosir
 * @property int $qty_grosir Jumlah qty untuk grosir
 * @property int|null $satuan_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read \App\Models\db\Satuan|null $satuan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereQtyGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereSatuanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangGrosir whereUpdatedAt($value)
 */
	class BarangGrosir extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property string|null $uraian
 * @property int $barang_id
 * @property string|null $nama
 * @property int $jenis 0 = keluar, 1 = masuk
 * @property int $jumlah
 * @property int $harga
 * @property int|null $invoice_belanja_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_total
 * @property-read mixed $tanggal
 * @property-read mixed $total
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereInvoiceBelanjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangHistory whereUraian($value)
 */
	class BarangHistory extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property string $nama
 * @property int $urut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangNama> $barang_nama
 * @property-read int|null $barang_nama_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\Barang> $barangs
 * @property-read int|null $barangs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangType> $types
 * @property-read int|null $types_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori filterByKategori($kategoriFilter)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori rowspan()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangKategori whereUrut($value)
 */
	class BarangKategori extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int $barang_kategori_id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\Barang> $barang
 * @property-read int|null $barang_count
 * @property-read \App\Models\db\Barang\BarangKategori $kategori
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama whereBarangKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangNama whereUpdatedAt($value)
 */
	class BarangNama extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int $barang_stok_harga_id
 * @property string $kode
 * @property int $jual
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereJual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokDetail whereUpdatedAt($value)
 */
	class BarangStokDetail extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property int|null $barang_unit_id
 * @property int|null $barang_type_id
 * @property int|null $barang_kategori_id
 * @property int|null $barang_nama_id
 * @property int $barang_id
 * @property int $stok_awal
 * @property int $stok
 * @property int $harga
 * @property float $harga_beli
 * @property int|null $min_jual
 * @property int $hide
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read \App\Models\db\Barang\BarangNama|null $barang_nama
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_harga_beli
 * @property-read mixed $nf_min_jual
 * @property-read mixed $nf_stok
 * @property-read mixed $nf_stok_awal
 * @property-read mixed $tanggal
 * @property-read \App\Models\HargaSubmission|null $harga_temp
 * @property-read \App\Models\db\Barang\BarangKategori|null $kategori
 * @property-read \App\Models\db\Satuan|null $satuan
 * @property-read \App\Models\db\Barang\BarangType|null $type
 * @property-read \App\Models\db\Barang\BarangUnit|null $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereBarangKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereBarangNamaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereBarangTypeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereHargaBeli($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereHide($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereMinJual($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereStok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereStokAwal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangStokHarga whereUpdatedAt($value)
 */
	class BarangStokHarga extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $typeRowspan
 * @property \Illuminate\Support\Collection $groupedBarangs
 * @property int $id
 * @property int $barang_unit_id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\Barang> $barangs
 * @property-read int|null $barangs_count
 * @property-read \App\Models\db\Barang\BarangUnit $unit
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType filterByType($type)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangType whereUpdatedAt($value)
 */
	class BarangType extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $unitRowspan
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $unit_rowspan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangType> $types
 * @property-read int|null $types_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit filterByUnit($unitFilter)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|BarangUnit whereUpdatedAt($value)
 */
	class BarangUnit extends \Eloquent {}
}

namespace App\Models\db\Barang{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\Barang> $barang
 * @property-read int|null $barang_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Subpg whereUpdatedAt($value)
 */
	class Subpg extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|CostOperational whereUpdatedAt($value)
 */
	class CostOperational extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $untuk
 * @property int $kode
 * @property float $persen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum wherePersen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|DiskonUmum whereUpdatedAt($value)
 */
	class DiskonUmum extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int $kategori_id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_sum_jumlah
 * @property-read mixed $nf_sum_total
 * @property-read mixed $sum_jumlah
 * @property-read mixed $sum_total
 * @property-read \App\Models\db\InventarisKategori $kategori
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\InventarisRekap> $rekap
 * @property-read int|null $rekap_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis whereKategoriId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisJenis whereUpdatedAt($value)
 */
	class InventarisJenis extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $sum_jumlah
 * @property-read mixed $sum_total
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\InventarisJenis> $jenis
 * @property-read int|null $jenis_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisKategori whereUpdatedAt($value)
 */
	class InventarisKategori extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int|null $inventaris_jenis_id
 * @property string $status
 * @property \App\Models\db\InventarisJenis|null $jenis
 * @property string $uraian
 * @property int $jumlah
 * @property int $pengurangan
 * @property int $harga_satuan
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_harga_satuan
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_total
 * @property-read mixed $tanggal
 * @property-read \App\Models\transaksi\InventarisInvoice|null $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereInventarisJenisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap wherePengurangan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisRekap whereUraian($value)
 */
	class InventarisRekap extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property int $is_sales
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Karyawan> $karyawan
 * @property-read int|null $karyawan_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereIsSales($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Jabatan whereUpdatedAt($value)
 */
	class Jabatan extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int $nomor
 * @property int $jabatan_id
 * @property string $nama
 * @property string $nickname
 * @property int $gaji_pokok
 * @property int $tunjangan_jabatan
 * @property int $tunjangan_keluarga
 * @property string $nik
 * @property string $npwp
 * @property string $bpjs_tk
 * @property int $apa_bpjs_tk
 * @property string $bpjs_kesehatan
 * @property int $apa_bpjs_kes
 * @property string $tempat_lahir
 * @property string $tanggal_lahir
 * @property string $alamat
 * @property string $no_hp
 * @property string $bank
 * @property string $no_rek
 * @property string $nama_rek
 * @property string $mulai_bekerja
 * @property string|null $foto_ktp
 * @property string|null $foto_diri
 * @property int $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\GantiRugi> $ganti_rugi
 * @property-read int|null $ganti_rugi_count
 * @property-read \App\Models\db\Jabatan $jabatan
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereApaBpjsKes($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereApaBpjsTk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereBpjsKesehatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereBpjsTk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereFotoDiri($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereFotoKtp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereGajiPokok($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereJabatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereMulaiBekerja($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNickname($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereTanggalLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereTempatLahir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereTunjanganJabatan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereTunjanganKeluarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Karyawan whereUpdatedAt($value)
 */
	class Karyawan extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\KelompokRuteDetail> $details
 * @property-read int|null $details_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRute whereUpdatedAt($value)
 */
	class KelompokRute extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int $kelompok_rute_id
 * @property int $wilayah_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\KelompokRute $kelompokRute
 * @property-read \App\Models\Wilayah $wilayah
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail whereKelompokRuteId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KelompokRuteDetail whereWilayahId($value)
 */
	class KelompokRuteDetail extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $kode
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Konsumen> $konsumen
 * @property-read int|null $konsumen_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KodeToko whereUpdatedAt($value)
 */
	class KodeToko extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int|null $karyawan_id
 * @property int $kode
 * @property int|null $kode_toko_id
 * @property string $nama
 * @property string|null $nik
 * @property string|null $upload_ktp
 * @property string $cp
 * @property string $no_hp
 * @property string $npwp
 * @property string|null $no_kantor
 * @property string|null $provinsi_id
 * @property string|null $kabupaten_kota_id
 * @property string|null $kecamatan_id
 * @property string|null $kota
 * @property string $alamat
 * @property int $pembayaran
 * @property int|null $plafon
 * @property int|null $tempo_hari
 * @property numeric $diskon_khusus Diskon khusus untuk konsumen
 * @property int $active
 * @property string|null $alasan
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\KonsumenDoc> $docs
 * @property-read int|null $docs_count
 * @property-read mixed $full_kode
 * @property-read mixed $nf_plafon
 * @property-read mixed $sistem_pembayaran
 * @property-read \App\Models\Wilayah|null $kabupaten_kota
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\KasKonsumen> $kas
 * @property-read int|null $kas_count
 * @property-read \App\Models\Wilayah|null $kecamatan
 * @property-read \App\Models\db\KodeToko|null $kode_toko
 * @property-read \App\Models\Wilayah|null $provinsi
 * @property-read \App\Models\db\SalesArea|null $sales_area
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen filter($filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereAlasan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereCp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereDiskonKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKabupatenKotaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKecamatanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKodeTokoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereNik($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereNoKantor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen wherePembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen wherePlafon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereProvinsiId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereTempoHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Konsumen whereUploadKtp($value)
 */
	class Konsumen extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int $konsumen_id
 * @property int $is_khusus
 * @property int|null $barang_unit_id
 * @property string $nama
 * @property string $file_path
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\BarangUnit|null $barang_unit
 * @property-read mixed $file_url
 * @property-read \App\Models\db\Konsumen $konsumen
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereFilePath($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereIsKhusus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KonsumenDoc whereUpdatedAt($value)
 */
	class KonsumenDoc extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property float $persen
 * @property string $npwp
 * @property string $no_rek
 * @property string $nama_rek
 * @property string $bank
 * @property int $apa_pph
 * @property int $is_active
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereApaPph($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereIsActive($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereNpwp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor wherePersen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Kreditor whereUpdatedAt($value)
 */
	class Kreditor extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $untuk
 * @property float $persen
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak wherePersen($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak whereUntuk($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Pajak whereUpdatedAt($value)
 */
	class Pajak extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Konsumen> $konsumen
 * @property-read int|null $konsumen_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|SalesArea whereUpdatedAt($value)
 */
	class SalesArea extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property string $nama
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Satuan whereUpdatedAt($value)
 */
	class Satuan extends \Eloquent {}
}

namespace App\Models\db{
/**
 * @property int $id
 * @property int|null $barang_unit_id
 * @property int $status
 * @property int $kode
 * @property string $nama
 * @property int $pembayaran 1: Cash, 2: Tempo
 * @property int|null $tempo_hari
 * @property string|null $kota
 * @property string $alamat
 * @property string $cp
 * @property string $no_hp
 * @property string $no_rek
 * @property string $bank
 * @property string $nama_rek
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $kode_supplier
 * @property-read mixed $sistem_pembayaran
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereAlamat($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereKota($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNama($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNoHp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier wherePembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereTempoHari($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Supplier whereUpdatedAt($value)
 */
	class Supplier extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int|null $inventaris_id
 * @property int $pembayaran 1: cash, 2: tempo, 3: kredit
 * @property string|null $uraian
 * @property int $jumlah
 * @property int $harga_satuan
 * @property int $ppn
 * @property int $add_fee
 * @property int $diskon
 * @property int $total
 * @property int $dp
 * @property int $lama_cicilan
 * @property int $nominal_cicilan
 * @property int $sisa_cicilan
 * @property string|null $tanggal_jatuh_tempo
 * @property string $no_rek
 * @property string $bank
 * @property string $nama_rek
 * @property int $lunas
 * @property int $void
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $dpp
 * @property-read mixed $id_tanggal_jatuh_tempo
 * @property-read mixed $nf_add_fee
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_dp
 * @property-read mixed $nf_dpp
 * @property-read mixed $nf_harga_satuan
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_sisa_bayar
 * @property-read mixed $nf_total
 * @property-read mixed $sisa_bayar
 * @property-read mixed $tanggal
 * @property-read \App\Models\db\InventarisRekap|null $inventaris
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereAddFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereDp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereInventarisId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereLamaCicilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereNominalCicilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice wherePembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereSisaCicilan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereTanggalJatuhTempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereUraian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InventarisInvoice whereVoid($value)
 */
	class InventarisInvoice extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $supplier_id
 * @property int $kas_ppn
 * @property int $nomor
 * @property string $uraian
 * @property float $diskon
 * @property float $ppn
 * @property float $add_fee
 * @property float $total
 * @property float $dp
 * @property float $dp_ppn
 * @property float $sisa
 * @property float $sisa_ppn
 * @property string $nama_rek
 * @property string $no_rek
 * @property string $bank
 * @property int $tempo
 * @property string|null $jatuh_tempo
 * @property int $void
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\InvoiceBelanjaDetail> $detail
 * @property-read int|null $detail_count
 * @property-read mixed $dpp
 * @property-read mixed $dpp_setelah_diskon
 * @property-read mixed $en_jatuh_tempo
 * @property-read mixed $id_jatuh_tempo
 * @property-read mixed $kode
 * @property-read mixed $nf_add_fee
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_dp
 * @property-read mixed $nf_dp_ppn
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_sisa
 * @property-read mixed $nf_sisa_ppn
 * @property-read mixed $nf_total
 * @property-read mixed $raw_dpp
 * @property-read mixed $tanggal
 * @property-read mixed $tanggal_en
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\InvoiceBelanjaCicil> $invoice_belanja_cicil
 * @property-read int|null $invoice_belanja_cicil_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\db\Barang\BarangHistory> $items
 * @property-read int|null $items_count
 * @property-read \App\Models\db\Supplier $supplier
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereAddFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereBank($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereDp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereDpPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereJatuhTempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereNamaRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereNoRek($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereSisa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereSisaPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereSupplierId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereTempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereUraian($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanja whereVoid($value)
 */
	class InvoiceBelanja extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $invoice_belanja_id
 * @property int $nominal
 * @property int $ppn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_total
 * @property-read mixed $tanggal
 * @property-read mixed $total
 * @property-read \App\Models\transaksi\InvoiceBelanja $invoice_belanja
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil whereInvoiceBelanjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaCicil whereUpdatedAt($value)
 */
	class InvoiceBelanjaCicil extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $invoice_belanja_id
 * @property int $barang_history_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\transaksi\InvoiceBelanja $invoice
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail whereBarangHistoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail whereInvoiceBelanjaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceBelanjaDetail whereUpdatedAt($value)
 */
	class InvoiceBelanjaDetail extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $sistem_pembayaran 1 = Cash, 2 = Tempo, 3 = Titipan
 * @property int $lunas
 * @property int $titipan
 * @property int $void
 * @property int $kas_ppn
 * @property int $nomor
 * @property string $kode
 * @property int|null $konsumen_id
 * @property int|null $konsumen_temp_id
 * @property int $total
 * @property int $diskon
 * @property int $ppn
 * @property int $add_fee
 * @property int $grand_total
 * @property int $dp
 * @property int $dp_ppn
 * @property int $sisa_tagihan
 * @property int $sisa_ppn
 * @property string|null $jatuh_tempo
 * @property int $ppn_dipungut
 * @property int $send_wa
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property int|null $karyawan_id
 * @property-read mixed $dpp
 * @property-read mixed $dpp_setelah_diskon
 * @property-read mixed $full_kode
 * @property-read mixed $id_jatuh_tempo
 * @property-read mixed $nf_add_fee
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_dp
 * @property-read mixed $nf_dp_ppn
 * @property-read mixed $nf_grand_total
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_sisa_ppn
 * @property-read mixed $nf_sisa_tagihan
 * @property-read mixed $sistem_pembayaran_word
 * @property-read mixed $tanggal
 * @property-read mixed $tanggal_en
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\InvoiceJualDetail> $invoice_detail
 * @property-read int|null $invoice_detail_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\InvoiceJualCicil> $invoice_jual_cicil
 * @property-read int|null $invoice_jual_cicil_count
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @property-read \App\Models\db\Konsumen|null $konsumen
 * @property-read \App\Models\KonsumenTemp|null $konsumen_temp
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual billing($filters, $kas_ppn, $titipan)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual gabung($filters)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereAddFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereDp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereDpPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereJatuhTempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereKode($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereKonsumenTempId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereLunas($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereNomor($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual wherePpnDipungut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereSendWa($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereSisaPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereSisaTagihan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereSistemPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereTitipan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJual whereVoid($value)
 */
	class InvoiceJual extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $invoice_jual_id
 * @property int $nominal
 * @property int $ppn
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $nf_nominal
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_total
 * @property-read mixed $tanggal
 * @property-read mixed $total
 * @property-read \App\Models\transaksi\InvoiceJual $invoice_jual
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil whereInvoiceJualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil whereNominal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualCicil whereUpdatedAt($value)
 */
	class InvoiceJualCicil extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $invoice_jual_id
 * @property int|null $barang_id
 * @property int|null $barang_stok_harga_id
 * @property int $jumlah
 * @property int $is_grosir Penjualan grosir
 * @property int|null $jumlah_grosir Jumlah grosir yang dibeli
 * @property int|null $satuan_grosir_id
 * @property int $harga_satuan
 * @property int $diskon
 * @property int $ppn
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang|null $barang
 * @property-read mixed $harga_diskon_dpp
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_harga_satuan_akhir
 * @property-read mixed $nf_harga_satuan
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_jumlah_grosir
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_total
 * @property-read \App\Models\transaksi\InvoiceJual $invoice
 * @property-read \App\Models\db\Satuan|null $satuan_grosir
 * @property-read \App\Models\db\Barang\BarangStokHarga|null $stok
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereInvoiceJualId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereIsGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereJumlahGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereSatuanGrosirId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualDetail whereUpdatedAt($value)
 */
	class InvoiceJualDetail extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int|null $karyawan_id
 * @property int $sistem_pembayaran 1 = Cash, 2 = Tempo, 3 = Titipan
 * @property int $is_finished
 * @property int $kas_ppn
 * @property int|null $konsumen_id
 * @property int $total
 * @property int $diskon
 * @property int $ppn
 * @property int $add_fee
 * @property int $grand_total
 * @property int $dp
 * @property int $dp_ppn
 * @property int $sisa_tagihan
 * @property int $sisa_ppn
 * @property int $ppn_dipungut
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $dpp
 * @property-read mixed $dpp_setelah_diskon
 * @property-read mixed $nf_add_fee
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_dp
 * @property-read mixed $nf_dp_ppn
 * @property-read mixed $nf_grand_total
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_sisa_ppn
 * @property-read mixed $nf_sisa_tagihan
 * @property-read mixed $sistem_pembayaran_word
 * @property-read mixed $tanggal
 * @property-read mixed $tanggal_en
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\InvoiceJualSalesDetail> $invoice_detail
 * @property-read int|null $invoice_detail_count
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @property-read \App\Models\db\Konsumen|null $konsumen
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\OrderInden> $order_inden
 * @property-read int|null $order_inden_count
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereAddFee($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereDp($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereDpPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereGrandTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales wherePpnDipungut($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereSisaPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereSisaTagihan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereSistemPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSales whereUpdatedAt($value)
 */
	class InvoiceJualSales extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $invoice_jual_sales_id
 * @property int|null $barang_id
 * @property int|null $barang_stok_harga_id
 * @property int $jumlah
 * @property int $is_grosir Penjualan grosir
 * @property int|null $jumlah_grosir Jumlah grosir yang dibeli
 * @property int|null $satuan_grosir_id
 * @property int $harga_satuan
 * @property int $diskon
 * @property int $ppn
 * @property int $total
 * @property int $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang|null $barang
 * @property-read \App\Models\db\Barang\BarangStokHarga|null $barangStokHarga
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_harga_satuan
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_jumlah_grosir
 * @property-read mixed $nf_total
 * @property-read \App\Models\transaksi\InvoiceJualSales $invoiceJualSales
 * @property-read \App\Models\db\Satuan|null $satuan_grosir
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereInvoiceJualSalesId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereIsGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereJumlahGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereSatuanGrosirId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|InvoiceJualSalesDetail whereUpdatedAt($value)
 */
	class InvoiceJualSalesDetail extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $jenis 1: kas_ppn, 2: kas_non_ppn
 * @property int $user_id
 * @property int $tempo
 * @property int $barang_id
 * @property int $jumlah
 * @property int $harga
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_total
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereJenis($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereTempo($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|Keranjang whereUserId($value)
 */
	class Keranjang extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $user_id
 * @property int $barang_unit_id
 * @property int $kas_ppn
 * @property int $sistem_pembayaran 1: Cash, 2: Tempo
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\BarangUnit $barang_unit
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\KeranjangBeliDetail> $details
 * @property-read int|null $details_count
 * @property-read mixed $kas_ppn_text
 * @property-read mixed $sistem_pembayaran_text
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereBarangUnitId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereKasPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereSistemPembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeli whereUserId($value)
 */
	class KeranjangBeli extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $keranjang_beli_id
 * @property int $barang_id
 * @property int $qty
 * @property int $harga
 * @property int $total
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_qty
 * @property-read mixed $nf_total
 * @property-read \App\Models\transaksi\KeranjangBeli|null $keranjang
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereHarga($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereKeranjangBeliId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereQty($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangBeliDetail whereUpdatedAt($value)
 */
	class KeranjangBeliDetail extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int|null $user_id
 * @property int|null $barang_id
 * @property int $jumlah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang|null $barang
 * @property-read mixed $nf_jumlah
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangInden whereUserId($value)
 */
	class KeranjangInden extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $keranjang_jual_konsumen_id
 * @property int $barang_ppn
 * @property int $barang_id
 * @property int $barang_stok_harga_id
 * @property int $jumlah
 * @property int $is_grosir Penjualan grosir
 * @property int|null $jumlah_grosir Jumlah grosir yang dibeli
 * @property int|null $satuan_grosir_id
 * @property int $harga_satuan
 * @property int $diskon Diskon yang diberikan pada keranjang jual
 * @property int $ppn PPN yang dikenakan pada keranjang jual
 * @property int $total_ppn Total PPN dikali qty yang dikenakan pada keranjang jual
 * @property int $total_diskon diskon x jumlah
 * @property int $harga_satuan_akhir Harga satuan akhir setelah diskon dan PPN
 * @property int $total
 * @property int $stok_kurang
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $harga_diskon_dpp
 * @property-read mixed $nf_diskon
 * @property-read mixed $nf_harga
 * @property-read mixed $nf_harga_satuan_akhir
 * @property-read mixed $nf_jumlah
 * @property-read mixed $nf_ppn
 * @property-read mixed $nf_total
 * @property-read \App\Models\db\Satuan|null $satuan_grosir
 * @property-read \App\Models\db\Barang\BarangStokHarga $stok
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereBarangPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereBarangStokHargaId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereHargaSatuan($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereHargaSatuanAkhir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereIsGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereJumlahGrosir($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereKeranjangJualKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual wherePpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereSatuanGrosirId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereStokKurang($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereTotal($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereTotalDiskon($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereTotalPpn($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJual whereUpdatedAt($value)
 */
	class KeranjangJual extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $user_id
 * @property int $konsumen_id
 * @property int $pembayaran
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read mixed $sistem_pembayaran_word
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\KeranjangJual> $keranjang_jual
 * @property-read int|null $keranjang_jual_count
 * @property-read \App\Models\db\Konsumen $konsumen
 * @property-read \App\Models\User $user
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen wherePembayaran($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|KeranjangJualKonsumen whereUserId($value)
 */
	class KeranjangJualKonsumen extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int|null $karyawan_id
 * @property int $konsumen_id
 * @property int $is_finished
 * @property int $jumlah
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\transaksi\OrderIndenDetail> $detail
 * @property-read int|null $detail_count
 * @property-read mixed $tanggal
 * @property-read \App\Models\db\Karyawan|null $karyawan
 * @property-read \App\Models\db\Konsumen $konsumen
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereIsFinished($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereKaryawanId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereKonsumenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderInden whereUpdatedAt($value)
 */
	class OrderInden extends \Eloquent {}
}

namespace App\Models\transaksi{
/**
 * @property int $id
 * @property int $order_inden_id
 * @property int $barang_id
 * @property int $jumlah
 * @property int $deleted
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\db\Barang\Barang $barang
 * @property-read mixed $nf_jumlah
 * @property-read \App\Models\transaksi\OrderInden $orderInden
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail query()
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereBarangId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereDeleted($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereJumlah($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereOrderIndenId($value)
 * @method static \Illuminate\Database\Eloquent\Builder<static>|OrderIndenDetail whereUpdatedAt($value)
 */
	class OrderIndenDetail extends \Eloquent {}
}

