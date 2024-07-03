@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Produksi</u></h1>
        </div>
    </div>
    <form action="{{route('billing.produksi.store')}}" method="post">
        @csrf
        <div class="row mt-3">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="product_id" class="form-label">Product</label>
                    <select class="form-select" name="product_id" id="product_id" required onchange="kemasan()">
                        <option value="" disabled selected>-- Pilih Product --</option>
                        @foreach ($data as $d)
                        <option value="{{$d->id}}">{{$d->kategori->nama}} - {{$d->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="kemasan_id" class="form-label">Kemasan</label>
                    <select class="form-select" name="kemasan_id" id="kemasan_id" required onchange="checkInput()">
                        <option value="" disabled selected>-- Pilih Kemasan --</option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="rencana_produksi" class="form-label">Rencana Produksi (Kemasan)</label>
                    <input type="text" name="rencana_produksi" id="rencana_produksi" class="form-control" required
                        oninput="checkInput()">
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="rencana_produksi" class="form-label">&nbsp;</label>
                    <button type="button" id="tampilkanButton" class="btn btn-rounded btn-success form-control"
                        onclick="komposisi()" disabled>Tampilkan</button>
                </div>
            </div>
        </div>
        <div class="row mt-3" id="komposisiDiv" style="display: none;">
            <div class="col-md-12">
                <table class="table table-bordered">
                    <thead class="table-primary">
                        <tr>
                            <th class="text-center align-middle">NO</th>
                            <th class="text-center align-middle">BAHAN BAKU</th>
                            <th class="text-center align-middle">KADAR(%)</th>
                            <th class="text-center align-middle">Jumlah<br>Bahan</th>
                            <th class="text-center align-middle">Jumlah<br>Stok</th>
                        </tr>
                    </thead>
                    <tbody id="komposisiBody">
                        <tr>
                            <td colspan="6" class="text-center">Data Kosong</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <div class="col-md-4 mt-2">
                <input type="hidden" name="packaging_id" readonly id="packaging_id">
                <label for="packaging" class="form-label">Packaging</label>
                <input type="text" class="form-control" name="packaging" id="packaging" readonly>
            </div>
            <div class="col-md-4 mt-2">
                <label for="total_packaging" class="form-label">Total Packaging</label>
                <input type="text" class="form-control" name="total_pack" id="total_packaging" disabled>
            </div>
            <div class="col-md-4 mt-2">
                <label for="stok_packaging" class="form-label">Stock Packaging</label>
                <input type="text" class="form-control" name="stok_pack" id="stok_packaging" disabled>
            </div>
            <div class="col-md-4 mt-2">
                <label for="expired_dalam" class="form-label">Expired Dalam</label>
                <div class="input-group">
                    <input type="number" class="form-control" name="expired_dalam" id="expired_dalam" required>
                    <span class="input-group-text" id="basic-addon1">Bulan</span>
                </div>
            </div>
            <button type="submit" class="btn btn-block btn-success mt-3">Simpan</button>
        </div>
        <div class="row mt-2">

            <a href="{{route('billing')}}" class="btn btn-block btn-secondary">Kembali</a>
        </div>
    </form>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script>
    function checkInput() {
        const rencanaProduksi = document.getElementById('rencana_produksi').value;
        const tampilkanButton = document.getElementById('tampilkanButton');
        const kemasanId = document.getElementById('kemasan_id').value;

        if (rencanaProduksi === '' || kemasanId === '') {
            tampilkanButton.disabled = true;
        } else {
            tampilkanButton.disabled = false;
        }
    }

    $(document).ready(function() {
        $('#product_id').select2({
            theme: 'bootstrap-5',});
        $('#kemasan_id').select2({
            theme: 'bootstrap-5',});
    });

    function kemasan() {
        var product_id = $('#product_id').val();
        $.ajax({
            url: "{{route('billing.produksi.get-kemasan')}}",
            type: "GET",
            data: {
                product_id: product_id
            },
            success: function (data) {
                if(data.status == '1') {
                    $('#kemasan_id').empty();
                    $('#kemasan_id').append('<option value="" disabled selected>-- Pilih Kemasan --</option>');
                    $.each(data.data, function (key, value) {
                        $('#kemasan_id').append('<option value="'+value.id+'" required>'+value.nama+'</option>');
                    });
                } else {
                    $('#product_id').val('');
                    $('#kemasan_id').val('');
                    $('#rencana_produksi').val('');
                    checkInput();
                    Swal.fire({
                        title: data.message ,
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#spinner').show();
                            window.location.reload();
                        }
                    })

                }
            }
        });
    }

    function komposisi()
    {
        var product_id = $('#product_id').val();
        var kemasan_id = $('#kemasan_id').val();

        $.ajax({
            url: "{{route('billing.produksi.get-komposisi')}}",
            type: "GET",
            data: {
                product_id: product_id,
                kemasan_id: kemasan_id
            },
            success: function (data) {
                if(data.status == '1') {
                    console.log(data.data);
                    displayData(data.data, data.kemasan);
                } else {
                    $('#product_id').val('');
                    Swal.fire({
                        title: data.message ,
                        icon: 'warning',
                        showCancelButton: false,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'OK'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $('#spinner').show();
                            window.location.reload();
                        }
                    })
                }
            }
        });
    }

    function displayData(data, kemasan)
    {
        console.log(kemasan);
        $('#komposisiBody').empty();
        $('#komposisiDiv').show();

        var rencanaProduksi = $('#rencana_produksi').val();
        var total = 0;

        var no = 1;
        $.each(data, function (key, value) {
            var totalBahan = ((((value.jumlah * rencanaProduksi) / 100) * kemasan.konversi_liter / value.product.konversi_liter).toFixed(2));
            var rowClass = '';
            if (value.bahan_baku.stock < totalBahan) {
                rowClass = 'table-danger';
            }
            $('#komposisiBody').append('<tr class="'+rowClass+'">'+
                '<td class="text-center align-middle">'+no+'</td>'+
                '<td class="text-start align-middle">'+value.bahan_baku.kategori.nama+' - '+value.bahan_baku.nama+'</td>'+
                '<td class="text-center align-middle">'+value.jumlah+'%</td>'+
                '<td class="text-center align-middle">'+parseFloat(totalBahan).toLocaleString('id-ID')+' '+value.bahan_baku.satuan.nama+'</td>'+
                '<td class="text-center align-middle">'+parseFloat(value.bahan_baku.stock).toLocaleString('id-ID')+' '+value.bahan_baku.satuan.nama+'</td>'+
            '</tr>');
            total += totalBahan;
            no++;
        });

        if(kemasan.packaging_id) {
            let totalPackaging = rencanaProduksi / kemasan.packaging.konversi_kemasan;
            let finalPackaging = Math.floor(totalPackaging);
            $('#packaging').val(kemasan.packaging.nama);
            $('#packaging_id').val(kemasan.packaging.id);
            $('#total_packaging').val(finalPackaging);
            $('#stok_packaging').val(kemasan.packaging.stok);
            if (finalPackaging > kemasan.packaging.stok) {
                $('#total_packaging').addClass('is-invalid');
                $('#stok_packaging').addClass('is-invalid');
            }
        } else {
            $('#packaging_id').val('');
            $('#packaging').val('-');
            $('#total_packaging').val(0);
            $('#stok_packaging').val(0);
        }

    }

</script>
@endpush
