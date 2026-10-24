@extends('layouts.app')
@section('content')
<div class="container iup-employees">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <div class="small text-primary text-uppercase fw-bold">Database perusahaan</div>
            <h2 class="fw-bold mb-1"><i class="fa fa-users fa-fw me-1" aria-hidden="true"></i>Manajemen pegawai</h2>
            <p class="text-muted mb-0">Kelola direksi, staff, jabatan, dan status kepegawaian.</p>
        </div>
        <div class="d-flex gap-2"><a href="{{ route('db') }}" class="btn btn-outline-secondary"><i
                    class="fa fa-database fa-fw me-1" aria-hidden="true"></i>Database</a><a
                href="{{ route('db.staff.create') }}" class="btn btn-primary"><i class="fa fa-plus fa-fw me-1"
                    aria-hidden="true"></i>Tambah pegawai</a></div>
    </div>
    @include('db.karyawan.feedback')
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-3 mb-4">
                <div>
                    <h5 class="fw-bold">Daftar pegawai</h5>@if(config('attendance.enabled'))<a
                        href="{{ route('attendance-integration.index') }}"
                        class="btn btn-sm btn-outline-secondary me-2"><i class="fa fa-refresh me-1"
                            aria-hidden="true"></i>Integrasi absensi</a>@endif<button
                        class="btn btn-sm btn-outline-primary" data-bs-toggle="modal"
                        data-bs-target="#create-category"><i class="fa fa-briefcase fa-fw me-1"
                            aria-hidden="true"></i>Kelola jabatan</button>
                </div>
                <div class="d-flex flex-wrap gap-3">
                    <div><label class="form-label small fw-semibold" for="employee-position">Jabatan</label>
                        <select id="employee-position" class="form-select">
                            <option value="">Semua jabatan</option>
                            @foreach($jabatan as $position)
                                <option value="{{ $position->id }}" @selected((string)$selectedJabatan === (string)$position->id)>{{ $position->nama }}</option>
                            @endforeach
                        </select>
                    </div>
                <div><label class="form-label small fw-semibold" for="employee-status">Status pegawai</label><select
                        id="employee-status" class="form-select">@foreach(['1'=>'Aktif','0'=>'Nonaktif','all'=>'Semua
                        status'] as $value=>$label)<option value="{{ $value }}"
                            @selected((string)$status===(string)$value)>{{ $label }}</option>@endforeach</select></div></div>
            </div>
            <div class="table-responsive">
                <table id="employee-table" class="table table-hover align-middle w-100">
                    <thead class="table-success">
                        <tr>
                            <th>No</th>
                            <th>Nama</th>
                            <th>Panggilan</th>
                            <th>Jabatan</th>
                            <th>Informasi bank</th>
                            <th>Status</th>
                            <th>Dokumen</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            </div>
        </div>
    </div>
    @include('db.karyawan.create-jabatan')
</div>
@include('db.karyawan.assets')
@endsection
@push('js')<script src="{{ asset('assets/plugins/datatable/datatables.min.js') }}"></script>
<script>
    jQuery(function($){let loading=false,append=false,previousRows=[],scrollPosition=0;let viewport=$();const table=$('#employee-table').DataTable({processing:true,serverSide:true,deferRender:true,searchDelay:500,scrollY:'480px',scrollX:true,scrollCollapse:true,pageLength:25,lengthChange:false,dom:'frti',order:[[1,'asc']],preDrawCallback:function(){loading=true;if(append){previousRows=$('#employee-table tbody tr').detach().toArray();scrollPosition=viewport.scrollTop();}else{previousRows=[];viewport.scrollTop(0);}},drawCallback:function(){viewport=$(this.api().table().container()).find('.dataTables_scrollBody');if(append){$('#employee-table tbody').prepend(previousRows);viewport.scrollTop(scrollPosition);this.api().columns.adjust();}append=false;loading=false;},ajax:{url:{{ Illuminate\Support\Js::from(route('db.staff')) }},data:function(d){d.status=$('#employee-status').val();d.jabatan_id=$('#employee-position').val();}},columnDefs:[{targets:0,orderable:false,searchable:false,render:function(data,type,row,meta){return meta.settings._iDisplayStart+meta.row+1;}},{targets:[6,7],orderable:false,searchable:false}],language:{search:'Cari pegawai:',lengthMenu:'Tampilkan _MENU_ pegawai',info:'_START_–_END_ dari _TOTAL_ pegawai',infoEmpty:'Belum ada pegawai',infoFiltered:'(dari _MAX_ pegawai)',zeroRecords:'Tidak ada pegawai yang sesuai',emptyTable:'Belum ada pegawai',processing:'Memuat pegawai…',paginate:{previous:'Sebelumnya',next:'Berikutnya',first:'Pertama',last:'Terakhir'}}});viewport.on('scroll',function(){if(!loading&&this.scrollTop+this.clientHeight>=this.scrollHeight-80){const info=table.page.info();if(info.page+1<info.pages){append=true;table.page('next').draw('page');}}});$('#employee-status, #employee-position').on('change',function(){append=false;table.ajax.reload();});});
</script>@endpush
