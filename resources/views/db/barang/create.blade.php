<div class="modal fade" id="createModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="createModal" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createModalTitle">
                    Modal title
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{route('db.barang.store')}}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_unit_id" class="form-label">UNIT</label>
                            <select class="form-select" name="barang_unit_id" id="barang_unit_id" required onchange="getType()">
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($units as $k)
                                <option value="{{$k->id}}">{{$k->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_type_id" class="form-label">TYPE</label>
                            <select class="form-select" name="barang_type_id" id="barang_type_id" required>
                                <option selected>-- Pilih Salah Satu --</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="barang_kategori_id" class="form-label">KELOMPOK BARANG</label>
                            <select class="form-select" name="barang_kategori_id" id="barang_kategori_id" required onchange="getNamaBarang()">
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($kategori as $i)
                                <option value="{{$i->id}}">{{$i->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="nama" class="form-label">NAMA BARANG</label>
                            <select class="form-select" name="barang_nama_id" id="barang_nama_id" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                            </select>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="kode" class="form-label">KODE BARANG</label>
                            <input type="text" class="form-control" name="kode" id="kode" aria-describedby="helpId"
                                placeholder="">
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="merk" class="form-label">MERK BARANG</label>
                            <input type="text" class="form-control" name="merk" id="merk" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-lg-3 col-md-3 mb-3 mt-3">
                            <label for="satuan_id" class="form-label">SATUAN BARANG</label>
                            <select class="form-select" name="satuan_id" id="satuan_id" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                @foreach ($satuan as $s)
                                <option value="{{$s->id}}">{{$s->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="nama" class="form-label">PPN / NON PPN</label>
                            <select class="form-select" name="jenis" id="jenis" required>
                                <option value="" disabled selected>-- Pilih Salah Satu --</option>
                                <option value="1">Barang PPN</option>
                                <option value="2">Barang Non PPN</option>
                            </select>
                        </div>
                        <div class="col-lg-12 col-md-12 mb-3 mt-3">
                            <label for="detail_type" class="form-label">KETERANGAN TYPE</label>
                            <select class="form-select" name="detail_type[]" id="detail_type" multiple>
                            </select>
                        </div>
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
        function getType() {
            let unit_id = document.getElementById('barang_unit_id').value;
            // ajax request
            $.ajax({
                url: `{{route('db.barang.get-type')}}`,
                type: 'GET',
                data: {
                    unit_id: unit_id
                },
                success: function (data) {
                    let select = document.getElementById('barang_type_id');
                    let detail_type = document.getElementById('detail_type');
                    if (data.status == 0) {
                        // swal error
                        console.log(data);
                        Swal.fire({
                            title: data.message, // Corrected typo here
                            icon: 'warning',
                        });

                        // clear select
                        select.innerHTML = '';
                        detail_type.innerHTML = '';
                    } else { // Corrected syntax error here

                        select.innerHTML = '';
                        detail_type.innerHTML = '';
                        data.data.forEach(element => {
                            let option = new Option(element.nama, element.id);
                            let optionDetail = new Option(element.nama, element.id);// Simplified option creation
                            select.add(option);
                            detail_type.add(optionDetail);
                        });
                    }
                }
            });
        }

        function getNamaBarang(){
            // get barang_nama based on barang_kategori_id
            let kategori_id = document.getElementById('barang_kategori_id').value;
            // find $kategori.barang_nama
            $.ajax({
                url: `{{route('db.barang.get-barang-nama')}}`,
                type: 'GET',
                data: {
                    kategori_id: kategori_id
                },
                success: function (data) {
                    let select = document.getElementById('barang_nama_id');
                    if (data.status == 0) {
                        // swal error
                        console.log(data);
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
