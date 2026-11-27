<button
    type="button"
    class="btn btn-{{$d->is_grosir ? 'warning' : 'primary'}} btn-sm"
    data-bs-toggle="modal"
    data-bs-target="#grosirModal"
    onclick="setGrosir({{$d->id}}, {{$d->satuan}}, @js($d->barang_nama->nama), @js($d->kode ?? ''), @js($d->merk))"
>
    {{$d->is_grosir ? 'Atur' : 'Tambah'}} Grosir
</button>
