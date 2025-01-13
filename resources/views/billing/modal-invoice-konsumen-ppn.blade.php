<div
class="modal fade"
id="modalKonsumenPpn"
tabindex="-1"
data-bs-backdrop="static"
data-bs-keyboard="false"

role="dialog"
aria-labelledby="modalTitleId"
aria-hidden="true"
>
<div
    class="modal-dialog modal-dialog-scrollable modal-dialog-centered"
    role="document"
>
    <div class="modal-content">
        <div class="modal-header">
            <h5 class="modal-title" id="modalTitleId">
                Invoice Konsumen PPN
            </h5>
            <button
                type="button"
                class="btn-close"
                data-bs-dismiss="modal"
                aria-label="Close"
            ></button>
        </div>
        <div class="modal-body">
            <div class="row">
                <div class="col-md-6 text-center mt-5">
                    <a href="{{route('billing.invoice-konsumen.titipan')}}" class="text-decoration-none">
                        <img src="{{asset('images/invoice-titipan.svg')}}" alt="" width="70">
                        <h4 class="mt-3">TITIPAN
                            @if ($ikt > 0)
                            <span class="text-danger">({{$ikt}})</span>
                            @endif
                        </h4>
                    </a>
                </div>
                <div class="col-md-6 text-center mt-5">
                    <a href="{{route('billing.invoice-konsumen')}}" class="text-decoration-none">
                        <img src="{{asset('images/invoice-tempo.svg')}}" alt="" width="70">
                        <h4 class="mt-3">TEMPO
                            @if ($ik-$ikt > 0)
                            <span class="text-danger">({{$ik-$ikt}})</span>
                            @endif
                        </h4>
                    </a>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button
                type="button"
                class="btn btn-secondary"
                data-bs-dismiss="modal"
            >
                Batalkan
            </button>

        </div>
    </div>
</div>
</div>
