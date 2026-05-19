<div class="row px-3">
    @if (file_exists(public_path('storage/invoices/invoice-'.$row->id.'.pdf')))
        <a href="{{asset('storage/invoices/invoice-'.$row->id.'.pdf')}}" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Invoice</a>
    @else
        <a href="{{route('billing.form-jual.invoice', ['invoice' => $row->id])}}" target="_blank" class="btn btn-primary btn-sm"><i class="fa fa-file me-1"></i> Invoice</a>
    @endif
</div>

<form action="{{route('billing.invoice-konsumen.bayar', ['invoice' => $row->id])}}" method="post" id="bayarForm{{ $row->id }}" class="bayar-form" data-id="{{ $row->id }}" data-nominal="{{$row->nf_sisa_tagihan}}">
    @csrf
    <div class="row p-3">
        <button type="button" class="btn btn-sm btn-success btn-submit-bayar"><i class="fa fa-credit-card me-1"></i> Bayar</button>
    </div>
</form>

@if (auth()->user()->role == 'admin' || auth()->user()->role == 'su')
<form action="{{route('billing.invoice-konsumen.void', ['invoice' => $row->id])}}" method="post" id="voidForm{{ $row->id }}" class="void-form" data-id="{{ $row->id }}">
    @csrf
    <div class="row p-3">
        <button type="button" class="btn btn-sm btn-danger btn-submit-void"><i class="fa fa-exclamation-circle me-1"></i> Void</button>
    </div>
</form>
@endif

{{-- Sertakan file modal histori cicilan jika dibutuhkan di sini --}}
@include('billing.invoice-konsumen.histori-cicil', ['d' => $row])
