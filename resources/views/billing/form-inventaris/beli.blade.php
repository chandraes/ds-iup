@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Inventaris</u></h1>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.form-inventaris.beli.store')}}" method="post" id="masukForm">
        @csrf
        <div class="row">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="pembayaran" class="form-label">Jenis Pembayaran</label>
                    <select class="form-select" id="pembayaran" name="pembayaran" required onchange="checkPembayaran()">
                        <option value="" selected disabled> -- Pilih Pembayaran -- </option>
                        <option value="1">Cash</option>
                        <option value="2">Tempo</option>
                        <option value="3">Kredit</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row" id="row-first">
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="kategori_id" class="form-label">Kategori</label>
                    <select class="form-select" id="kategori_id" name="kategori_id" required onchange="getJenis()">
                        <option value="" selected disabled> -- Pilih Kategori -- </option>
                        @foreach ($data as $k)
                        <option value="{{$k->id}}">{{$k->nama}}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="inventaris_jenis_id" class="form-label">Jenis</label>
                    <select class="form-select" id="inventaris_jenis_id" name="inventaris_jenis_id" required>
                        <option value=""> -- Pilih Jenis -- </option>
                    </select>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="uraian" class="form-label">Apakah menggunakan PPn?</label>
                    <select class="form-select" name="apa_ppn" id="apa_ppn" onchange="add_ppn()" required>
                        <option value="">-- Pilih Salah Satu --</option>
                        <option value="1">Dengan PPn</option>
                        <option value="0">Tanpa PPn</option>
                    </select>
                </div>
            </div>
        </div>
        <div class="row" id="row-second">
            <div class="col-md-12">
                <div class="mb-3">
                    <label for="uraian" class="form-label">Uraian</label>
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" name="uraian" id="uraian" aria-describedby="helpId"
                            placeholder="" required>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="mb-3">
                    <label for="jumlah" class="form-label">Jumlah</label>
                    <div class="input-group mb-3">
                        <input type="number" class="form-control" name="jumlah" id="jumlah" aria-describedby="helpId"
                            placeholder="" required value="0" onkeyup="calculateTotal()">
                        <span class="input-group-text" id="basic-addon1">Buah</span>
                    </div>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="harga_satuan" class="form-label">Harga Satuan</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('harga_satuan'))
                    is-invalid
                @endif" name="harga_satuan" id="harga_satuan" data-thousands="." required value="0" onkeyup="calculateTotal()">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="ppn" class="form-label">PPN</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('ppn'))
                    is-invalid
                @endif" name="ppn" id="ppn" required readonly value="0">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="add_fee" class="form-label">Additional Fee</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('add_fee'))
                    is-invalid
                @endif" name="add_fee" id="add_fee" required value="0" onkeyup="calculateTotal()">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="diskon" class="form-label">Diskon</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('diskon'))
                    is-invalid
                @endif" name="diskon" id="diskon" required value="0" onkeyup="calculateTotal()">
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="total" class="form-label">Total Harga</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('total'))
                    is-invalid
                @endif" name="total" id="total" required readonly value="0">
                </div>
            </div>
        </div>
        <div class="row" id="row-dp">
            <div class="col-md-4 mb-3">
                <label for="dp" class="form-label">DP</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1">Rp</span>
                    <input type="text" class="form-control @if ($errors->has('dp'))
                    is-invalid
                @endif" name="dp" id="dp" data-thousands="." value="0" disabled>
                </div>
            </div>
            <div class="col-md-4 mb-3">
                <label for="tanggal_jatuh_tempo" class="form-label">Tanggal Jatuh Tempo</label>
                <div class="input-group mb-3">
                    <span class="input-group-text" id="basic-addon1"><i class="fa fa-calendar"></i></span>
                    <input type="text" class="form-control @if ($errors->has('dp'))
                    is-invalid
                @endif" name="tanggal_jatuh_tempo" id="tanggal_jatuh_tempo" disabled >
                </div>
            </div>
        </div>
        <div class="row">
            <hr>
            <h3>Transfer Ke : </h3>
            <hr>
            <div class="col-md-4 mb-3">
                <label for="nama_rek" class="form-label">Nama Rekening</label>
                <input type="text" class="form-control @if ($errors->has('nama_rek'))
                    is-invalid
                @endif" name="nama_rek" id="nama_rek" required maxlength="15">
                @if ($errors->has('nama_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('nama_rek')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="bank" class="form-label">Bank</label>
                <input type="text" class="form-control @if ($errors->has('bank'))
                    is-invalid
                @endif" name="bank" id="bank" required maxlength="10">
                @if ($errors->has('bank'))
                <div class="invalid-feedback">
                    {{$errors->first('bank')}}
                </div>
                @endif
            </div>
            <div class="col-md-4 mb-3">
                <label for="no_rek" class="form-label">Nomor Rekening</label>
                <input type="text" class="form-control @if ($errors->has('no_rek'))
                    is-invalid
                @endif" name="no_rek" id="no_rek" required>
                @if ($errors->has('no_rek'))
                <div class="invalid-feedback">
                    {{$errors->first('no_rek')}}
                </div>
                @endif
            </div>
        </div>
        <hr>

        <div class="d-grid gap-3 mt-3">
            <button class="btn btn-primary">Beli</button>
            <a href="{{route('billing.form-inventaris')}}" class="btn btn-secondary" type="button">Batal</a>
        </div>
    </form>
</div>
@endsection
@push('js')
<script>
      $(function() {
            var nominal = new Cleave('#harga_satuan', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            var dp = new Cleave('#dp', {
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

            var diskon = new Cleave('#diskon', {
                numeral: true,
                numeralThousandsGroupStyle: 'thousand',
                numeralDecimalMark: ',',
                delimiter: '.'
            });

            var no_rek = new Cleave('#no_rek', {
                delimiter: '-',
                blocks: [4, 4, 8]
            });

            // flatpickr id tanggal_jatuh_tempo
            flatpickr('#tanggal_jatuh_tempo', {
                enableTime: false,
                dateFormat: "d-m-Y",
            });

        });

        function checkPembayaran(){
            console.log('checkPembayaran');
            var pembayaran = document.getElementById('pembayaran').value;
            if(pembayaran == 1)
            {
                dp = document.getElementById('dp');
                dp.setAttribute('disabled', 'disabled');
                // required false
                dp.removeAttribute('required');
                tanggal_jatuh_tempo = document.getElementById('tanggal_jatuh_tempo');
                tanggal_jatuh_tempo.setAttribute('disabled', 'disabled');
                // required false
                tanggal_jatuh_tempo.removeAttribute('required');
                dp.value = 0;
                tanggal_jatuh_tempo.value = '';
            } else if(pembayaran == 2){
                dp = document.getElementById('dp');
                dp.removeAttribute('disabled');
                // required true
                dp.setAttribute('required', 'required');
                tanggal_jatuh_tempo = document.getElementById('tanggal_jatuh_tempo');
                tanggal_jatuh_tempo.removeAttribute('disabled');
                // required true
                tanggal_jatuh_tempo.setAttribute('required', 'required');
            }
        }


        function getJenis(){
            console.log('getJenis');
            var kategori_id = document.getElementById('kategori_id').value;
            $.ajax({
                url: "{{route('billing.form-inventaris.get-jenis')}}",
                type: "GET",
                data: {
                    kategori_id: kategori_id
                },
                success: function(data){
                    if(data.status === 0 ){
                        Swal.fire({
                            icon: 'error',
                            title: 'Oops...',
                            text: 'Data Jenis Inventaris Kosong!',
                        });
                        document.getElementById('kategori_id').value = '';
                        document.getElementById('apa_ppn').value = '';
                    }
                    var html = '';
                    html += '<option value=""> -- Pilih Jenis -- </option>';
                    data.data.forEach(function(item){
                        html += '<option value="'+item.id+'">'+item.nama+'</option>';
                    });
                    document.getElementById('inventaris_jenis_id').innerHTML = html;
                }
            });
        }

    function calculateTotal(){
        var harga = parseInt(document.getElementById('harga_satuan').value.replace(/\./g, ''), 10);
        var jumlah = parseInt(document.getElementById('jumlah').value, 10);
        var add_fee = parseInt(document.getElementById('add_fee').value.replace(/\./g, ''), 10);
        var diskon = parseInt(document.getElementById('diskon').value.replace(/\./g, ''), 10);

        var apa_ppn = document.getElementById('apa_ppn').value;
        var ppn = 0;
        var tot = harga * jumlah;

        if (apa_ppn === "1") {
            var ppnRate = {!! $ppn !!};
            ppn = tot * (ppnRate / 100);
        }

        var total = tot + ppn - diskon + add_fee;
        document.getElementById('ppn').value = ppn.toLocaleString('id-ID');
        document.getElementById('total').value = total.toLocaleString('id-ID');
    }

        confirmAndSubmit('#masukForm', 'Apakah anda Yakin?');

</script>
@endpush
