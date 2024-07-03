@extends('layouts.app')
@section('content')
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1><u>GROUP WHATSAPP</u></h1>
        </div>
    </div>
    <div class="flex-row justify-content-between mt-3">
        <div class="col-md-6">
            <table class="table" id="data-table">
                <tr>
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td><a href="{{route('pengaturan')}}"><img src="{{asset('images/pengaturan.svg')}}" alt="dokumen" width="30">
                            Pengaturan</a></td>
                </tr>
            </table>
        </div>
    </div>
</div>
{{-- if error has any --}}
@if ($errors->any())
<div class="container">
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <strong>Whoops!</strong> Ada kesalahan saat input data, yaitu:
        <ul>
            @foreach ($errors->all() as $error)
            <li><strong>{{ $error }}</strong></li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
</div>
@endif
{{-- if success has any --}}
@include('pengaturan.wa.edit')
<div class="container mt-5 table-responsive">
    <table class="table table-bordered table-hover" id="data">
        <thead class="table-warning bg-gradient">
            <tr>
                <th class="text-center align-middle" style="width: 5%">NO</th>
                <th class="text-center align-middle">UNTUK</th>
                <th class="text-center align-middle">NAMA GROUP</th>
                <th class="text-center align-middle">ACT</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($data as $d)
            <tr>
                <td class="text-center align-middle">{{$loop->iteration}}</td>
                <td class="text-center align-middle">{{strtoupper($d->untuk)}}</td>
                <td class="text-center align-middle">{{$d->group_id}}</td>
                <td class="text-center align-middle">
                    <div class="d-flex justify-content-center">
                        <button type="button" class="btn btn-primary m-2" data-bs-toggle="modal"
                            data-bs-target="#editWa" onclick="editWa({{$d}}, {{$d->id}})"><i
                                class="fa fa-edit"></i></button>
                    </div>

                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

@endsection
@push('css')
<link href="{{asset('assets/css/dt.min.css')}}" rel="stylesheet">
@endpush
@push('js')
<script src="{{asset('assets/js/cleave.min.js')}}"></script>
<script src="{{asset('assets/js/dt5.min.js')}}"></script>
<script>

    function funSelect(){
        var obj = document.getElementById("edit_nama_group");
        var text = obj.options[obj.selectedIndex].text;
        document.getElementById('group_id').value = text;
    }


    function editWa(data, id) {
        document.getElementById('edit_untuk').value = data.untuk.toUpperCase();

        // ajax get_group_wa
        $.ajax({
            url: "{{ route('pengaturan.wa.get-group-wa') }}",
            type: 'get',
            dataType: 'json',
            beforeSend: function() {
                // Show loading animation
                $('#spinner').show();
                // hide modal editForm
                // $('#editForm').modal('hide');
            },
            success: function (response) {

                // empty edit_nama_group
                $("#edit_nama_group").empty();
                $("#edit_nama_group").append('<option value="">Pilih Group</option>');
                var len = 0;
                if (response != null) {
                    len = response.length;
                }
                if (len > 0) {
                    // Read data and create <option >
                    for (var i = 0; i < len; i++) {
                        var name = response[i]['id'];
                        var group = response[i]['name'];
                        var option = "<option value='" + name + "'>" + group + "</option>";
                        $("#edit_nama_group").append(option);
                        document.getElementById('group_id').value = group;
                    }
                }

                // Update the form's action URL with the correct ID
                document.getElementById('editForm').action = '/pengaturan/wa/' + id + '/update';

            },
            complete: function() {
                // Hide loading animation
                $('#spinner').hide();
            }
        });
    }

    $('#data').DataTable({
        paging: false,
        scrollCollapse: true,
        scrollY: "550px",
    });

    $('#editForm').submit(function(e){
            e.preventDefault();
            Swal.fire({
                title: 'Apakah data sudah benar?',
                text: "Pastikan data sudah benar sebelum disimpan!",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, simpan!'
                }).then((result) => {
                if (result.isConfirmed) {
                    $('#spinner').show();
                    this.submit();
                }
            })
        });

</script>
@endpush
