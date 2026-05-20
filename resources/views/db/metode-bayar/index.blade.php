@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><i class="fa fa-cog text-secondary"></i> Pengaturan Batas Jatuh Tempo</h1>
            <p class="text-muted small mb-0">Tentukan maksimal penambahan hari jatuh tempo berdasarkan metode pembayaran.</p>
        </div>
        <a href="{{ route('db') }}" class="btn btn-secondary shadow-sm"><i class="fa fa-arrow-left"></i> Kembali</a>
    </div>

    <div class="card shadow-sm border-0 col-md-8 mx-auto">
        <div class="card-body p-0">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-success">
                    <tr>
                        <th class="text-center" width="10%">No</th>
                        <th>Metode Pembayaran</th>
                        <th class="text-center">Maksimal Jatuh Tempo (+Hari)</th>
                        <th class="text-center" width="20%">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($metodeBayars as $index => $mb)
                    <tr>
                        <td class="text-center">{{ $index + 1 }}</td>
                        <td class="fw-bold">{{ $mb->nama }}</td>
                        <td class="text-center h6 mb-0 fw-bold text-primary">{{ $mb->max_hari }} Hari</td>
                        <td class="text-center">
                            <button class="btn btn-sm btn-outline-warning px-3 btn-edit-days" data-id="{{ $mb->id }}" data-nama="{{ $mb->nama }}" data-hari="{{ $mb->max_hari }}">
                                <i class="fa fa-edit"></i> Edit Hari
                            </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection

@push('js')
<script>
    $('.btn-edit-days').click(function() {
        let id = $(this).data('id');
        let nama = $(this).data('nama');
        let currentHari = $(this).data('hari');
        let urlUpdate = "{{ route('db.metode-bayar.update', ':id') }}".replace(':id', id);

        Swal.fire({
            title: 'Ubah Batas Hari ' + nama,
            text: 'Masukkan jumlah maksimal penambahan hari jatuh tempo dari hari ini:',
            input: 'number',
            inputValue: currentHari,
            inputAttributes: { min: 0, step: 1 },
            showCancelButton: true,
            confirmButtonText: 'Simpan Perubahan',
            confirmButtonColor: '#ffc107',
            cancelButtonText: 'Batal',
            inputValidator: (value) => {
                if (!value || value < 0) {
                    return 'Jumlah hari wajib diisi dan tidak boleh minus!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $('#spinner').show();
                $.ajax({
                    url: urlUpdate,
                    type: 'POST',
                    data: { _token: "{{ csrf_token() }}", max_hari: result.value },
                    success: function(res) {
                        $('#spinner').hide();
                        Swal.fire('Berhasil!', res.message, 'success').then(() => location.reload());
                    },
                    error: function() {
                        $('#spinner').hide();
                        Swal.fire('Error', 'Gagal memperbarui data.', 'error');
                    }
                });
            }
        });
    });
</script>
@endpush
