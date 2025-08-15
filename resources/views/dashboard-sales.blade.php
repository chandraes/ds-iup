
<div class="row justify-content-left">
    <h3>TRANSAKSI</h3>
     <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.konsumen')}}" class="text-decoration-none">
            <img src="{{asset('images/customer.svg')}}" alt="" width="70">
            <h5 class="mt-2">KONSUMEN</h5>
        </a>
    </div>
     <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.check-konsumen')}}" class="text-decoration-none">
            <img src="{{asset('images/check.svg')}}" alt="" width="70">
            <h5 class="mt-2">CHECK INVOICE JATUH TEMPO</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.jual')}}" class="text-decoration-none">
            <img src="{{asset('images/stok-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">STOK &<br>HARGA JUAL<br>BARANG</h5>
        </a>
    </div>
</div>
<hr>
<div class="row justify-content-left">
    <h3>REKAP</h3>
     <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.order', ['kas_ppn' => 1])}}" class="text-decoration-none">
            <img src="{{asset('images/order-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">SALES ORDER<br>BARANG A</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.order', ['kas_ppn' => 0])}}" class="text-decoration-none">
            <img src="{{asset('images/order-non-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">SALES ORDER<br>BARANG B</h5>
        </a>
    </div>
     <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.pre-order')}}" class="text-decoration-none">
            <img src="{{asset('images/order-inden.svg')}}" alt="" width="70">
            <h5 class="mt-2">PRE ORDER</h5>
        </a>
    </div>
</div>
<hr>
<div class="row justify-content-left">
    <h3>STATISTIK</h3>
   <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.omset-harian')}}" class="text-decoration-none">
            <img src="{{asset('images/omset-sales.svg')}}" alt="" width="70">
            <h5 class="mt-2">OMSET HARIAN SALES</h5>
        </a>
    </div>
</div>
