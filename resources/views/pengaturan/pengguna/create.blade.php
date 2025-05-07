<div class="modal fade" id="createUser" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="createUserTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="createUserTitle">Tambah Akun</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('pengaturan.akun.store') }}" method="post" id="createForm">
                @csrf
                <div class="modal-body">
                    <div class="row mb-4">
                        <label class="col-md-3 form-label">Name</label>
                        <div class="col-md-9">
                            <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" placeholder="Full Name">
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
                            <input type="text" class="form-control @error('username') is-invalid @enderror" name="username" placeholder="Username" value="{{ old('username') }}">
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
                            <input type="email" id="example-email" name="email" class="form-control @error('email') is-invalid @enderror" placeholder="Email" value="{{ old('email') }}">
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
                            <div class="input-group">
                                <input type="password" class="form-control @error('password') is-invalid @enderror" name="password" id="password" placeholder="*************">
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <span class="fa fa-eye" id="eyeIcon"></span>
                                </button>
                            </div>
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
                            <select class="form-select" name="role" id="role" required onchange="checkRole()">
                                <option value="" selected>-- Pilih Role --</option>
                                @foreach ($roles as $role)
                                    <option value="{{ $role }}">{{ Str::upper($role) }}</option>

                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row mb-4" id="karyawan_div" hidden>
                        <label for="" class="col-md-3 form-label">Karyawan</label>
                        <div class="col-md-9">
                            <select class="form-select" name="karyawan_id" id="karyawan_id" required>
                                <option value="">-- Pilih database Karyawan --</option>
                                @foreach ($karyawan as $k)
                                <option value="{{ $k->id }}">{{ Str::upper($k->nama) }}</option>
                                @endforeach
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
@push('js')
<script>
    document.getElementById('togglePassword').addEventListener('click', function (e) {
        // Toggle the type attribute
        const password = document.getElementById('password');
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        // Toggle the eye icon
        const eyeIcon = document.getElementById('eyeIcon');
        eyeIcon.classList.toggle('fa-eye');
        eyeIcon.classList.toggle('fa-eye-slash');
    });

    function checkRole() {
        var role = document.getElementById('role').value;
        var karyawanDiv = document.getElementById('karyawan_div');
        if (role == 'sales') {
            karyawanDiv.removeAttribute('hidden');
            document.getElementById('karyawan_id').setAttribute('required', true);
        } else {
            karyawanDiv.setAttribute('hidden', true);
            document.getElementById('karyawan_id').removeAttribute('required');
            document.getElementById('karyawan_id').value = '';
        }
    }
</script>
@endpush
