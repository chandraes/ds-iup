@extends('layouts.app')

@section('content')
<div class="container py-4">
    {{-- Header & Navigasi --}}
    <div class="row justify-content-center">
        <div class="col-md-12 text-center">
            <h1 class="fw-bold mb-0">
                OTORISASI PEMBELIAN <br> {{$user->name}}
            </h1>
        </div>
    </div>
    <div class="row justify-content-between mt-3">
        <div class="col-md-7">
            <table class="table">
                <tr class="text-center">
                    <td><a href="{{route('home')}}"><img src="{{asset('images/dashboard.svg')}}" alt="dashboard"
                                width="30"> Dashboard</a></td>
                    <td class="text-center align-middle"><a href="{{route('billing')}}"><img
                                src="{{asset('images/billing.svg')}}" alt="dokumen" width="30">
                            Billing</a></td>

                </tr>
            </table>
        </div>
    </div>

    {{-- Main Content Card --}}
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <form action="{{ route('billing.otorisasi-pembelian.keranjang') }}" method="get">
                        <input type="hidden" name="asistenId" value="{{ $user->id }}">

                        <div class="row g-3">
                            {{-- Pilihan Tempo (Menggunakan Floating Label) --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-primary-subtle" name="tempo" id="tempo" required>
                                        {{-- Menghitung jumlah item untuk badge visual --}}
                                        @php
                                        $countCash = $keranjang->where('tempo', 0)->count();
                                        $countTempo = $keranjang->where('tempo', 1)->count();
                                        @endphp
                                        <option value="0">Cash {{ $countCash > 0 ? "($countCash)" : "" }}</option>
                                        <option value="1">Dengan Tempo {{ $countTempo > 0 ? "($countTempo)" : "" }}
                                        </option>
                                    </select>
                                    <label for="tempo">Metode Pembayaran</label>
                                </div>
                            </div>

                            {{-- Pilihan PPN --}}
                            <div class="col-md-6">
                                <div class="form-floating">
                                    <select class="form-select border-primary-subtle" name="jenis" id="jenis"
                                        required>
                                        @php
                                        $countPpn = $keranjang->where('jenis', 1)->count();
                                        $countNonPpn = $keranjang->where('jenis', 0)->count();
                                        @endphp
                                        <option value="1">KAS PPN {{ $countPpn > 0 ? "($countPpn)" : "" }}</option>
                                        <option value="0">KAS NON PPN {{ $countNonPpn > 0 ? "($countNonPpn)" : "" }}
                                        </option>
                                    </select>
                                    <label for="kas_ppn">Tipe KAS</label>
                                </div>
                            </div>
                        </div>

                        {{-- Action Button --}}
                        <div class="d-grid gap-2 mt-4">
                            <button class="btn btn-primary btn-lg py-3 fw-bold shadow-sm" type="submit">
                                Lanjutkan Proses <i class="bi bi-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            {{-- Optional: Info Alert jika keranjang kosong --}}
            @if($keranjang->count() == 0)
            <div class="alert alert-info mt-3 border-0 shadow-sm d-flex align-items-center" role="alert">
                <i class="bi bi-info-circle-fill me-2 fs-5"></i>
                <div>
                    Belum ada data pembelian yang perlu diotorisasi saat ini.
                </div>
            </div>
            @endif
        </div>
    </div>
</div>
@endsection
