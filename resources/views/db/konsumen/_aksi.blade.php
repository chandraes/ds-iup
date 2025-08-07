<div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal" data-bs-target="#editInvestor"
        onclick="editInvestor({{$d}}, {{$d->id}})"><i class="fa fa-edit"></i></button>
    <form action="{{route('db.konsumen.delete', $d)}}" method="post" id="deleteForm-{{$d->id}}">
        @csrf
        @method('delete')
        <button type="submit" class="btn btn-danger m-2"><i
                class="fa fa-{{request()->has('status') && request('status') == 0 ? 'refresh' : 'power-off' }}"></i></button>
    </form>
</div>
<div class="d-flex justify-content-center">
    <a href="{{route('db.konsumen.daftar-kunjungan', $d->id)}}" class="btn btn-success btn-sm m-2"
        target="_blank">Daftar Kunjungan</a>
</div>

<script>
    $('#deleteForm-{{$d->id}}').submit(function(e){
                    e.preventDefault();
                    Swal.fire({
                        title: 'Apakah data yakin untuk menonaktifkan konsumen ini?',
                        icon: 'warning',
                        input: 'text',
                        inputPlaceholder: 'Masukkan alasan',
                        inputValidator: (value) => {
                            if (!value) {
                                return 'Anda harus memasukkan alasan!'
                            }
                        },
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#6c757d',
                        confirmButtonText: 'Ya, hapus!'
                        }).then((result) => {
                        if (result.isConfirmed) {
                            var status =1;
                            $('#deleteForm-{{$d->id}}').append($('<input>').attr({
                                type: 'hidden',
                                name: 'status',
                                value: status
                            }));
                            $('<input>').attr({
                                type: 'hidden',
                                name: 'alasan',
                                value: result.value
                            }).appendTo('#deleteForm-{{$d->id}}');
                            $('#spinner').show();
                            this.submit();
                        }
                    })
                });
</script>
