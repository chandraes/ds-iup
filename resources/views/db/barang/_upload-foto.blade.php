@if ($d->foto != null)
    <a href="{{ asset('storage/' . $d->foto) }}" data-lightbox="foto-barang-{{ $d->id }}" data-title="{{ $d->barang_nama->nama }}">
        <img src="{{ asset('storage/' . $d->foto) }}" alt="Foto Barang" class="img-fluid" width="100">
    </a>
    <div class="row px-3 text-nowrap mt-1">
        <button type="button" data-bs-toggle="modal" data-bs-target="#uploadFotoModal"
            onclick="uploadFoto({{ $d->id }})" class="btn btn-success btn-sm">
            <i class="fa fa-upload"></i> Edit
        </button>
    </div>
@else
    <div class="row px-3 text-nowrap">
        <button type="button" data-bs-toggle="modal" data-bs-target="#uploadFotoModal"
            onclick="uploadFoto({{ $d->id }})" class="btn btn-primary btn-sm">
            <i class="fa fa-upload"></i> Upload
        </button>
    </div>
@endif
