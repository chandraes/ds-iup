@extends('layouts.app')

@section('content')
@php
    $actionLabels = [
        'created' => ['Tambah', 'success'],
        'updated' => ['Ubah', 'warning'],
        'deleted' => ['Hapus', 'danger'],
        'delete_rejected' => ['Hapus Ditolak', 'secondary'],
        'grosir_created' => ['Tambah Grosir', 'info'],
        'grosir_deleted' => ['Hapus Grosir', 'dark'],
    ];
@endphp
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>LOG AUDIT BARANG</u></h1>
            @if ($barang)
                <h5>#{{ $barang->id }} {{ $barang->barang_nama->nama }}, {{ $barang->kode }}, {{ $barang->merk }}</h5>
            @elseif (request('barang_id'))
                <h5>Barang #{{ request('barang_id') }} (sudah dihapus)</h5>
            @endif
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle"><a href="{{ route('home') }}"><img
                                src="{{ asset('images/dashboard.svg') }}" alt="dashboard" width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{ route('db.barang') }}"><img
                                src="{{ asset('images/barang.svg') }}" alt="barang" width="30"> Data Barang</a></td>
                </tr>
            </table>
        </div>
    </div>

    <form method="get" class="row g-2 mb-3">
        <div class="col-md-3">
            <input type="number" class="form-control" name="barang_id" value="{{ request('barang_id') }}" placeholder="ID Barang">
        </div>
        <div class="col-md-3">
            <select name="action" class="form-select">
                <option value="">Semua Aksi</option>
                @foreach ($actionLabels as $value => [$label])
                    <option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-3">
            <button type="submit" class="btn btn-primary">Filter</button>
            <a href="{{ route('db.barang.audit-log') }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>

    <div class="table-responsive">
        <table class="table table-bordered table-sm align-middle">
            <thead class="table-success text-center">
                <tr>
                    <th>Waktu</th>
                    <th>User</th>
                    <th>Aksi</th>
                    <th>Barang</th>
                    <th>Perubahan</th>
                    <th>Sumber</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($logs as $log)
                    @php
                        [$label, $color] = $actionLabels[$log->action] ?? [$log->action, 'secondary'];
                        $old = $log->old_values ?? [];
                        $new = $log->new_values ?? [];
                        $context = $log->context ?? [];
                    @endphp
                    <tr>
                        <td class="text-nowrap">{{ $log->created_at->format('d-m-Y H:i:s') }}</td>
                        <td>{{ $log->user?->name ?? $log->user_name ?? '-' }}</td>
                        <td class="text-center"><span class="badge bg-{{ $color }}">{{ $label }}</span></td>
                        <td class="text-center">
                            @if ($log->barang_id)
                                <a href="{{ route('db.barang.audit-log', ['barang_id' => $log->barang_id]) }}">#{{ $log->barang_id }}</a>
                            @else
                                -
                            @endif
                        </td>
                        <td>
                            @foreach (array_unique([...array_keys($old), ...array_keys($new)]) as $field)
                                <div class="small">
                                    <strong>{{ $field }}</strong>:
                                    @if (array_key_exists($field, $old))
                                        <span class="text-danger">{{ is_scalar($old[$field]) ? $old[$field] : json_encode($old[$field]) }}</span>
                                    @endif
                                    @if (array_key_exists($field, $old) && array_key_exists($field, $new)) &rarr; @endif
                                    @if (array_key_exists($field, $new))
                                        <span class="text-success">{{ is_scalar($new[$field]) ? $new[$field] : json_encode($new[$field]) }}</span>
                                    @endif
                                </div>
                            @endforeach
                            @isset($context['reason'])
                                <div class="small text-danger">Alasan: {{ $context['reason'] }}</div>
                            @endisset
                        </td>
                        <td class="small">
                            <div>{{ $context['route'] ?? '-' }} ({{ $context['method'] ?? '-' }})</div>
                            <div>IP: {{ $context['ip'] ?? '-' }}</div>
                            @isset($context['referer'])
                                <div class="text-break">Dari: {{ $context['referer'] }}</div>
                            @endisset
                            @if (! empty($context['input']) || ! empty($context['files']))
                                <details>
                                    <summary>Data dikirim</summary>
                                    <pre class="small mb-0">{{ json_encode(['input' => $context['input'] ?? [], 'files' => $context['files'] ?? []], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                </details>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="text-center">Belum ada log.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endsection
