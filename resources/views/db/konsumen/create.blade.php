<div class="modal fade" id="createInvestor" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="investorTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="investorTitle">Tambah Konsumen</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('db.konsumen.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="nama" class="form-label">Nama Perusahaan / Perorangan</label>
                            <input type="text" class="form-control" name="nama" id="nama" aria-describedby="helpId" value="{{old('nama')}}"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="cp" class="form-label">Contact Person</label>
                            <input type="text" class="form-control" name="cp" id="cp" aria-describedby="helpId" value="{{old('cp')}}"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="no_hp" class="form-label">No HP</label>
                            <input type="text" class="form-control" name="no_hp" id="no_hp" aria-describedby="helpId" value="{{old('no_hp')}}"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="no_kantor" class="form-label">No Tlp Kantor</label>
                            <input type="text" class="form-control" name="no_kantor" id="no_kantor" aria-describedby="helpId" value="{{old('no_kantor')}}"
                                placeholder="">
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="npwp" class="form-label">NPWP</label>
                            <input type="text" class="form-control" name="npwp" id="npwp" aria-describedby="helpId" value="{{old('npwp')}}"
                                placeholder="" required>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="npwp" class="form-label">Sistem Pembayaran</label>
                            <select name="pembayaran" id="pembayaran" required class="form-select" onchange="pembayaranFun()">
                                <option value="" disabled selected>-- Pilih Sistem Pembayaran --</option>
                                <option value="1">Cash</option>
                                <option value="2">Tempo</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3" id="divPlafon">
                            <label for="plafon" class="form-label">Limit Plafon</label>
                            <div class="input-group mb-3">
                                <span class="input-group-text" id="basic-addon1">Rp</span>
                                <input type="text" class="form-control" name="plafon" id="plafon" required data-thousands="." value="{{old('plafon')}}">
                              </div>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3" id="divTempo">
                            <label for="tempo_hari" class="form-label">Tempo</label>
                            <div class="input-group mb-3">
                                <input type="number" class="form-control" name="tempo_hari" id="tempo_hari" required value="{{old('tempo_hari')}}">
                                <span class="input-group-text" id="basic-addon1">Hari</span>
                              </div>
                        </div>

                    </div>
                    <hr>
                    <div class="row">
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="kota" class="form-label">Provinsi</label>
                            <select name="provinsi_id" id="provinsi_id" class="form-select" onchange="getKabKota()" required>
                                <option value="">-- Pilih Provinsi --</option>
                                @foreach ($provinsi as $p)
                                <option value="{{$p->id}}">{{$p->nama_wilayah}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="kota" class="form-label">Kabupaten / Kota</label>
                            <select name="kabupaten_kota_id" id="kabupaten_kota_id" class="form-select" onchange="getKecamatan()" required>
                                <option value="">-- Pilih Kabupaten / Kota --</option>
                            </select>
                        </div>
                        <div class="col-md-4 col-sm-6 mb-3">
                            <label for="kota" class="form-label">Kecamatan</label>
                            <select name="kecamatan_id" id="kecamatan_id" class="form-select">
                                <option value="">-- Pilih Kecamatan --</option>
                            </select>
                        </div>
                        {{-- <div class="col-md-4 col-sm-6 mb-3">
                            <label for="kota" class="form-label">Kota</label>
                            <input type="text" class="form-control" name="kota" id="kota" aria-describedby="helpId" value="{{old('kota')}}"
                                placeholder="" required>
                        </div> --}}
                        <div class="col-md-12 col-sm-12 mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea name="alamat" id="alamat" cols="30" rows="5" class="form-control">{{old('alamat')}}</textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
<script>
    function pembayaranFun() {
        var pembayaran = document.getElementById('pembayaran').value;
        if (pembayaran == 1) {
            document.getElementById('divPlafon').style.display = 'none';
            document.getElementById('divTempo').style.display = 'none';
            documnet.getElementById('plafon').value = '';
            documnet.getElementById('tempo_hari').value = '';
        } else {
            document.getElementById('divPlafon').style.display = 'block';
            document.getElementById('divTempo').style.display = 'block';
        }
    }

    function getKabKota(){
        var provinsi = document.getElementById('provinsi_id').value;
        $('#kabupaten_kota_id').empty();
        $('#kabupaten_kota_id').append('<option value="" selected> -- Pilih Kabupaten / Kota -- </option>');
        $('#kecamatan_id').empty();
        $('#kecamatan_id').append('<option value="" selected> -- Pilih Kecamatan -- </option>');
        // ajax request to get-kab-kota
        $.ajax({
            url: '{{route('get-kab-kota')}}',
            type: 'GET',
            data: {
                provinsi: provinsi
            },
            success: function(data) {
                if (data.status === 'success') {
                    $('#kabupaten_kota_id').empty();
                    $('#kabupaten_kota_id').append('<option value=""> -- Pilih Kabupaten / Kota -- </option>');
                    // append to option with select id kabupaten_kota_id
                    $.each(data.data, function(index, value){
                        $('#kabupaten_kota_id').append('<option value="'+value.id+'">'+value.nama_wilayah+'</option>');
                    });

                } else {
                    // swal show error message\
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: textStatus
                    });
            }
        });
    }

    function getKecamatan(){
        var kab = document.getElementById('kabupaten_kota_id').value;
        $('#kecamatan_id').empty();
        $('#kecamatan_id').append('<option value="" selected> -- Pilih Kecamatan -- </option>');
        // ajax request to get-kab-kota
        $.ajax({
            url: '{{route('get-kecamatan')}}',
            type: 'GET',
            data: {
                kab: kab
            },
            success: function(data) {
                if (data.status === 'success') {
                    $('#kecamatan_id').empty();
                    $('#kecamatan_id').append('<option value=""> -- Pilih Kecamatan -- </option>');
                    // append to option with select id kabupaten_kota_id
                    $.each(data.data, function(index, value){
                        $('#kecamatan_id').append('<option value="'+value.id+'">'+value.nama_wilayah+'</option>');
                    });

                } else {
                    // swal show error message\
                    Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: data.message
                    });
                }
            },
            error: function(jqXHR, textStatus, errorThrown) {
                Swal.fire({
                        icon: 'error',
                        title: 'Oops...',
                        text: textStatus
                    });
            }
        });
    }
</script>
@endpush
