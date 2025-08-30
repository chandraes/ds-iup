
@if ($d->docs_count > 0)
    <div class="row px-3 text-nowrap mt-1">
        <button type="button" data-bs-toggle="modal" data-bs-target="#dokumenModal"
            onclick="dokumen({{ $d->id }}, '{{$d->nama}}', '{{$d->kode_toko->kode}}')" class="btn btn-success btn-sm">
            <i class="fa fa-upload"></i> Atur
        </button>
    </div>
@else
    <div class="row px-3 text-nowrap">
        <button type="button" data-bs-toggle="modal" data-bs-target="#dokumenModal"
            onclick="dokumen({{ $d->id }}, '{{$d->nama}}', '{{$d->kode_toko->kode}}')"  class="btn btn-primary btn-sm">
            <i class="fa fa-plus"></i> Tambah
        </button>
    </div>
@endif
