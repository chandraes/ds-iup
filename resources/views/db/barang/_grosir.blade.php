<button
    type="button"
    class="btn btn-{{$d->is_grosir ? 'warning' : 'primary'}} btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#grosirModal"
    onclick="setGrosir({{$d->id}}, {{$d->satuan}})"
>
    {{$d->is_grosir ? 'Atur' : 'Tambah'}} Grosir
</button>
