<div class="modal fade" id="keranjangModal" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="keranjangTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="keranjangTitle">
                    Keranjang PPn Masukan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered table-hover" id="keranjangTable">
                    <thead>
                        <tr>
                            <th class="text-center align-middle">No</th>
                            <th class="text-center align-middle">Nota</th>
                            <th class="text-center align-middle">Faktur</th>
                            <th class="text-center align-middle">Nominal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($keranjangData as $d)
                        <tr>
                            <td class="text-center align-middle">{{$loop->iteration}}</td>
                            <td class="text-center align-middle">
                                @if ($d->invoiceBelanja)
                                <a href="{{route('billing.invoice-supplier.detail', ['invoice' => $d])}}">
                                    {{$d->invoiceBelanja->kode}}
                                </a>
                                @endif
                            </td>
                            <td class="text-center align-middle">
                                {{$d->no_faktur}}
                            </td>
                            <td class="text-end align-middle">
                                {{$d->nf_nominal}}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
                <button type="button" class="btn btn-primary">Lanjutkan</button>
            </div>
        </div>
    </div>
</div>

