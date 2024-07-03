@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>{{$data->product->kategori->nama}} - {{$data->product->nama}}</u></h1>
            <h1><u>{{$data->kode_produksi}}</u></h1>
        </div>
    </div>
    {{-- if error has any --}}
    @include('swal')
    @if ($errors->any())
    <div class="alert alert-danger">
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{$error}}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <form action="{{route('billing.stok-bahan-jadi.produksi-ke.store', $data)}}" method="post" id="postForm">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <table class="table table-bordered" id="barangJadi" style="font-size: 11px">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center align-middle">
                                PRODUKSI KE
                            </th>
                            <th class="text-center align-middle">
                                JUMLAH<br>PRODUKSI KEMASAN
                            </th>
                            {{-- <th class="text-center align-middle">
                                JUMLAH<br>PACKAGING
                            </th> --}}
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($data->produksi_detail as $group)
                        <tr>
                            <td class="text-center align-middle">
                                {{$group->detail_ke}}
                                <input type="hidden" name="id[]" value="{{$group->id}}">
                            </td>
                            <td class="text-center align-middle px-5">
                                <input type="number" name="total_kemasan[]" value="{{$group->total_kemasan}}"
                                    class="form-control">
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                    <tfoot>
                        <tr>
                            <th class="text-end align-middle">Grand Total</th>
                            <th class="text-center align-middle" id="grandTotalFooter"></th>
                        </tr>
                        <tr>
                            <th class="text-end align-middle">Jumlah Packaging</th>
                            <th class="text-center align-middle" id="grandPackagingFooter"></th>
                        </tr>
                        <tr>
                            <th class="text-end align-middle">Sisa Kemasan</th>
                            <th class="text-center align-middle" id="sisaKemasanFooter"></th>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
        <input type="hidden" name="real_packaging" id="real_packaging">
        <div class="mt-3 px-3 row">
            <button type="submit" class="btn btn-block btn-primary">Simpan</button>
        </div>
        <div class="mt-2 px-3 row">
            <a href="{{route('billing.stok-bahan-jadi.rencana')}}" class="btn btn-block btn-secondary">Batalkan</a>
        </div>
    </form>
</div>
@endsection
@push('js')

<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    function calculateGrandTotal() {
        var packaging = {{$konversi}};

        var total_kemasan = document.getElementsByName('total_kemasan[]');
        var grandTotal = 0;
        for (var i = 0; i < total_kemasan.length; i++) {
            grandTotal += Number(total_kemasan[i].value);
        }
        document.getElementById('grandTotalFooter').innerText = grandTotal;

        var grandPackaging = Math.floor(grandTotal / packaging);
        var sisaKemasan = grandTotal % packaging;

        document.getElementById('grandPackagingFooter').innerText = grandPackaging;
        document.getElementById('real_packaging').value = grandPackaging;
        document.getElementById('sisaKemasanFooter').innerText = sisaKemasan;

    }

    // Call the function when the page loads
    window.onload = calculateGrandTotal;

    // Also call the function whenever any total_kemasan input changes
    var total_kemasan = document.getElementsByName('total_kemasan[]');
    for (var i = 0; i < total_kemasan.length; i++) {
        total_kemasan[i].addEventListener('input', calculateGrandTotal);
    }

    $('#postForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });
</script>
@endpush
