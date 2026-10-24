<div class="d-flex flex-wrap gap-1">
    @foreach(['foto_ktp' => ['KTP', 'id-card-o'], 'foto_diri' => ['Foto', 'picture-o'], 'perjanjian_kerja' =>
    ['Perjanjian kerja', 'file-pdf-o']] as $document => [$label, $icon])
    @if($employee->$document)
    <a class="btn btn-sm btn-outline-primary" href="{{ route('db.staff.document', [$employee, $document]) }}"
        target="_blank" rel="noopener"><i class="fa fa-{{ $icon }} me-1" aria-hidden="true"></i>{{ $label }}</a>
    @else
    <span class="btn btn-sm btn-outline-secondary disabled" aria-disabled="true">{{ $label }}</span>
    @endif
    @endforeach
</div>
