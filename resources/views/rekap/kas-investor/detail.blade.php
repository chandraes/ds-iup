@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>HISTORY INVESTOR</u></h1>
            <h2>{{$investor->nama}}</h2>
        </div>
    </div>
    {{-- if has any error --}}
    @if ($errors->any())
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <strong>Whoops!</strong> Ada kesalahan dalam input data:
                <ul>
                    @foreach ($errors->all() as $error)
                    <li>{{$error}}</li>
                    @endforeach
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </ul>
            </div>
        </div>
    </div>
    @endif
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('rekap')}}"><img src="{{asset('images/rekap.svg')}}" alt="dokumen" width="30">
                            REKAP</a></td>
                    <td><a href="{{route('rekap.kas-investor')}}"><img src="{{asset('images/kas-investor.svg')}}"
                                alt="dokumen" width="30"> REKAP Investor</a></td>


                </tr>
            </table>
        </div>
    </div>
    <div class="row mt-3">
        <table class="table table-bordered table-hover" id="data-table">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle">Tanggal</th>
                    <th class="text-center align-middle">Uraian</th>
                    <th class="text-center align-middle">Nominal</th>
                </tr>
            </thead>
            <tbody>

            </tbody>
            <tfoot>
                <tr>
                    <th colspan="2">Grand Total:</th> <!-- Label for the total -->
                    <th id="total-nominal"></th> <!-- Cell where the total will be displayed -->

                </tr>
            </tfoot>
        </table>
    </div>

</div>
@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
@endpush
@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>
    $(document).ready(function() {
        $('#data-table').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: '{{ route('rekap.kas-investor.detail', $investor->id) }}',
            columns: [
                { data: 'tanggal', name: 'tanggal', class: 'text-center'},
                { data: 'uraian', name: 'uraian' },
                {
                    data: 'nominal',
                    name: 'nominal',
                    class: 'text-end',
                    render: function(data, type, row) {
                        // Use Intl.NumberFormat to format the number in Indonesian format
                        return new Intl.NumberFormat('id-ID').format(data);
                    }
                },
                // Add more columns as needed
            ],
            footerCallback: function(row, data, start, end, display) {
                var api = this.api();
                var total = api.column(2).data().reduce(function (a, b) {
                    return parseInt(a) + parseInt(b);
                }, 0);
                $('#total-nominal').html(new Intl.NumberFormat('id-ID').format(total));
            }
        });


    });

</script>
@endpush
