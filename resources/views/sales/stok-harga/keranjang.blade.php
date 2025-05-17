@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>KERANJANG SALES ORDER</u></h1>
        </div>
    </div>
    <div class="row mb-3 d-flex">
        <div class="col-md-6">
            <a href="{{route('sales.stok')}}" class="btn btn-secondary"><i class="fa fa-arrow-left"></i>
                Kembali</a>
        </div>
    </div>
    <div class="row">
        <form action="{{route('sales.stok.keranjang.checkout')}}" method="post" id="storeForm">
            @csrf
            <div class="card">
                <div class="card-body bg-white">
                    <h4 class="card-title">
                        {{-- <strong>#INVOICE : {{$invoice}}</strong> --}}
                    </h4>
                    <div class="row mt-3 mb-3">
                        <div class="row mb-3">
                            <div class="col-md-12 my-3">
                                Pre Order:
                            </div>
                            <div class="col-md-5 pt-1">
                                {{-- select barang nama and input jumlah --}}
                                <select class="form-select" name="barang_id" id="barang_id" onchange="setSatuan()">
                                    <option value="" disabled selected>-- Pilih Barang --</option>
                                    @foreach ($barang as $bInden)
                                    <option value="{{$bInden->id}}" data-satuan="{{$bInden->satuan->nama}}">{{$bInden->barang_nama->nama}} ({{$bInden->kode}}) ({{$bInden->merk}})</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-md-5">
                                <div class="input-group mb-3">

                                    <input type="text" class="form-control" name="jumlah_inden" id="jumlah_inden" placeholder="Jumlah">
                                    <span class="input-group-text" id="satuan-inden">-</span>
                                  </div>
                            </div>
                            <div class="col-md-2">
                                <button type="button" class="btn btn-primary" onclick="addKeranjangInden()"><i class="fa fa-plus"></i>
                                    Tambah</button>
                            </div>
                        </div>
                        <div class="row">
                            <table class="table table-bordered table-hover">
                                <thead class="table-primary">
                                    <tr>
                                        <th class="text-center align-middle">No</th>
                                        <th class="text-center align-middle">Kelompok Barang</th>
                                        <th class="text-center align-middle">Nama Barang</th>
                                        <th class="text-center align-middle">Jumlah</th>
                                        <th class="text-center align-middle">Satuan</th>
                                        <th class="text-center align-middle">ACT</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($orderInden as $a)
                                    <tr>
                                        <td class="text-center align-middle">{{$loop->iteration}}</td>
                                        <td class="text-center align-middle">{{$a->barang->kategori->nama}}</td>
                                        <td class="text-center align-middle">{{$a->barang->barang_nama->nama}}</td>
                                         <td class="text-center align-middle">{{$a->nf_jumlah}}</td>
                                        <td class="text-center align-middle">{{$a->barang->satuan->nama}}</td>
                                        <td class="text-center align-middle">
                                            <button type="button" class="btn btn-danger btn-sm" onclick="indenDelete({{$a->id}})"><i
                                                    class="fa fa-trash"></i></button>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>

                            </table>
                        </div>

                        <hr>
                        <div class="col-md-12 my-3">
                            <div class="row" id="konsumenRow">
                                <div class="row invoice-info">
                                    <div class="col-md-6 invoice-col">
                                        <table style="width: 90%">
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Konsumen</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <select class="form-select" name="konsumen_id" id="konsumen_id" required
                                                        onchange="getKonsumenData()">
                                                        <option value="" selected>-- Pilih Konsumen --</option>
                                                        {{-- <option value="*">INPUT MANUAL</option> --}}
                                                        @foreach ($konsumen as $k)
                                                        <option value="{{$k->id}}">{{$k->kode_toko ? $k->kode_toko->kode.'.' : ''}} {{$k->nama}}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                            </tr>
                                            <tr id="namaTr" hidden style="height:50px">
                                                <td class="text-start align-middle">Nama</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="nama" id="nama"
                                                        class="form-control">
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Sistem Pembayaran</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <select type="text" name="pembayaran" id="pembayaran" onchange="checkPembayaran()"
                                                        class="form-select" required>

                                                    </select>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Tempo</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <div class="input-group">
                                                        <input type="text" class="form-control" name="tempo_hari"
                                                            id="tempo_hari" disabled>
                                                        <span class="input-group-text" id="basic-addon1">Hari</span>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">NPWP</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <input type="text" name="npwp" id="npwp" class="form-control"
                                                        disabled>
                                                </td>
                                            </tr>
                                            <tr style="height:50px">
                                                <td class="text-start align-middle">Alamat</td>
                                                <td class="text-start align-middle" style="width: 10%">:</td>
                                                <td class="text-start align-middle">
                                                    <textarea name="alamat" id="alamat" class="form-control"
                                                        disabled></textarea>
                                                </td>
                                            </tr>

                                        </table>
                                    </div>
                                    <!-- /.col -->
                                    <div class="col-md-6 invoice-col" >
                                        <div class="row d-flex justify-content-end">
                                            <table style="width: 90%">
                                                {{-- <tr style="height:50px">
                                                    <td class="text-start align-middle">Invoice</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="kode" id="kode" class="form-control"
                                                            disabled value="{{$kode}}">
                                                    </td>
                                                </tr> --}}
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Tanggal</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="tanggal" id="tanggal" class="form-control"
                                                            value="{{$tanggal}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">Jam</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="jam" id="jam" class="form-control"
                                                            value="{{$jam}}" disabled>
                                                    </td>
                                                </tr>
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">No WA</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <input type="text" name="no_hp" id="no_hp" class="form-control"
                                                            disabled>
                                                    </td>
                                                </tr>
                                                @if ($adaPpn == 1)
                                                <tr style="height:50px">
                                                    <td class="text-start align-middle">PPn Disetor Oleh</td>
                                                    <td class="text-start align-middle" style="width: 10%">:</td>
                                                    <td class="text-start align-middle">
                                                        <select class="form-select" name="dipungut" id="dipungut" required
                                                        onchange="ppnPungut()">
                                                            <option selected value="1">Sendiri</option>
                                                    </select>
                                                    </td>
                                                </tr>
                                                @endif
                                            </table>
                                        </div>

                                    </div>

                                </div>
                            </div>
                        </div>

                    </div>
                    <hr>
                    @if ($keranjang->where('barang_ppn' , 1)->count() > 0)
                    @include('sales.stok-harga.table-ppn')
                    @endif
                    <hr>
                    @if ($keranjang->where('barang_ppn' , 0)->count() > 0)
                    @include('sales.stok-harga.table-non-ppn')
                    @endif
                    <div class="row ">
                        <div class="col-md-6"></div>
                        <div class="col-md-6 text-end">
                            <button type="submit" class="btn btn-success"><i class="fa fa-credit-card"></i>
                                Lanjutkan</button>
                            {{-- <button type="button" class="btn btn-info text-white"
                                onclick="javascript:window.print();"><i class="fa fa-print"></i> Print Invoice</button>
                            --}}
                        </div>
                    </div>
                </div>
            </div>
        </form>
        <div class="col-md-6 text-end mt-2">
            @include('wa-status')
        </div>
    </div>
</div>
@endsection
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
{{-- <script src="{{asset('assets/js/cleave.min.js')}}"></script> --}}
<script src="{{asset('assets/plugins/select2/js/select2.min.js')}}"></script>
<script>

    var tempTempo = '-';

    $('#konsumen_id').select2({
        width: '100%',
    });

    $('#barang_id').select2({
        width: '100%',
    });

    // Format input angka dengan Cleave.js
    const cleaveOptions = {
        numeral: true,
        numeralThousandsGroupStyle: 'thousand',
        numeralDecimalMark: ',',
        delimiter: '.',
        negative: true
    };


    function setSatuan() {
        // Ambil elemen select dan span
        const selectElement = document.getElementById('barang_id');
        const satuanSpan = document.getElementById('satuan-inden');

        // Ambil opsi yang dipilih
        const selectedOption = selectElement.options[selectElement.selectedIndex];

        // Ambil data-satuan dari opsi yang dipilih
        const satuan = selectedOption.getAttribute('data-satuan');

        // Ganti innerText dari span dengan satuan
        satuanSpan.innerText = satuan || '-';
    }


    // Helper untuk menghapus format angka
    function parseNumber(value) {
        return parseFloat(value.replace(/\./g, '') || 0);
    }

    // Helper untuk memformat angka ke format Indonesia
    function formatNumber(value) {
        return value.toLocaleString('id-ID');
    }

    function ppnPungut() {
        var dipungut = document.getElementById('dipungut').value ?? 0;
        calculatePpn();
    }


    function checkPembayaran()
    {
        var pembayaran = document.getElementById('pembayaran').value;
         var tempo_hari = tempTempo;
        if (pembayaran == 2) {


            var elementTrJumlahDp = document.getElementById('trJumlahDp');
            var elementTrJumlahDpNonPpn = document.getElementById('trJumlahDp_non_ppn');
            var elementTrDp = document.getElementById('trDp');
            var elementTrDpNonPpn = document.getElementById('trDp_non_ppn');

            if (elementTrJumlahDp) {
                document.getElementById('trJumlahDp').hidden = false;
            }

            if (elementTrJumlahDpNonPpn) {
                document.getElementById('trJumlahDp_non_ppn').hidden = false;

            }

            if (elementTrDp) {
                document.getElementById('trDp').hidden = false;

            }

            if (elementTrDpNonPpn) {
                document.getElementById('trDp_non_ppn').hidden = false;

            }

            var elementDpCleave = document.getElementById('dp');
            var elementDpNonPpnCleave = document.getElementById('dp_non_ppn');

            if (elementDpCleave) {
                var dp = new Cleave('#dp', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.',
                    negative: false
                });
            }

            if (elementDpNonPpnCleave) {
                var dp_non_ppn = new Cleave('#dp_non_ppn', {
                    numeral: true,
                    numeralThousandsGroupStyle: 'thousand',
                    numeralDecimalMark: ',',
                    delimiter: '.',
                    negative: false
                });
            }

            var ppnValElement = document.getElementById('thPpn');
            var ppnVal = ppnValElement ? ppnValElement.innerText : 0;
            // remove . in ppnVal
            if (ppnVal != '0') {
                document.getElementById('trDpPpn').hidden = false;
            }

            var gtElement = document.getElementById('grandTotalTh');
            var gtNonPpnElement = document.getElementById('grandTotalTh_non_ppn');

            if (gtElement) {
                var sisa = gtElement.innerText;
            }

            if (gtNonPpnElement) {
                var sisa_non_ppn = gtNonPpnElement.innerText;
            }

            var trSisaElement = document.getElementById('trSisa');
            var trSisaNonPpnElement = document.getElementById('trSisa_non_ppn');
            if (trSisaElement) {
                document.getElementById('trSisa').hidden = false;
            }
            if (trSisaNonPpnElement) {
                document.getElementById('trSisa_non_ppn').hidden = false;
            }

            var thSisaElement = document.getElementById('thSisa');
            var thSisaNonPpnElement = document.getElementById('thSisa_non_ppn');

            if (thSisaElement) {
                document.getElementById('thSisa').innerText = sisa;
            }
            if (thSisaNonPpnElement) {
               document.getElementById('thSisa_non_ppn').innerText = sisa_non_ppn;
            }

            document.getElementById('tempo_hari').value = tempo_hari;

        } else {
            var elementTrJumlahDp = document.getElementById('trJumlahDp');
            var elementTrJumlahDpNonPpn = document.getElementById('trJumlahDp_non_ppn');
            if (elementTrJumlahDp) {
                document.getElementById('trJumlahDp').hidden = true;

            }

            if (elementTrJumlahDpNonPpn) {
                document.getElementById('trJumlahDp_non_ppn').hidden = true;

            }

            document.getElementById('tempo_hari').value = '-';

            var elementTrDp = document.getElementById('trDp');
            var elementTrDpNonPpn = document.getElementById('trDp_non_ppn');
            if (elementTrDp) {
                document.getElementById('trDp').hidden = true;

            }
            if (elementTrDpNonPpn) {
                document.getElementById('trDp_non_ppn').hidden = true;

            }

            var tdDpPpnElement = document.getElementById('trDpPpn');
            if (tdDpPpnElement) {
                document.getElementById('trDpPpn').hidden = true;
            }
            // document.getElementById('trDpPpn').hidden = true;
            var trSisaElement = document.getElementById('trSisa');
            var trSisaNonPpnElement = document.getElementById('trSisa_non_ppn');
            if (trSisaElement) {
                document.getElementById('trSisa').hidden = true;
            }
            if (trSisaNonPpnElement) {
                 document.getElementById('trSisa_non_ppn').hidden = true;
            }


        }
    }


    function getKonsumenData()
    {
        var id = document.getElementById('konsumen_id').value;

        document.getElementById('nama').required = false;
        document.getElementById('namaTr').hidden = true;
        document.getElementById('alamat').disabled = true;
        document.getElementById('npwp').disabled = true;
        document.getElementById('no_hp').disabled = true;
        $.ajax({
            url: '{{route('universal.get-konsumen')}}',
            type: 'GET',
            data: {
                id: id
            },
            success: function(data) {
                document.getElementById('pembayaran').value = data.sistem_pembayaran;
                var kota = data.kabupaten_kota ? data.kabupaten_kota.nama_wilayah : '';
                document.getElementById('alamat').value = data.alamat + ', ' + kota;
                document.getElementById('npwp').value = data.npwp;
                document.getElementById('no_hp').value = data.no_hp;
                if (data.pembayaran == 2) {
                    // empty pembayaran option
                    document.getElementById('pembayaran').innerHTML = '';
                    // add option dengan value 1 cash, 2 tempo, 3 titipan menggunakan array atau json lalu buat selected ke 2
                    var pembayaranText = ['Cash', 'Tempo', 'Titipan'];
                    var pembayaranValue = [1, 2, 3];

                    for (var i = 0; i < pembayaranText.length; i++) {
                        var option = document.createElement('option');
                        option.value = pembayaranValue[i];
                        option.text = pembayaranText[i];
                        document.getElementById('pembayaran').add(option);
                    }

                    document.getElementById('pembayaran').value = 2;

                    document.getElementById('tempo_hari').value = data.tempo_hari;

                    tempTempo = data.tempo_hari;

                    var jumlah_dp_ppn = document.getElementById('trJumlahDp');
                    var jumlah_dp_non_ppn =  document.getElementById('trJumlahDp_non_ppn');
                    var dp_ppn = document.getElementById('trDp');
                    var dp_non_ppn = document.getElementById('trDp_non_ppn');

                    if (jumlah_dp_ppn) {
                        document.getElementById('trJumlahDp').hidden = false;

                        var dpCleave = new Cleave('#jumlah_dp', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                    }
                    if (jumlah_dp_non_ppn) {
                        document.getElementById('trJumlahDp_non_ppn').hidden = false;

                        var dpNonPpnCleave = new Cleave('#jumlah_dp_non_ppn', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                    }

                    if (dp_ppn) {

                        document.getElementById('trDp').hidden = false;
                        var dp = new Cleave('#dp', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                    }

                    if (dp_non_ppn) {
                        document.getElementById('trDp_non_ppn').hidden = false;
                        var dpNonPpn = new Cleave('#dp_non_ppn', {
                            numeral: true,
                            numeralThousandsGroupStyle: 'thousand',
                            numeralDecimalMark: ',',
                            delimiter: '.',
                            negative: false
                        });
                    }

                    var ppnValElement = document.getElementById('thPpn');
                    var ppnVal = ppnValElement ? ppnValElement.innerText : 0;
                    // remove . in ppnVal
                    if (ppnVal != '0') {
                        document.getElementById('trDpPpn').hidden = false;
                    }




                    var trSisaElement = document.getElementById('trSisa');
                    var trSisaNonPpnElement = document.getElementById('trSisa_non_ppn');

                    if (trSisaElement) {
                        document.getElementById('trSisa').hidden = false;
                        var sisa = document.getElementById('grandTotalTh').innerText;
                        document.getElementById('thSisa').innerText = sisa;
                    }
                    if (trSisaNonPpnElement) {
                        document.getElementById('trSisa_non_ppn').hidden = false;
                        var sisa_non_ppn = document.getElementById('grandTotalTh_non_ppn').innerText;
                        document.getElementById('thSisa_non_ppn').innerText = sisa_non_ppn;
                    }

                } else {

                    document.getElementById('tempo_hari').value = '-';
                    document.getElementById('trJumlahDp').hidden = true;
                    document.getElementById('trJumlahDp_non_ppn').hidden = true;

                    document.getElementById('trDp').hidden = true;
                    document.getElementById('trDp_non_ppn').hidden = true;

                    var tdDpPpnElement = document.getElementById('trDpPpn');
                    if (tdDpPpnElement) {
                        document.getElementById('trDpPpn').hidden = true;
                    }
                    // document.getElementById('trDpPpn').hidden = true;

                    document.getElementById('trSisa').hidden = true;

                    document.getElementById('trSisa_non_ppn').hidden = true;
                }
            }
        });
        return;

    }

    $('#storeForm').submit(function(e){
        e.preventDefault();
        Swal.fire({
            title: 'Apakah anda yakin?',
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

    function deleteKeranjang(id)
    {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('sales.stok.keranjang.delete', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        location.reload();
                    }
                });
            }
        })
    }

    function addKeranjangInden()
    {

        var barang_id = document.getElementById('barang_id').value;
        var jumlah = document.getElementById('jumlah_inden').value;

        if (barang_id == '' || barang_id == null) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Barang tidak boleh kosong!',
            });
            return;
        }

        if (jumlah == '' || jumlah == null) {
            Swal.fire({
                icon: 'error',
                title: 'Oops...',
                text: 'Jumlah tidak boleh kosong!',
            });
            return;
        }

        $.ajax({
            url: '{{route('sales.stok.keranjang.inden.store')}}',
            type: 'POST',
            data: {
                barang_id: barang_id,
                jumlah: jumlah,
                _token: '{{ csrf_token() }}'
            },
            success: function(data) {
                // reset barang_id dan jumlah
                document.getElementById('barang_id').value = '';
                document.getElementById('jumlah_inden').value = '';
                location.reload();
            }
        });
    }

    function indenDelete(id)
    {
        Swal.fire({
            title: 'Apakah anda yakin?',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Ya, hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '{{route('sales.stok.keranjang.inden.delete', ':id')}}'.replace(':id', id),
                    type: 'POST',
                    data: {
                        id: id,
                        _token: '{{ csrf_token() }}'
                    },
                    success: function(data) {
                        location.reload();
                    }
                });
            }
        })
    }
</script>
@endpush
