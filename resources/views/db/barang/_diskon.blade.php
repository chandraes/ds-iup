@if ($d->diskon > 0)

<div class="{{ $d->diskon_selesai && \Carbon\Carbon::parse($d->diskon_selesai)->lt(now()) ? 'text-danger' : '' }}">
    Diskon {{ $d->diskon }}% <br>
{{ $d->diskon_mulai ? $d->diskon_mulai : 'N/A' }} s/d
{{ $d->diskon_selesai ? $d->diskon_selesai : 'N/A' }}
</div>
<br>
<a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal"
    onclick="setDiskon({{ $d->id }}, @js($d->diskon), @js($d->diskon_mulai), @js($d->diskon_selesai), @js($d->barang_nama->nama), @js($d->kode ?? ''), @js($d->merk))">
    <i class="fa fa-edit"></i> Edit Diskon
</a>
@else
<a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal"
    onclick="setDiskon({{ $d->id }}, @js($d->diskon), @js($d->diskon_mulai), @js($d->diskon_selesai), @js($d->barang_nama->nama), @js($d->kode ?? ''), @js($d->merk))">
    <i class="fa fa-plus"></i> Tambah Diskon
</a>
@endif
