<div class="d-flex justify-content-center">
    <button type="button" class="btn btn-primary m-2" onclick="editInvestor({{ $d }}, {{ $d->id }})"><i class="fa fa-edit"></i></button>
    <form action="{{ route('db.konsumen.delete', $d) }}" method="post" id="deleteForm-{{ $d->id }}">
        @csrf
        @method('delete')
        <button type="submit" class="btn btn-danger m-2"><i class="fa fa-power-off"></i></button>
    </form>
</div>
