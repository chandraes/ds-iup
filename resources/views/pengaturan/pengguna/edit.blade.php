<div class="modal fade" id="editUser-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false" role="dialog" aria-labelledby="editUserTitle-{{$d->id}}" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserTitle-{{$d->id}}">Modal title</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pengaturan.akun.update', ['akun' => $d->id]) }}" method="post" id="editForm-{{$d->id}}">
                @csrf
                @method('patch')
                <div class="modal-body">
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Nama</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{$d->name}}">
                            @error('name')
                            <span class="invalid-feedback text-danger" role="alert">
                                <strong>{{ $message }}</strong>
                            </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Username</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" value="{{$d->username}}">
                            @error('username')
                                <span class="invalid-feedback text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-md-3 form-label" for="example-email">Email</label>
                        <div class="col-md-9">
                            <input type="email" id="example-email" name="email" class="form-control @error('email') is-invalid @enderror" value="{{$d->email}}">
                            @error('email')
                                <span class="invalid-feedback text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Password</label>
                        <div class="col-md-9">
                            <input type="password" class="form-control @if ($errors->has('password'))
                            is-invalid
                        @endif" name="password" placeholder="*************">
                            @error('password')
                                <span class="invalid-feedback text-danger" role="alert">
                                    <strong>{{ $message }}</strong>
                                </span>
                            @enderror
                        </div>
                    </div>
                    <div class="row mb-4">
                        <label for="" class="col-md-3 form-label">Role</label>
                        <div class="col-md-9">
                            <select class="form-select" name="role" id="role" required>
                                <option value="">-- Pilih Role --</option>
                                <option value="admin" @if($d->role == 'admin') selected @endif>Admin</option>
                                <option value="user" @if($d->role == 'user') selected @endif>User</option>
                                <option value="investor" @if($d->role == 'investor') selected @endif>Investor</option>
                            </select>
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
<script>
    $('#editForm-{{$d->id}}').submit(function(e){
           e.preventDefault();
           Swal.fire({
               title: 'Apakah data yakin?',
               icon: 'warning',
               showCancelButton: true,
               confirmButtonColor: '#3085d6',
               cancelButtonColor: '#6c757d',
               confirmButtonText: 'Ya!'
               }).then((result) => {
               if (result.isConfirmed) {
                   $('#spinner').show();
                   this.submit();
               }
           })
       });
</script>
