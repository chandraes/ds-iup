@extends('layouts.app')
@section('content')

<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>CHECK INVOICE JATUH TEMPO</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>

                </tr>
            </table>
        </div>
    </div>
</div>
<div class="container table-responsive mt-2">
    <div class="row">
        <div class="col-md-9">
            <select name="konsumen_id" id="konsumen_id" class="form-select">
                <option value="" disabled>-- Pilih Konsumen --</option>
                @foreach ($konsumen as $data)
                <option value="{{ $data->id }}">{{$data->kode_toko ? $data->kode_toko->kode.' ' : ''}}{{ $data->nama }}
                </option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button class="btn btn-primary" id="btnCari" onclick="checkInvoice()"><i class="fa fa-up"></i> Periksa
                Invoice</button>
        </div>
    </div>
    <div class="row mt-3">
        <div class="col-md-12">
            <div id="resultDiv"></div>
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<meta name="csrf-token" content="{{ csrf_token() }}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#rekapTable').DataTable({
            "paging": false,
            "ordering": true,
            "scrollCollapse": true,
            "scrollY": "60vh", // Set scrollY to 50% of the viewport height
            "scrollCollapse": true,
            "scrollX": true,

        });

        $('#konsumen_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
        });

    });

    function checkInvoice()
    {
        var konsumenId = $('#konsumen_id').val();
        if (!konsumenId) {
            alert('Silakan pilih konsumen terlebih dahulu.');
            return;
        }

        $.ajax({
            url: "{{ route('sales.check-konsumen.invoice') }}",
            type: 'POST',
            data: {
                _token: $('meta[name="csrf-token"]').attr('content'),
                konsumen_id: konsumenId
            },
            success: function(response) {
                if (response.success === true) {
                    // Redirect to the invoice page
                   console.log(response);
                   if (response.total == 0) {
                        $('#resultDiv').html('<div class="alert alert-success">Tidak ada invoice jatuh tempo untuk konsumen ini. Silahkan Melanjutkan Transaksi</div>');
                    } else {
                        var html = '<table class="table table-bordered table-striped" id="rekapTable">' +
                            '<thead>' +
                            '<tr>' +
                            '<th>No</th>' +
                            '<th>Invoice</th>' +
                            '<th>Tanggal Jatuh Tempo</th>' +
                            '<th>Sisa Tagihan</th>' +
                            '</tr>' +
                            '</thead>' +
                            '<tbody>';

                        $.each(response.data, function(index, invoice) {
                            html += '<tr>' +
                                '<td class="text-center">' + (index + 1) + '</td>' +
                                '<td>' + invoice.kode + '</td>' +
                                '<td>' + invoice.id_jatuh_tempo + '</td>' +
                                '<td data-order="'+ invoice.sisa_tagihan +'">Rp. ' + invoice.sisa_tagihan.toLocaleString() + '</td>' +
                                '</tr>';
                        });

                        html += '</tbody></table>';
                        $('#resultDiv').html(html);
                        $('#rekapTable').DataTable({
                            "paging": false,
                            "ordering": true,
                            "scrollCollapse": true,
                            "scrollY": "60vh",
                            "scrollX": true,
                            "searching": false,
                            "info": false
                        });
                   }

                } else {
                    alert(response.message);
                }
            },
            error: function(xhr, status, error) {
                console.error(error);
                alert('Terjadi kesalahan saat memeriksa invoice.');
            }
        });
    }


</script>
@endpush
