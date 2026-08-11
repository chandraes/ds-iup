@extends('layouts.app')
@section('content')
<div class="container">
    <!-- Header dan navigasi tetap sama -->
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>PPN KELUARAN</u></h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td><a href="{{route('pajak.index')}}"><img src="{{asset('images/pajak.svg')}}" alt="dokumen" width="30"> PAJAK</a></td>
                    <td><a href="@if($keranjang > 0) {{route('pajak.ppn-keluaran.keranjang')}} @else # @endif"><i class="fa fa-shopping-cart h3 me-2"></i> Keranjang {!! $keranjang > 0 ? "<span class='text-danger'>($keranjang)</span>" : '' !!}</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
@include('pajak.ppn-keluaran.faktur-modal')
@include('pajak.ppn-keluaran.show-faktur')

<div class="container-fluid table-responsive ml-3">
    <div class="row mt-3">
        <form action="{{route('pajak.ppn-keluaran.keranjang-store')}}" method="post" id="keranjangForm">
            @csrf
            <div class="row">
                <div class="col-md-2 text-end">
                    <div class="mb-3 pt-2">
                        <label for="total_tagihan">Nominal Dipilih :</label>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="mb-3">
                        <input type="text" class="form-control" name="total_tagihan" id="total_tagihan" value="0" hidden />
                        <input type="hidden" name="selectedData" id="selectedData" required>
                        <input type="text" class="form-control" name="total_tagihan_display" id="total_tagihan_display" value="0" disabled />
                    </div>
                </div>
                <div class="col-md-2">
                    <div class="row">
                        <button type="submit" class="btn btn-success">Masukan Keranjang</button>
                    </div>
                </div>
            </div>
        </form>

        <table class="table table-hover table-bordered w-100 nowrap" id="rekapTable">
            <thead class="table-success">
                <tr>
                    <th class="text-center align-middle" rowspan="2">
                        <input style="height: 25px; width:25px" type="checkbox" onclick="checkAll(this)" id="checkAllMaster">
                    </th>
                    <th rowspan="2" class="text-center align-middle">Tanggal</th>
                    <th rowspan="2" class="text-center align-middle">Nota</th>
                    <th rowspan="2" class="text-center align-middle">Konsumen</th>
                    <th rowspan="2" class="text-center align-middle">NIK/NPWP</th>
                    <th rowspan="2" class="text-center align-middle">Uraian</th>
                    <th colspan="2" class="text-center align-middle">Sebelum Terbit Faktur</th>
                    <th rowspan="2" class="text-center align-middle">Setelah<br>Terbit<br>Faktur</th>
                    <th rowspan="2" class="text-center align-middle">ACT</th>
                </tr>
                <tr>
                    <th class="text-center align-middle">NIK</th>
                    <th class="text-center align-middle">NPWP</th>
                </tr>
            </thead>
            <tbody>
                <!-- Load by Yajra -->
            </tbody>
            <tfoot>
                <tr>
                    <th class="text-end align-middle" colspan="6">Grand Total</th>
                    <th class="text-end align-middle">{{ number_format($totalNonNpwp, 0, ',','.') }}</th>
                    <th class="text-end align-middle">{{ number_format($totalNpwp, 0, ',','.') }}</th>
                    <th class="text-end align-middle">{{ number_format($totalFaktur, 0, ',','.') }}</th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
<!-- Tambahan Ekstensi Scroller DataTables untuk Infinite Scroll -->
<link href="https://cdn.datatables.net/scroller/2.2.0/css/scroller.bootstrap5.min.css" rel="stylesheet">
<style>
    /* Agar footer tidak ikut scroll dan tetap menempel (sticky) di bawah jika diperlukan */
    tfoot th { position: sticky; bottom: 0; background-color: #f8f9fa; z-index: 1; }
</style>
@endpush

@push('js')
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<!-- Tambahan Script Ekstensi Scroller -->
<script src="https://cdn.datatables.net/scroller/2.2.0/js/dataTables.scroller.min.js"></script>

<script>
    function showFaktur(noFaktur) {
        $('#no_faktur_show').val(noFaktur);
    }

    function faktur(id, nota, nominal, is_faktur, no_faktur) {
        const form = document.getElementById('fakturForm');
        form.action = `/pajak/ppn-keluaran/store-faktur/${id}`;
        form.reset();

        $('#nota').val(nota);
        $('#nominal').val(nominal);
        $('#no_faktur').val(is_faktur == 1 ? no_faktur : '');
    }

    $(document).ready(function() {
        $(document).on('submit', '.expired-form', function(e){
            e.preventDefault();
            var form = $(this);
            Swal.fire({
                title: 'Apakah Anda Yakin?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    form[0].submit();
                }
            });
        });

        // Inisialisasi DataTables dengan Infinite Scroll & Fix Sorting
        $('#rekapTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('pajak.ppn-keluaran') }}",

            // Konfigurasi Infinite Scroll
            scrollY: 400, // Harus berupa angka (tanpa 'px') untuk Scroller
            scrollCollapse: true,
            deferRender: true,
            scroller: {
                loadingIndicator: true
            },

            order: [[1, 'asc']],
            columns: [
                { data: 'checkbox', name: 'id', orderable: false, searchable: false, className: 'text-center align-middle' },
                { data: 'tanggal', name: 'tanggal', className: 'text-center align-middle' },
                { data: 'nota', name: 'nota', className: 'text-center align-middle' },
                { data: 'konsumen', name: 'konsumen', className: 'text-start align-middle' },
                { data: 'nik_npwp', name: 'nik_npwp', className: 'text-start align-middle text-nowrap' },
                { data: 'uraian', name: 'uraian', className: 'text-start align-middle' },
                { data: 'non_npwp', name: 'non_npwp', className: 'text-end align-middle' },
                { data: 'npwp', name: 'npwp', className: 'text-end align-middle' },
                { data: 'faktur', name: 'faktur', className: 'text-end align-middle' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center align-middle' },
            ]
        });
    });

    // Logic Checkbox - Tetap menggunakan array penyimpan ID yang dipilih
    function check(checkbox, id) {
        var totalTagihan = parseFloat($('#total_tagihan').val()) || 0;
        var tagihan = parseFloat($(checkbox).data('tagihan'));
        var selectedDataInput = $('#selectedData');
        var selectedValues = selectedDataInput.val() ? selectedDataInput.val().split(',') : [];

        if (checkbox.checked) {
            totalTagihan += tagihan;
            if (!selectedValues.includes(id.toString())) {
                selectedValues.push(id);
            }
        } else {
            totalTagihan -= tagihan;
            selectedValues = selectedValues.filter(val => val !== id.toString() && val !== '');
        }

        $('#total_tagihan').val(totalTagihan);
        $('#total_tagihan_display').val(totalTagihan.toLocaleString('id-ID'));

        selectedDataInput.val(selectedValues.join(',').replace(/^,|,$/g, ''));
    }

    // Check All: Hanya menyeleksi checkbox yang sedang termuat (DOM aktif) di view saat scroll
    function checkAll(checkbox) {
        $('.dt-checkbox').each(function() {
            if (!this.disabled && this.checked !== checkbox.checked) {
                this.checked = checkbox.checked;
                check(this, this.value);
            }
        });
    }
</script>
@endpush
