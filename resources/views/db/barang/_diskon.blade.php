@if ($d->diskon > 0)
Diskon {{ $d->diskon }}% <br>
{{ $d->diskon_mulai ? $d->diskon_mulai : 'N/A' }} s/d
{{ $d->diskon_selesai ? $d->diskon_selesai : 'N/A' }} <br>
<a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal"
    onclick="setDiskon({{ $d->id }}, {{ $d->diskon }}, {{ $d->diskon_mulai ? $d->diskon_mulai : 'null' }}, {{ $d->diskon_selesai ? $d->diskon_selesai : 'null' }})">
    <i class="fa fa-edit"></i> Edit Diskon
</a>
@else
<a href="#" data-bs-toggle="modal" data-bs-target="#diskonModal"
    onclick="setDiskon({{ $d->id }}, {{ $d->diskon }}, {{ $d->diskon_mulai ? $d->diskon_mulai : 'null' }}, {{ $d->diskon_selesai ? $d->diskon_selesai : 'null' }})">
    <i class="fa fa-plus"></i> Tambah Diskon
</a>
@endif
