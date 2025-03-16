<div class="modal fade" id="modalHistoriCicilan{{$d->id}}" tabindex="-1" data-bs-backdrop="static"
    data-bs-keyboard="false" role="dialog" aria-labelledby="historiCicilanTitle" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="historiCicilanTitle">
                    Histori Cicilan
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-lg-12">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th class="text-center align-middle">Tanggal</th>
                                    <th class="text-center align-middle">Nominal</th>
                                    @if ($d->kas_ppn == 1)
                                    <th class="text-center align-middle">Ppn</th>
                                    @endif

                                    <th class="text-center align-middle">Total</th>
                                </tr>
                            </thead>
                          <tbody>
                            @foreach ($d->invoice_jual_cicil as $i)
                            <tr>
                                <td class="text-center align-middle">{{$i->tanggal}}</td>
                                <td class="text-end align-middle">{{$i->nf_nominal}}</td>
                                @if ($d->kas_ppn == 1)
                                <td class="text-end align-middle">{{$i->nf_ppn}}</td>
                                @endif
                                <td class="text-end align-middle">{{$i->nf_total}}</td>
                            </tr>
                            @endforeach
                          </tbody>

                        </table>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
