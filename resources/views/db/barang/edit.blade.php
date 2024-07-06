<div class="modal fade" id="editModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog"
    aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">
                    Edit Type
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="post" id="editForm">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row">
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="barang_unit_id" class="form-label">UNIT</label>
                            <select class="form-select" name="barang_unit_id" id="edit_barang_unit_id" required onchange="getTypeEdit()">
                                <option value="" disabled>-- Pilih Salah Satu --</option>
                                @foreach ($units as $k)
                                <option value="{{$k->id}}">{{$k->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="barang_type_id" class="form-label">TYPE</label>
                            <select class="form-select" name="barang_type_id" id="edit_barang_type_id" required>
                                <option value="">-- Pilih Salah Satu --</option>
                                @foreach ($data as $type)
                                <option value="{{$type->id}}">{{$type->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="barang_kategori_id" class="form-label">KATEGORI BARANG</label>
                            <select class="form-select" name="barang_kategori_id" id="edit_barang_kategori_id" required>
                                <option value="" disabled>-- Pilih Salah Satu --</option>
                                @foreach ($kategori as $i)
                                <option value="{{$i->id}}">{{$i->nama}}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="nama" class="form-label">NAMA BARANG</label>
                            <input type="text" class="form-control" name="nama" id="edit_nama" aria-describedby="helpId"
                                placeholder="" required>
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="kode" class="form-label">KODE</label>
                            <input type="text" class="form-control" name="kode" id="edit_kode" aria-describedby="helpId"
                                placeholder="">
                        </div>
                        <div class="col-lg-6 col-md-6 mb-3 mt-3">
                            <label for="merk" class="form-label">MERK</label>
                            <input type="text" class="form-control" name="merk" id="edit_merk" aria-describedby="helpId"
                                placeholder="" required>
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
