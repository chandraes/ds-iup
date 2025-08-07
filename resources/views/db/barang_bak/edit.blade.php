<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document" >
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    Edit Kode & Merk Barang
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm" enctype="multipart/form-data">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_unit_id" class="form-label">KATEGORI PERUSAHAAN</label>
                            <select class="form-select" name="barang_unit_id" id="edit_barang_unit_id" required onchange="getTypeEdit()">
                                <option value="" disabled>-- Pilih Salah Satu --</option>
                                @foreach ($units as $k)
                                <option value="{{$k->id}}">{{$k->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_type_id" class="form-label">BIDANG</label>
                            <select class="form-select" name="barang_type_id" id="edit_barang_type_id" required>
                                <option value="">-- Pilih Salah Satu --</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_kategori_id" class="form-label">KELOMPOK BARANG</label>
                            <select class="form-select" name="barang_kategori_id" id="edit_barang_kategori_id" required onchange="getNamaBarangEdit()">
                                <option value="" disabled>-- Pilih Salah Satu --</option>
                                @foreach ($kategori as $i)
                                <option value="{{$i->id}}">{{$i->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="nama" class="form-label">NAMA BARANG</label>
                            <select class="form-select" name="barang_nama_id" id="edit_barang_nama_id" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                            </select>
                        </div>

                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="kode" class="form-label">KODE BARANG</label>
                            <input type="text" class="form-control" name="kode" id="edit_kode" aria-describedby="helpId"
                                placeholder="">
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="merk" class="form-label">MERK BARANG</label>
                            <input type="text" class="form-control" name="merk" id="edit_merk" aria-describedby="helpId"
                                placeholder="" required>
                        </div>

                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="satuan_id" class="form-label">SATUAN BARANG</label>
                            <select class="form-select" name="satuan_id" id="edit_satuan_id" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($satuan as $s)
                                <option value="{{$s->id}}">{{$s->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                         <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="subpg_id" class="form-label">SUBPG</label>
                            <select class="form-select" name="subpg_id" id="edit_subpg_id">
                                <option value="" selected>-- Pilih Salah Satu --</option>
                                @foreach ($subpg as $sub)
                                <option value="{{$sub->id}}">{{$sub->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="keterangan" class="form-label">KETERANGAN</label>
                            <input type="text" class="form-control" name="keterangan" id="edit_keterangan" aria-describedby="helpId"
                                placeholder="">
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="nama" class="form-label">PPN / NON PPN</label>
                            <select class="form-select" name="jenis" id="edit_jenis" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                <option value="1">Barang PPN</option>
                                <option value="2">Barang Non PPN</option>
                            </select>
                        </div>

                        {{-- <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="detail_type" class="form-label">KETERANGAN TYPE</label>
                            <select class="form-select" name="detail_type[]" id="edit_detail_type" multiple>
                            </select>
                        </div> --}}
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                        Batalkan
                    </button>
                    <button type="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@push('js')
    <script>
        function getTypeEdit() {
            let unit_id = document.getElementById('edit_barang_unit_id').value;
            // ajax request
            $.ajax({
                url: `{{route('db.barang.get-type')}}`,
                type: 'GET',
                data: {
                    unit_id: unit_id
                },
                success: function (data) {
                    let select = document.getElementById('edit_barang_type_id');
                    // let detail_type = document.getElementById('edit_detail_type');
                    if (data.status == 0) {
                        // swal error
                        Swal.fire({
                            title: data.message, // Corrected typo here
                            icon: 'warning',
                        });

                        // clear select
                        select.innerHTML = '';
                        // detail_type.innerHTML = '';
                    } else { // Corrected syntax error here

                        select.innerHTML = '';
                        // detail_type.innerHTML = '';
                        data.data.forEach(element => {
                            let option = new Option(element.nama, element.id);
                            let optionDetail = new Option(element.nama, element.id);// Simplified option creation
                            select.add(option);
                            // detail_type.add(optionDetail);
                        });
                    }
                }
            });
        }

        function getNamaBarangEdit(){
            // get barang_nama based on barang_kategori_id
            let kategori_id = document.getElementById('edit_barang_kategori_id').value;
            // find $kategori.barang_nama
            $.ajax({
                url: `{{route('db.barang.get-barang-nama')}}`,
                type: 'GET',
                data: {
                    kategori_id: kategori_id
                },
                success: function (data) {
                    let select = document.getElementById('edit_barang_nama_id');
                    if (data.status == 0) {
                        // swal error
                        Swal.fire({
                            title: data.message, // Corrected typo here
                            icon: 'warning',
                        });

                        // clear select
                        select.innerHTML = '';
                    } else { // Corrected syntax error here

                        select.innerHTML = '';
                        data.data.forEach(element => {
                            let option = new Option(element.nama, element.id); // Simplified option creation
                            select.add(option);
                        });
                    }
                }
            });
        }
    </script>
@endpush
