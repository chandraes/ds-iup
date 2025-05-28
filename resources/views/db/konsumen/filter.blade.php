<div class="row mb-3">
    <div class="col-md-3 mb-2">
        <label for="filterSalesArea" class="form-label">Sales Area</label>

        <select id="filterSalesArea" name="area" class="form-select" onchange="filterData()">
            <option value="" selected>-- Semua Sales Area --</option>
            @foreach ($sales_area as $salesArea)
            <option value="{{ $salesArea->id }}" {{ request('area')==$salesArea->id ? 'selected' : '' }}>
                {{ $salesArea->nama }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <label for="filterProvinsi" class="form-label">Provinsi</label>
        <select id="filterProvinsi" name="provinsi" class="form-select" onchange="filterData()">
            <option value="">-- Semua Provinsi --</option>
            @foreach ($provinsi as $prov)
            <option value="{{ $prov->id }}" {{ request('provinsi')==$prov->id ? 'selected' : '' }}>
                {{ $prov->nama_wilayah }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <label for="filterKota" class="form-label">Kab/Kota</label>
        <select id="filterKota" name="kabupaten_kota" class="form-select" onchange="filterData()">
            <option value="">-- Semua Kab/Kota --</option>
            @foreach ($kabupaten_kota as $kab)
            <option value="{{ $kab->id }}" {{ request('kabupaten_kota')==$kab->id ? 'selected' : '' }}>
                {{ $kab->nama_wilayah }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <label for="filterKecamatan" class="form-label">Kecamatan</label>
        <select id="filterKecamatan" name="kecamatan" class="form-select" onchange="filterData()">
            <option value="">-- Semua Kecamatan --</option>
            @foreach ($kecamatan_filter as $kec)
            <option value="{{ $kec->id }}" {{ request('kecamatan')==$kec->id ? 'selected' : '' }}>
                {{ $kec->nama_wilayah }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-1">
        <label for="filterSalesArea" class="form-label">Kode Toko</label>

        <select id="filterKodeToko" name="kode_toko" class="form-select" onchange="filterData()">
            <option value="" selected>-- Semua Kode Toko --</option>
            @foreach ($kode_toko as $k)
            <option value="{{ $k->id }}" {{ request('kode_toko')==$k->id ? 'selected' : '' }}>
                {{ $k->kode }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-3 mb-2">
        <label for="filterKecamatan" class="form-label">Status</label>
        <select id="filterStatus" name="status" class="form-select" onchange="filterData()">
            <option value="1" {{ request()->has('status') && request('status') == 1 ? 'selected' : 'selected'
                }}>Aktif</option>
            <option value="0" {{ request()->has('status') && request('status') == 0 ? 'selected' : '' }}>Non
                Aktif</option>
        </select>
    </div>
    <div class="col-md-1 mt-4">
        <div class="row">
            <a href="{{ url()->current() }}" class="btn btn-secondary mt-2">Reset</a>
        </div>

    </div>
</div>
@push('js')
<script src="{{asset('assets/plugins/select2/select2.full.min.js')}}"></script>
<script>


      $('#filterProvinsi').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterKecamatan').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });
    $('#filterKota').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterKodeToko').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });

    $('#filterSalesArea').select2({
        theme: 'bootstrap-5',
        width: '100%',
    });
</script>
@endpush

