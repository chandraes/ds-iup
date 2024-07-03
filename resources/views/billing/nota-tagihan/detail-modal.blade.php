<div class="modal fade" id="detailInvoice-{{$d->id}}" tabindex="-1" data-bs-backdrop="static" data-bs-keyboard="false"
    role="dialog" aria-labelledby="detailInvoice-{{$d->id}}Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="detailInvoice-{{$d->id}}Label""> Detail Balance </h5>
                <button type=" button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                @if ($d->invoiceTagihanDetails())
                <div class="table-responsive">
                    <table class="table table-bordered table-striped">
                        <thead>
                            <tr>
                                <th class="text-center align-middle"> Tanggal </th>
                                <th class="text-center align-middle"> Deskripsi </th>
                                <th class="text-center align-middle"> Jumlah </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($d->invoiceTagihanDetails as $key => $item)
                            <tr>
                                <td class="text-center align-middle"> {{ $item->tanggal }} </td>
                                <td class="align-middle"> {{ $item->uraian }} </td>
                                <td class="text-end align-middle"> {{ $item->nf_nominal }} </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <th class="text-center align-middle" colspan="2"> Total </th>
                                <th class="text-end align-middle"> {{ number_format($d->invoiceTagihanDetails->sum('nominal'), 0, ',','.') }} </th>
                            </tr>
                        </tfoot>
                    </table>
                @endif
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    Tutup
                </button>
            </div>
        </div>
    </div>
</div>
