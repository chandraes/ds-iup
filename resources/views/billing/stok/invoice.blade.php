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
                <td class="text-center align-middle"><a href="#" onclick="printInvoice()"><i class="fa fa-print"></i> Cetak Invoice
                            </a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container-fluid mt-3 table-responsive">
    <center>
        <img id="invoiceImage" src="{{ $jpegUrl }}" alt="Invoice Image" width="50%">
        <p>. <a href="{{ $jpegUrl }}" target="_blank">Klik di sini untuk mendownload Invoice</a>.</p>
        <!-- Tombol untuk mencetak gambar -->
    </center>
</div>
</div>
@endsection
@push('css')
{{--
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet"> --}}
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script>
      function printInvoice() {
        // Dapatkan elemen gambar
        var img = document.getElementById("invoiceImage");

        // Buka jendela baru
        var printWindow = window.open('', '_blank');

        // Tambahkan HTML yang berisi hanya gambar ke jendela baru
        printWindow.document.write('<html><head><title>Cetak Invoice</title>');
        printWindow.document.write('</head><body style="margin: 0; padding: 0; text-align: center;">');
        printWindow.document.write('<img src="' + img.src + '" style="width:100%; max-width:100%;">');
        printWindow.document.write('</body></html>');

        // Tutup dokumen untuk memastikan bahwa gambar telah dimuat
        printWindow.document.close();

        // Tunggu hingga konten siap, lalu cetak
        printWindow.onload = function() {
            printWindow.focus();
            printWindow.print();
            printWindow.close();
        };
    }
</script>
@endpush
