<div class="row justify-content-left">
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.stok')}}" class="text-decoration-none">
            <img src="{{asset('images/stok-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">STOK &<br>HARGA JUAL<br>BARANG</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.order', ['kas_ppn' => 1])}}" class="text-decoration-none">
            <img src="{{asset('images/order-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">SALES ORDER<br>PPN</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.order', ['kas_ppn' => 0])}}" class="text-decoration-none">
            <img src="{{asset('images/order-non-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">SALES ORDER<br>NON PPN</h5>
        </a>
    </div>
</div>
