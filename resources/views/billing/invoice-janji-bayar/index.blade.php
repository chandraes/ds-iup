@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-file-text text-primary"></i> Daftar Dokumen Janji Bayar</h1>
            <p class="text-muted small mb-0">Memuat riwayat komitmen janji bayar yang berstatus <strong class="text-warning">Pending</strong>.</p>
        </div>
        <div>
            <a href="{{ route('billing') }}" class="btn btn-secondary shadow-sm me-2">
                <i class="fa fa-arrow-left"></i> Kembali ke Billing
            </a>
            <a href="{{ route('billing.form-janji-bayar') }}" class="btn btn-success shadow-sm">
                <i class="fa fa-plus-circle"></i> Buat Janji Bayar Baru
            </a>
        </div>
    </div>

    @include('swal')

    <div class="card shadow-sm border-0">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle w-100" id="janjiBayarTable" style="font-size: 0.9rem;">
                    <thead class="table-success">
                        <tr>
                            <th class="text-center" width="5%">No</th>
                            <th>Kode Dokumen</th>
                            <th>Konsumen</th>
                            <th class="text-center">Metode</th>
                            <th class="text-end">Nominal Komitmen</th>
                            <th class="text-center">Jatuh Tempo</th>
                            <th class="text-center">Status</th>
                            <th class="text-center" width="18%">Aksi</th> </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('css')
<link href="{{ asset('assets/css/dt.min.css') }}" rel="stylesheet">
@endpush

@push('js')
<script src="{{ asset('assets/js/dt5.min.js') }}"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi DataTable ke variabel agar bisa direload via AJAX nanti
        let table = $('#janjiBayarTable').DataTable({
            processing: true,
            serverSide: true,
            ajax: "{{ route('billing.invoice-janji-bayar.data') }}",
            order: [[1, 'desc']],
            columns: [
                { data: 'DT_RowIndex', name: 'DT_RowIndex', orderable: false, searchable: false, className: 'text-center' },
                { data: 'kode', name: 'kode', className: 'fw-bold text-primary' },
                { data: 'konsumen_nama', name: 'konsumen_nama' },
                { data: 'metode', name: 'metode', className: 'text-center text-uppercase' },
                { data: 'nominal', name: 'nominal', className: 'text-end fw-bold' },
                { data: 'jatuh_tempo', name: 'jatuh_tempo', className: 'text-center' },
                { data: 'status', name: 'status', className: 'text-center' },
                { data: 'action', name: 'action', orderable: false, searchable: false, className: 'text-center' }
            ]
        });

        // PERUBAHAN AJAK VOID MECHANISM DENGAN SWEETALERT CONFIRMATION
        $('#janjiBayarTable').on('click', '.btn-void-document', function(e) {
            e.preventDefault();
            let id = $(this).data('id');
            let kode = $(this).data('kode');
            let urlVoid = "{{ route('billing.invoice-janji-bayar.void', ':id') }}".replace(':id', id);

            Swal.fire({
                title: 'Apakah Anda Yakin?',
                text: "Dokumen Janji Bayar (" + kode + ") akan dihapus permanen, dan status seluruh nota di dalamnya akan dikembalikan menjadi BELUM LUNAS!",
                icon: 'danger',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Void Dokumen!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show(); // Tampilkan global loading jika ada

                    $.ajax({
                        url: urlVoid,
                        type: 'POST',
                        data: {
                            _token: "{{ csrf_token() }}"
                        },
                        success: function(response) {
                            $('#spinner').hide();
                            if(response.status === 'success') {
                                Swal.fire('Berhasil Void!', response.message, 'success');
                                table.ajax.reload(null, false); // Reload data table secara asinkron tanpa reset pagination
                            } else {
                                Swal.fire('Gagal!', response.message, 'error');
                            }
                        },
                        error: function(xhr) {
                            $('#spinner').hide();
                            let msg = xhr.responseJSON ? xhr.responseJSON.message : 'Terjadi kesalahan sistem.';
                            Swal.fire('Oops...', msg, 'error');
                        }
                    });
                }
            });
        });
    });
</script>
@endpush
