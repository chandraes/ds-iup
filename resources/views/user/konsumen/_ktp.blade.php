@if ($d->upload_ktp && \Illuminate\Support\Facades\Storage::disk('local')->exists($d->upload_ktp))
    <a href="{{ route('customer-files.ktp', $d->id) }}" data-lightbox="ktp-{{ $d->id }}" data-title="Foto KTP {{ $d->nama }}">
        <img src="{{ route('customer-files.ktp', $d->id) }}" alt="Foto KTP" class="img-fluid" width="100">
    </a>
   
@else
   -
@endif
