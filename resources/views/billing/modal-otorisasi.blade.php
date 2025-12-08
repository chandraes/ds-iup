<div class="modal fade" id="modalOtorisasi" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="titleOtorisasi" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titleOtorisasi">
                    Otorisasi Pembelian
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{route('billing.otorisasi-pembelian')}}" method="get" id="formOtorisasi">
                    <div class="mb-3">
                        <label for="asistenId" class="form-label">Pilih Asisten Admin</label>
                        <select class="form-select" name="asistenId" id="asistenId">
                            <option value="" selected>-- Pilih Salah Satu --</option>
                            @foreach ($asistenAdm as $asist)
                            <option value="{{$asist->id}}">{{$asist->name}} {{$asist->keranjang_beli_count > 0 ?
                                "($asist->keranjang_beli_count)" : ''}}</option>
                            @endforeach
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batalkan
                </button>
                <button type="submit" form="formOtorisasi" class="btn btn-primary">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>
