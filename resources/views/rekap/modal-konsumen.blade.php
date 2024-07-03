<div class="modal fade" id="kasKonsumenModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="konsumenTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="konsumenTitle">
                    Pilih Konsumen
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('rekap.kas-konsumen')}}" method="get">
                <div class="modal-body">
                    <div class="mb-3">
                        <select class="form-select" name="konsumen_id" id="konsumen_id">
                            <option value="" disabled selected>-- Pilih Konsumen --</option>
                            @foreach ($konsumen as $d)
                            <option value="{{$d->id}}">{{$d->nama}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Lanjutkan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">
@endpush
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>
    $('#konsumen_id').select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Pilih',
            dropdownParent: $('#kasKonsumenModal')
        });
</script>
@endpush
