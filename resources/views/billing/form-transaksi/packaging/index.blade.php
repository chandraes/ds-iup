@extends('layouts.app')
@section('content')
<div class="container-fluid">
    <div class="row justify-content-center mb-5">
        <div class="col-md-12 text-center">
            <h1><u>Form Beli Packaging</u></h1>
            <h1><u>CASH</u></h1>
        </div>
    </div>
    <div class="row justify-content-left mt-3 mb-3">
        <div class="col-5">
            <table>
                <tr>
                    <td>
                        <a href="#" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#keranjangBelanja" >
                            <i class="fa fa-shopping-cart"> Keranjang </i> ({{$keranjang->count()}})
                        </a>
                        @include('billing.form-transaksi.packaging.keranjang')
                    </td>
                    <td>
                        <form action="{{route('billing.form-transaksi.packaging.keranjang.empty')}}" method="post" id="kosongKeranjang">
                            @csrf
                            <button class="btn btn-danger" type="submit">
                                <i class="fa fa-trash"> Kosongkan Keranjang </i>
                            </button>
                        </form>
                    </td>
                </tr>
            </table>
        </div>
    </div>
    @include('swal')
    <form action="{{route('billing.form-transaksi.packaging.store')}}" method="post" id="masukForm">
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

        function add_diskon() {
            // Existing code to calculate discount and total after discount
            var diskonT = document.getElementById('diskon').value;
            var diskon = diskonT.replace(/\./g, '');
            var total = document.getElementById('tdTotal').textContent;
            total = total.replace(/\./g, '');
            var ppn = document.getElementById('tdPpn').textContent;
            ppn = ppn.replace(/\./g, '');
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
        }

        function add_ppn() {
            var apa_ppn = document.getElementById('ppn').value;
            var ppnRate = {!! $ppn !!} / 100;
            // Retrieve add_fee value and convert it to a number after removing any formatting
            var addFee = Number(document.getElementById('add_fee').value.replace(/\./g, ''));
            if (apa_ppn === "1") {
                var gt = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
                var vPpn = gt * ppnRate;
                // Include add_fee in the total calculation
                var totalap = gt + vPpn + addFee;
                var tF = totalap.toLocaleString('id-ID');
                var vF = vPpn.toLocaleString('id-ID');
                document.getElementById('grand_total').textContent = tF;
                document.getElementById('tdPpn').textContent = vF;
            } else {
                // Since PPN is not applied, directly update grand_total with tdTotalSetelahDiskon and add_fee
                var gtWithoutPpn = Number(document.getElementById('tdTotalSetelahDiskon').textContent.replace(/\./g, ''));
                var totalWithoutPpn = gtWithoutPpn + addFee;
                var totalFormatted = totalWithoutPpn.toLocaleString('id-ID');
                document.getElementById('grand_total').textContent = totalFormatted;
            }
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

    </script>
@endpush
