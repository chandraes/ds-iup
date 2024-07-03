@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Beli Packaging</u></h1>
            <h1><u>TEMPO</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-3 mb-3">
        <div class="col-5">
            <table>
                <tr>
                    <td>
                        <a href="#" class="btn btn-success {{$keranjang->count() == 0 ? 'disabled' : ''}}"" data-bs-toggle="modal" data-bs-target="#keranjangBelanja" >
                            <i class="fa fa-shopping-cart"> Keranjang </i> ({{$keranjang->count()}})
                        </a>
                        @include('billing.form-transaksi.packaging.tempo.keranjang')
                    </td>
                    <td>
                        <form action="{{route('billing.form-transaksi.packaging.keranjang-tempo.empty')}}" method="post" id="kosongKeranjang">
                            @csrf
                            <button class="btn btn-danger" type="submit" {{$keranjang->count() == 0 ? 'disabled' : ''}}">
                                <i class="fa fa-trash"> Kosongkan Keranjang </i>
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.form-transaksi.packaging.keranjang-tempo.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-3">
                <div class="mb-3">
                    <label for="packaging_id" class="form-label">Packaging</label>
                    <select class="form-select" id="packaging_id" name="packaging_id" required>
                        <option value=""> -- Pilih Packaging -- </option>
                        @foreach ($kategori as $k)
                            <option value="{{$k->id}}">{{$k->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="mb-3">
                  <label for="jumlah" class="form-label">Jumlah</label>
                  <input type="number"
                    class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId" placeholder="" required>
                </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="harga" class="form-label">Harga Satuan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga'))
                    is-invalid
                @endif" name="harga" id="harga" data-thousands="." required>
                  </div>
            </div>
            <div class="col-md-3 mb-3">
                <label for="add_fee" class="form-label">Additional Fee</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('add_fee'))
                    is-invalid
                @endif" name="add_fee" id="add_fee" data-thousands="." required value="0">
                  </div>
            </div>
        </div>
        <hr>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Masukan Keranjang</button>
            <a href="{{route('billing.form-transaksi')}}" class="btn btn-secondary" type="button">Batal</a>
          </div>
    </form>
</div>
@endsection
@push('js')
    <script>
        function submitBeli(){
            Swal.fire({
                title: "Apakah Anda Yakin?" ,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'OK'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    document.getElementById('beliBarang').submit();
                }
            })
        }

        function check_sisa(){
            var grand_total = document.getElementById('grand_total').textContent;
            grand_total = parseInt(grand_total.replace(/\./g, ''), 10);
            var dp = document.getElementById('dpTd').textContent;
            dp = parseInt(dp.replace(/\./g, ''), 10);
            var dpPPNtd = document.getElementById('dpPPNtd').textContent;
            dpPPNtd = parseInt(dpPPNtd.replace(/\./g, ''), 10);

            var sisa = grand_total - dp - dpPPNtd;
            var sisaF = sisa.toLocaleString('id-ID');

            var tdPPN = document.getElementById('tdPpn').textContent;
            tdPPN = parseInt(tdPPN.replace(/\./g, ''), 10);

            var sisaPPN = tdPPN - dpPPNtd;

            document.getElementById('sisa').textContent = sisaF;
            document.getElementById('sisaPPN').textContent = sisaPPN.toLocaleString('id-ID');

            var totalDp = dp + dpPPNtd;
            document.getElementById('totalDpTd').textContent = totalDp.toLocaleString('id-ID');

        }

        function add_dp_ppn(){
            var apa_dp_ppn = document.getElementById('dp_ppn').value;
            if(apa_dp_ppn === '1')
            {
                var dp_ppn = document.getElementById('dp').value;
                var dp_ppn = dp_ppn.replace(/\./g, '');
                var ppn = {!! $ppn !!} / 100;

                var ppn_dp_num = dp_ppn * ppn;

                ppn_dp = ppn_dp_num.toLocaleString('id-ID');

                document.getElementById('dpPPNtd').textContent = ppn_dp;

                var ppn_total = document.getElementById('tdPpn').textContent;
                ppn_total = ppn_total.replace(/\./g, '');

                var sisa_ppn = ppn_total - ppn_dp_num;

                var sisa_ppnF = sisa_ppn.toLocaleString('id-ID');

                document.getElementById('sisaPPN').textContent = sisa_ppnF;


            } else {
                document.getElementById('dpPPNtd').textContent = 0;
                document.getElementById('sisaPPN').textContent = 0;

            }

            check_sisa();
        }

        function add_dp(){
            // get value from dp
            var dpT = document.getElementById('dp').value;
            var dp = dpT.replace(/\./g, '');

            // get element value tdTotal
            document.getElementById('dpTd').textContent = dpT;
            add_dp_ppn();
            check_sisa();
            // set value to dpTd
            // var dpTable = Number(dp).toLocaleString('id-ID');

        }

    function add_diskon() {
        // get value from tdDiskon
        var diskonT = document.getElementById('diskon').value;
        var diskon = diskonT.replace(/\./g, '');

        // get element value tdTotal
        var total = document.getElementById('tdTotal').textContent;
        total = total.replace(/\./g, '');

        var ppn = document.getElementById('tdPpn').textContent;
        ppn = ppn.replace(/\./g, '');

        var total_diskon = total - diskon;

        var gd = total_diskon + Number(ppn);

        var diskonFormatted = Number(diskon).toLocaleString('id-ID');
        var totalFormatted = total_diskon.toLocaleString('id-ID');
        var gF = gd.toLocaleString('id-ID');

        document.getElementById('tdDiskon').textContent = diskonT;
        document.getElementById('tdTotalSetelahDiskon').textContent = totalFormatted;
        document.getElementById('grand_total').textContent = gF;

        check_sisa();
    }

    function add_ppn(){
        var apa_ppn = document.getElementById('ppn').value;
        var ppn = {!! $ppn !!} / 100;

        if (apa_ppn === "1") { // compare with string "1"
            var gt = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));

            var vPpn = gt * ppn;
            var totalap = gt + (gt *ppn);

            var tF = totalap.toLocaleString('id-ID');
            var vF = vPpn.toLocaleString('id-ID');
            document.getElementById('grand_total').textContent = tF;
            document.getElementById('tdPpn').textContent = vF;
            // unhide select option dp_ppn
            document.getElementById('dp_ppn').style.display = 'block';
        } else {
            document.getElementById('tdPpn').textContent = 0;
            document.getElementById('dp_ppn').value = 0;
            // make select option dp_ppn to hidden
            document.getElementById('dp_ppn').style.display = 'none';

            add_diskon();
        }

        check_sisa();
    }

        $(function() {
            var nominal = new Cleave('#harga', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
            var diskoTn = new Cleave('#diskon', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            var add_fee = new Cleave('#add_fee', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });
        });

        confirmAndSubmit('#kosongKeranjang', 'Apakah anda Yakin?');
        confirmAndSubmit('#beliBarang', 'Apakah anda Yakin?');

        $('.delete-form').submit(function(e){
            e.preventDefault();
            var formId = $(this).data('id');
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $(`#deleteForm${formId}`).unbind('submit').submit();
                    $('#spinner').show();
                }
            });
        });

        function getProduct()
        {
            var kategori_product_id = document.getElementById('kategori_product_id').value;
            console.log(kategori_product_id);
            $.ajax({
                url: "{{route('billing.form-transaksi.kemasan.get-product')}}",
                type: "GET",
                data: {
                    kategori_product_id: kategori_product_id
                },
                success: function(data){
                    if (data.status == 0) {
                        Swal.fire({
                            title: "Gagal",
                            text: data.message,
                            icon: "error",
                            button: "OK"
                        });
                        // make kategori_product_id to '' selected
                        $('#kategori_product_id').val('');



                    } else {
                        var data = data.data;
                        $('#product_id').empty();

                        $('#product_id').append('<option value=""> -- Pilih Jenis-- </option>');
                        $.each(data, function(index, value){
                            $('#product_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                        });
                    }

                }
            });
        }

        // funGetBarang

        function getKemasan() {
            var product_id = document.getElementById('product_id').value;
            $.ajax({
                url: "{{route('billing.form-transaksi.kemasan.get-kemasan')}}",
                type: "GET",
                data: {
                    product_id: product_id
                },
                success: function(data){

                    if (data.status == 0) {
                        Swal.fire({
                            title: "Gagal",
                            text: data.message,
                            icon: "error",
                            button: "OK"
                        });

                        $('#kemasan_id').empty();
                    } else {
                        var data = data.data;
                        $('#kemasan_id').empty();
                        $('#kemasan_id').append('<option value=""> -- Pilih Kemasan -- </option>');
                        $.each(data, function(index, value){
                            $('#kemasan_id').append('<option value="'+value.id+'">'+value.nama+' ('+value.satuan.nama+')</option>');
                        });
                    }
                }
            });
        }

    </script>
@endpush
