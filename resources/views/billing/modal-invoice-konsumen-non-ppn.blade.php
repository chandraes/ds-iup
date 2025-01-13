<div class="modal fade" id="modalKonsumenNonPpn" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="titleNonPpn" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="titleNonPpn">
                    Invoice Konsumen Non PPN
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-6 text-center mt-5">
                        <a href="{{route('billing.invoice-konsumen.non-ppn.titipan')}}" class="text-decoration-none">
                            <img src="{{asset('images/invoice-titipan.svg')}}" alt="" width="70">
                            <h4 class="mt-3">TITIPAN
                                @if ($iktn > 0)
                                <span class="text-danger">({{$iktn}})</span>
                                @endif
                            </h4>
                        </a>
                    </div>
                    <div class="col-md-6 text-center mt-5">
                        <a href="{{route('billing.invoice-konsumen.non-ppn')}}" class="text-decoration-none">
                            <img src="{{asset('images/invoice-tempo.svg')}}" alt="" width="70">
                            <h4 class="mt-3">TEMPO
                                @if ($ikn-$iktn > 0)
                                <span class="text-danger">({{$ikn-$iktn}})</span>
                                @endif
                            </h4>
                        </a>
                    </div>
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Batalkan
                </button>
                <button type="button" class="btn btn-primary">Save</button>
            </div>
        </div>
    </div>
</div>
