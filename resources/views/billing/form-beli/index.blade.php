@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Beli {{$jenis == 1 ? 'PPN' : 'NON PPN'}}</u></h1>
            <h1><u>{{$req['tempo'] == 0 ? 'CASH' : 'TEMPO'}}</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-3 mb-3">
        <div class="col-5">
            <table>
                <tr>
                    <td>
                        <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#keranjangBelanja" {{$keranjang->count() == 0 ? 'disabled' : ''}}>
                            <i class="fa fa-shopping-cart"> Keranjang </i> ({{$keranjang->count()}})
                        </button>
                        @include('billing.form-beli.keranjang')
                    </td>
                    <td>
                        <form action="{{route('billing.form-beli.keranjang.empty')}}" method="post" id="kosongKeranjang">
                            @csrf
                            <input type="hidden" name="jenis" value="{{$jenis}}">
                            <input type="hidden" name="tempo" value="{{$req['tempo']}}">
                            <button class="btn btn-danger" type="submit" {{$keranjang->count() == 0 ? 'disabled' : ''}}>
                                <i class="fa fa-trash"> Kosongkan Keranjang </i>
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.form-beli.keranjang.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="barang_unit_id" class="form-label">Unit</label>
                    <select class="form-select" name="barang_unit_id" id="barang_unit_id" onchange="funGetBarang()" required>
                        <option value=""> -- Pilih Unit -- </option>
                        @foreach ($data as $k)
                            <option value="{{$k->id}}">{{$k->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="barang_type_id" class="form-label">Type</label>
                    <select class="form-select" name="barang_type_id" id="barang_type_id" required onchange="getKategori()">
                        <option value=""> -- Pilih Type -- </option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="barang_kategori_id" class="form-label">Kelompok Barang</label>
                    <select class="form-select" name="barang_kategori_id" id="barang_kategori_id" required onchange="getBarang()">
                        <option value=""> -- Pilih Kelompok Barang -- </option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="barang_nama_id" class="form-label">Nama Barang</label>
                    <select class="form-select" name="barang_nama_id" id="barang_nama_id" required onchange="getMerk()">
                        <option value=""> -- Pilih Barang -- </option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="barang_id" class="form-label">Merk</label>
                    <select class="form-select" name="barang_id" id="barang_id" required onchange="getKode()">
                        <option value=""> -- Pilih Merk -- </option>
                    </select>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="kode" class="form-label">Kode</label>
                    <input type="text" class="form-control" name="kode" id="kode" disabled>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                    <label for="satuan" class="form-label">Satuan</label>
                    <input type="text" class="form-control" name="satuan" id="satuan" disabled>
                </div>
            </div>
            <div class="col-md-2">
                <div class="mb-3">
                  <label for="jumlah" class="form-label">Jumlah</label>
                  <input type="text"
                    class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId" placeholder="" required>
                </div>
            </div>
            <div class="col-md-2 mb-3">
                <label for="harga" class="form-label">Harga Satuan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga'))
                    is-invalid
                @endif" name="harga" id="harga" data-thousands="." required>
                  </div>
            </div>
        </div>
        <input type="hidden" name="tempo" value="{{$req['tempo']}}">
        <input type="hidden" name="jenis" value="{{$jenis}}">
        <hr>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Masukan Keranjang</button>
            <a href="{{route('billing')}}" class="btn btn-secondary" type="button">Batal</a>
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

        function add_diskon() {
            // Existing code to calculate discount and total after discount
            var diskonT = document.getElementById('diskon').value ?? 0;
            var diskon = diskonT.replace(/\./g, '');
            var total = document.getElementById('tdTotal').textContent;
            total = total.replace(/\./g, '');
            var apa_ppn = {{$req['kas_ppn']}};

            if (apa_ppn == 1) {
                var ppn = document.getElementById('tdPpn').textContent;
                ppn = ppn.replace(/\./g, '');
            }

            var addFeeT = document.getElementById('add_fee').value;
            var addFee = addFeeT.replace(/\./g, '');
            var total_diskon = total - diskon;
            var gd = total_diskon + Number(ppn) + Number(addFee);
            var diskonFormatted = Number(diskon).toLocaleString('id-ID');
            var totalFormatted = total_diskon.toLocaleString('id-ID');
            var addFeeFormatted = Number(addFee).toLocaleString('id-ID');
            var gF = gd.toLocaleString('id-ID');
            document.getElementById('tdDiskon').textContent = diskonT;
            document.getElementById('tdTotalSetelahDiskon').textContent = totalFormatted;
            document.getElementById('tdAddFee').textContent = addFeeFormatted;
            document.getElementById('grand_total').textContent = gF;

            // Call add_ppn at the end to recalculate PPN based on the new total after discount
            add_ppn();
            check_sisa();
        }

        function add_ppn() {
            var apa_ppn = {{$req['kas_ppn'] == 1 ? 1 : 0}};
            var ppnRate = {!! $ppnRate !!} / 100;

            // Retrieve add_fee value and convert it to a number after removing any formatting
            var addFee = Number(document.getElementById('add_fee').value.replace(/\./g, ''));

            if (apa_ppn === 1) {

                var gt = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
                var vPpn = Math.round(gt * ppnRate);
                // vPpn = vPpn.toFixed(0);
                // Include add_fee in the total calculation
                var totalap = gt + vPpn + addFee;
                // totalap = totalap.toFixed(0);
                // console.log(totalap);
                var tF = totalap.toLocaleString('id-ID');
                var vF = vPpn.toLocaleString('id-ID');
                document.getElementById('grand_total').textContent = tF;
                document.getElementById('tdPpn').textContent = vF;
            } else {
                // Since PPN is not applied, directly update grand_total with tdTotalSetelahDiskon and add_fee
                var gtWithoutPpn = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
                var totalWithoutPpn = gtWithoutPpn + addFee;
                totalWithoutPpn = totalWithoutPpn.toFixed(0);
                var totalFormatted = totalWithoutPpn.toLocaleString('id-ID');
                document.getElementById('grand_total').textContent = totalFormatted;
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

        function add_dp_ppn(){
            var apa_dp_ppn = document.getElementById('dp_ppn') ? document.getElementById('dp_ppn').value || 0 : 0;
            if(apa_dp_ppn === '1')
            {
                var dp_ppn = document.getElementById('dp').value;
                var dp_ppn = dp_ppn.replace(/\./g, '');
                var ppn = {!! $ppnRate !!} / 100;

                var ppn_dp_num = dp_ppn * ppn;

                ppn_dp = ppn_dp_num.toLocaleString('id-ID');

                document.getElementById('dpPPNtd').textContent = ppn_dp;

                var ppn_total = document.getElementById('tdPpn').textContent;
                ppn_total = ppn_total.replace(/\./g, '');

                var sisa_ppn = ppn_total - ppn_dp_num;

                sisa_ppn = sisa_ppn.toFixed(0);

                var sisa_ppnF = sisa_ppn.toLocaleString('id-ID');

                document.getElementById('sisaPPN').textContent = sisa_ppnF;


            } else {
                if (document.getElementById('dpPPNtd')) {
                    document.getElementById('dpPPNtd').textContent = 0;
                }

                if (document.getElementById('sisaPPN')) {
                    document.getElementById('sisaPPN').textContent = 0;
                }
            }

            check_sisa();
        }

        function check_sisa(){
            var grand_total = document.getElementById('grand_total').textContent;
            grand_total = parseInt(grand_total.replace(/\./g, ''), 10);
            var dp = document.getElementById('dpTd').textContent;
            dp = parseInt(dp.replace(/\./g, ''), 10);
            var dpPPNtd = document.getElementById('dpPPNtd') ? document.getElementById('dpPPNtd').textContent : '0';
            dpPPNtd = parseInt(dpPPNtd.replace(/\./g, ''), 10);

            // Jika ingin memastikan dpPPNtd selalu berupa angka valid
            if (isNaN(dpPPNtd)) {
                dpPPNtd = 0;
            }

            var sisa = grand_total - dp - dpPPNtd;
            var sisaF = sisa.toLocaleString('id-ID');
            console.log(sisaF);
            var tdPPN = document.getElementById('tdPpn') ? document.getElementById('tdPpn').textContent : '0';
            tdPPN = parseInt(tdPPN.replace(/\./g, ''), 10);

            if (isNaN(tdPPN)) {
                tdPPN = 0;
            }

            var sisaPPN = tdPPN - dpPPNtd;
            console.log(sisaPPN);
            sisaPPN = sisaPPN.toFixed(0);
            document.getElementById('sisa').textContent = sisaF;

            if (sisaPPN == 0) {
                if (document.getElementById('sisaPPN'))
                document.getElementById('sisaPPN').textContent = 0;
            } else {
                if (document.getElementById('sisaPPN'))
                document.getElementById('sisaPPN').textContent = sisaPPN.toLocaleString('id-ID');

            }

            var totalDp = dp + dpPPNtd;
            totalDp = totalDp.toFixed(0);

            if (document.getElementById('totalDpTd'))
            document.getElementById('totalDpTd').textContent = totalDp.toLocaleString('id-ID');

        }

        $(function() {
            var nominal = new Cleave('#harga', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            var jumlah = new Cleave('#jumlah', {
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
                negative: true,
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

        // funGetBarang
        function funGetBarang() {
            var barang_unit_id = $('#barang_unit_id').val();

            $.ajax({
                url: "{{route('db.barang.get-type')}}",
                type: "GET",
                data: {
                    unit_id: barang_unit_id,
                },
                success: function(data){
                    if (data.status == 1) {
                        $('#barang_type_id').empty();
                        $('#barang_kategori_id').empty();
                        $('#barang_nama_id').empty();
                        $('#barang_id').empty();
                        $('#kode').val('');
                        $('#barang_type_id').append('<option value=""> -- Pilih Type -- </option>');
                        $.each(data.data, function(index, value){
                            $('#barang_type_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                        });
                    } else {
                        $('#barang_type_id').empty();
                        $('#barang_type_id').append('<option value=""> -- Pilih Type -- </option>');
                        $('#barang_id').empty();
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Unit belum memiliki Type!',
                        });
                    }
                }
            });
        }

        function getKategori() {
            var barang_type_id = $('#barang_type_id').val();

            $.ajax({
                url: "{{route('billing.form-beli.get-kategori')}}",
                type: "GET",
                data: {
                    barang_type_id: barang_type_id,
                },
                success: function(data){
                    if (data.status == 1) {
                        $('#barang_kategori_id').empty();
                        $('#barang_nama_id').empty();
                        $('#barang_id').empty();
                        $('#kode').val('');
                        $('#barang_kategori_id').append('<option value=""> -- Pilih Kelompok Barang -- </option>');
                        $.each(data.data, function(index, value){
                            $('#barang_kategori_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                        });
                    } else {
                        $('#barang_kategori_id').empty();
                        $('#barang_id').empty();
                        $('#barang_kategori_id').append('<option value=""> -- Pilih Kelompok Barang -- </option>');

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Type belum memiliki Kategori Barang!',
                        });
                    }
                }
            });
        }

        function getBarang() {
            var barang_type_id = $('#barang_type_id').val();
            var barang_kategori_id = $('#barang_kategori_id').val();

            $.ajax({
                url: "{{route('billing.form-beli.get-barang')}}",
                type: "GET",
                data: {
                    barang_type_id: barang_type_id,
                    barang_kategori_id: barang_kategori_id,
                    jenis: {{$jenis}}
                },
                success: function(data){
                    if (data.status == 1) {
                        console.log(data);
                        $('#barang_nama_id').empty();
                        $('#barang_nama_id').append('<option value=""> -- Pilih Nama Barang -- </option>');
                        $('#kode').val('');
                        $.each(data.data, function(index, value){
                            $('#barang_nama_id').append('<option value="'+value.id+'">'+value.nama+'</option>');
                        });
                    } else {
                        $('#barang_nama_id').empty();
                        $('#kode').val('');
                        $('#barang_nama_id').append('<option value=""> -- Pilih Nama Barang -- </option>');

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Type belum memiliki Barang Barang!',
                        });
                    }
                }
            });
        }

        function getMerk() {
            var jenis = {{$jenis}};
            var barang_nama_id = $('#barang_nama_id').val();
            var barang_type_id = $('#barang_type_id').val();
            var barang_kategori_id = $('#barang_kategori_id').val();

            $.ajax({
                url: "{{route('billing.form-beli.get-merk')}}",
                type: "GET",
                data: {
                    barang_nama_id: barang_nama_id,
                    barang_type_id: barang_type_id,
                    barang_kategori_id: barang_kategori_id,
                    jenis: {{$jenis}}
                },
                success: function(data){
                    console.log(data);
                    if (data.status == 1) {
                        $('#kode').val('');
                        $('#satuan').val('');
                        $('#barang_id').empty();
                        $('#barang_id').append('<option value=""> -- Pilih Merk -- </option>');
                        $.each(data.data, function(index, value){
                            $('#barang_id').append('<option value="'+value.id+'">'+value.merk+'</option>');
                        });
                    } else {
                        $('#satuan').val('');
                        $('#kode').val('');
                        $('#barang_id').empty();
                        $('#barang_id').append('<option value=""> -- Pilih Merk -- </option>');

                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Type belum memiliki Barang Barang!',
                        });
                    }
                }
            });
        }

        function getKode()
        {
            var barang_id = $('#barang_id').val();
            $.ajax({
                url: "{{route('billing.form-beli.get-kode')}}",
                type: "GET",
                data: {
                    barang_id: barang_id,
                },
                success: function(data){
                    if (data.status == 1) {
                        $('#kode').val(data.data.kode);
                        $('#satuan').val(data.data.satuan);
                    } else {
                        $('#kode').val('');
                        $('#satuan').val('');
                    }
                }
            });
        }

    </script>
@endpush
