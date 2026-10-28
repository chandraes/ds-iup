<div class="customer-actions" role="group" aria-label="Aksi konsumen {{ $d->nama }}">
    <button type="button" data-bs-toggle="modal" data-bs-target="#editInvestor"
        data-customer="{{ json_encode($d) }}" class="btn btn-outline-primary customer-edit">
        <i class="fa fa-edit" aria-hidden="true"></i><span>Edit</span>
    </button>
    <form action="{{ route('db.konsumen.delete', $d) }}" method="post"
        class="customer-status-form" data-active="{{ (int) $d->active }}" id="deleteForm-{{ $d->id }}">
        @csrf
        @method('delete')
        <input type="hidden" name="active" value="{{ $d->active ? 0 : 1 }}">
        <button type="submit" class="btn {{ $d->active ? 'btn-outline-danger' : 'btn-outline-success' }}">
            <i class="fa fa-{{ $d->active ? 'power-off' : 'refresh' }}" aria-hidden="true"></i>
            <span>{{ $d->active ? 'Nonaktifkan' : 'Aktifkan' }}</span>
        </button>
    </form>
    <a href="{{ route('db.konsumen.daftar-kunjungan', $d->id) }}"
        class="btn btn-outline-secondary customer-actions-visit" target="_blank" rel="noopener"
        aria-label="Daftar kunjungan {{ $d->nama }} (tab baru)">
        <i class="fa fa-calendar" aria-hidden="true"></i><span>Daftar Kunjungan</span>
        <i class="fa fa-external-link customer-actions-external" aria-hidden="true"></i>
    </a>
</div>
