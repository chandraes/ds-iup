@include('billing.modal-form-beli')
<div class="row">
    <div class="col-md-2 text-center mt-5">
        <a href="#" class="text-decoration-none" data-bs-toggle="modal" data-bs-target="#modalBeli">
            <img src="{{asset('images/form-beli.svg')}}" alt="" width="70">
            <h5 class="mt-3">FORM BELI</h5>
        </a>
    </div>
    <div class="col-md-2 text-center mt-5">
        <a href="{{route('db.stok-all')}}" class="text-decoration-none">
            <img src="{{asset('images/stock-all.svg')}}" alt="" width="70">
            <h5 class="mt-3">STOK &<br>HARGA JUAL<br>BARANG ALL</h5>
        </a>
    </div>
</div>
