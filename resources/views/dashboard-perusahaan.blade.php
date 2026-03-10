<div class="row justify-content-left">
    <h3>DATABASE</h3>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.konsumen')}}" class="text-decoration-none">
            <img src="{{asset('images/customer.svg')}}" alt="" width="70">
            <h5 class="mt-2">KONSUMEN</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.sales')}}" class="text-decoration-none">
            <img src="{{asset('images/karyawan.svg')}}" alt="" width="70">
            <h5 class="mt-2">KARYAWAN</h5>
        </a>
    </div>
    @if ($barang_ppn)
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.stok-ppn')}}" class="text-decoration-none">
            <img src="{{asset('images/stok-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">STOK & HARGA JUAL BARANG<br>PPN</h5>
        </a>
    </div>
    @endif
    @if ($barang_non_ppn)
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.stok-non-ppn')}}" class="text-decoration-none">
            <img src="{{asset('images/stok-non-ppn.svg')}}" alt="" width="70">
            <h5 class="mt-2">STOK & HARGA JUAL BARANG NON PPN</h5>
        </a>
    </div>
    @endif
</div>
<hr>
<div class="row justify-content-left">
    <h3>RETUR</h3>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.stok-retur')}}" class="text-decoration-none">
            <img src="{{asset('images/retur-send.svg')}}" alt="" width="70">
            <h5 class="mt-3">KIRIM RETUR SUPPLIER
                @if ($sr > 0)
                <span class="text-danger">({{$sr}})</span>
                @endif
            </h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.penyelesaian-retur.index')}}" class="text-decoration-none">
            <img src="{{asset('images/retur-done.svg')}}" alt="" width="70">
            <h5 class="mt-3">PENYELESAIAN RETUR SUPPLIER
                @if ($ps > 0)
                <span class="text-danger">({{$ps}})</span>
                @endif
            </h5>
        </a>
    </div>
</div>
<hr>
<div class="row justify-content-left">
    <h3>REKAP</h3>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.selling-out')}}" class="text-decoration-none">
            <img src="{{asset('images/selling-out.svg')}}" alt="" width="70">
            <h5 class="mt-2">SELLING OUT</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.order')}}" class="text-decoration-none">
            <img src="{{asset('images/order.svg')}}" alt="" width="70">
            <h5 class="mt-2">ORDER</h5>
        </a>
    </div>
      <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('perusahaan.omset-bulanan-konsumen')}}" class="text-decoration-none">
            <img src="{{asset('images/omset-tahunan-konsumen.svg')}}" alt="" width="70">
            <h5 class="mt-2">OMSET KONSUMEN BULANAN</h5>
        </a>
    </div>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('statistik.omset-tahunan-konsumen')}}" class="text-decoration-none">
            <img src="{{asset('images/omset-tahunan-k.svg')}}" alt="" width="70">
            <h5 class="mt-2">OMSET KONSUMEN TAHUNAN</h5>
        </a>
    </div>
</div>
{{-- <div class="row justify-content-left">
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
</div> --}}
{{--
<hr>
<div class="row justify-content-left">
    <h3>STATISTIK</h3>
    <div class="col-lg-2 col-md-2 col-sm-4 my-4 text-center">
        <a href="{{route('sales.omset-harian')}}" class="text-decoration-none">
            <img src="{{asset('images/omset-sales.svg')}}" alt="" width="70">
            <h5 class="mt-2">OMSET HARIAN SALES</h5>
        </a>
    </div>
</div> --}}
