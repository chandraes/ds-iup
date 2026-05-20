@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1">
                    <li class="breadcrumb-item"><a href="{{ route('billing.invoice-janji-bayar') }}">Daftar Janji Bayar</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Detail Dokumen</li>
                </ol>
            </nav>
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-info-circle text-info"></i> Detail Janji Bayar: {{ $janjiBayar->kode }}</h1>
        </div>
        <a href="{{ route('billing.invoice-janji-bayar') }}" class="btn btn-secondary shadow-sm">
            <i class="fa fa-arrow-left"></i> Kembali ke Daftar
        </a>
    </div>

    <div class="row">
        <div class="col-lg-4 mb-4">
            <div class="card shadow-sm border-0 border-top border-info border-4 h-100">
                <div class="card-header bg-white py-3">
                    <h6 class="m-0 fw-bold text-info"><i class="fa fa-vcard"></i> Informasi Komitmen</h6>
                </div>
                <div class="card-body">
                    <table class="table table-borderless sm mb-0">
                        <tr>
                            <td class="text-muted" width="40%">Kode Dokumen</td>
                            <td class="fw-bold">: {{ $janjiBayar->kode }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Konsumen</td>
                            <td class="fw-bold">: {{ $janjiBayar->konsumen->kode_toko?->kode.' '.$janjiBayar->konsumen->nama }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Metode</td>
                            <td class="fw-bold">: <span class="text-uppercase badge bg-dark">{{ $janjiBayar->metode }}</span></td>
                        </tr>
                        <tr>
                            <td class="text-muted">Nominal Janji</td>
                            <td class="fw-bold text-success">: Rp {{ number_format($janjiBayar->nominal, 0, ',', '.') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Jatuh Tempo</td>
                            <td class="fw-bold">: {{ \Carbon\Carbon::parse($janjiBayar->jatuh_tempo)->format('d-m-Y') }}</td>
                        </tr>
                        <tr>
                            <td class="text-muted">Status Saat Ini</td>
                            <td>:
                                {!! $janjiBayar->status_label !!}
                            </td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-lg-8 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="m-0 fw-bold"><i class="fa fa-list"></i> Cakupan Invoice Terkait</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0" style="font-size: 0.9rem;">
                            <thead class="table-light">
                                <tr>
                                    <th class="text-center" width="8%">No</th>
                                    <th>Nomor Nota / Invoice</th>
                                    <th>Tanggal Transaksi</th>
                                    <th class="text-end">Sisa Tagihan Saat Dijaminkan</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $grandTotalSisa = 0; @endphp
                                @foreach($details as $index => $detail)
                                    @if($detail->invoice_jual)
                                        @php $grandTotalSisa += $detail->invoice_jual->sisa_tagihan; @endphp
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td>
                                                <a href="{{ route('billing.invoice-konsumen.detail', $detail->invoice_jual_id) }}" target="_blank" class="fw-bold">
                                                    {{ $detail->invoice_jual->kode }}
                                                </a>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($detail->invoice_jual->created_at)->format('d-m-Y') }}</td>
                                            <td class="text-end fw-bold text-danger">
                                                Rp {{ number_format($detail->invoice_jual->sisa_tagihan, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @else
                                        <tr>
                                            <td class="text-center">{{ $index + 1 }}</td>
                                            <td colspan="3" class="text-muted italic text-center">Data Invoice Sudah Tidak Tersedia (Terhapus)</td>
                                        </tr>
                                    @endif
                                @endforeach
                            </tbody>
                            <tfoot class="table-light fw-bold">
                                <tr>
                                    <td colspan="3" class="text-end">Total Akumulasi Sisa Tagihan:</td>
                                    <td class="text-end text-danger h6 mb-0 fw-bold">Rp {{ number_format($grandTotalSisa, 0, ',', '.') }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
