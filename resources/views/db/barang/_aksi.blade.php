<a href="#" class="btn btn-warning m-2" data-bs-toggle="modal" data-bs-target="#editModal"
    onclick="editFun({{ $d }}, {{ $d->barang_type_id }}, {{ $d->barang_unit_id }})"><i class="fa fa-edit"></i></a>
<form action="{{ route('db.barang.delete', $d->id) }}" method="post" class="d-inline delete-form"
    id="deleteForm{{ $d->id }}" data-id="{{ $d->id }}">
    @csrf
    @method('delete')
    <button type="submit" class="btn btn-danger m-2"><i class="fa fa-trash"></i></button>
</form>
