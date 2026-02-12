@if ($d->upload_ktp != null)
    <a href="{{ asset('storage/' . $d->upload_ktp) }}" data-lightbox="ktp-{{ $d->id }}" data-title="Foto KTP {{ $d->nama }}">
        <img src="{{ asset('storage/' . $d->upload_ktp) }}" alt="Foto KTP" class="img-fluid" width="100">
    </a>
   
@else
   -
@endif
