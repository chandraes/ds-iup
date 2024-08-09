@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('swal')
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{route('home')}}"><img
                                src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-3 table-responsive">
    <object data="{{ $pdfUrl }}" type="application/pdf" width="100%" height="600px">
        <p>Browser Anda tidak mendukung tampilan PDF. <a href="{{  $pdfUrl }}">Klik di sini untuk mendownload PDF</a>.</p>
    </object>
</div>
@endsection
@push('css')
{{--
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet"> --}}
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')

@endpush
