@extends('layouts.app')

@section('content')
<div class="container-fluid">
    @include('swal')

    {{-- Navigasi Tombol Utama --}}
    <div class="row justify-content-between mt-3">
        <div class="col-md-12">
            <table class="table">
                <tr class="text-center">
                    <td class="text-center align-middle">
                        <a href="{{route('home')}}">
                            <img src="{{asset('images/dashboard.svg')}}" alt="dashboard" width="30"> Dashboard
                        </a>
                    </td>
                    <td class="text-center align-middle">
                        <a href="{{route('billing')}}">
                            <img src="{{asset('images/billing.svg')}}" alt="dokumen" width="30"> Billing
                        </a>
                    </td>
                    {{-- Tombol Cetak Khusus --}}
                    <td class="text-center align-middle">
                        <button type="button" class="btn btn-success btn-sm px-3" onclick="cetakInvoiceHalamanIni()">
                            <i class="fa fa-print me-1"></i> Cetak Invoice
                        </button>
                    </td>
                </tr>
            </table>
        </div>
    </div>
</div>

{{-- Container Tampilan PDF Canvas --}}
<div class="container-fluid mt-3">
    <div class="row justify-content-center">
        <div class="col-md-10 text-center">

            <div id="loadingPdf" class="my-4">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Memuat PDF...</span>
                </div>
                <p class="mt-2 text-muted">Memuat tampilan invoice...</p>
            </div>

            {{-- Tempat PDF dirender sebagai Canvas (Bukan PDF viewer biasa) --}}
            <div id="pdf-wrapper" style="overflow-x: auto; background-color: #e9ecef; padding: 20px; border-radius: 8px;">
                <canvas id="pdf-canvas" class="shadow-sm" style="background-color: white; border: 1px solid #ccc;"></canvas>
            </div>

        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">

{{-- Style khusus saat pencetakan CTRL+P / Tombol Print --}}
<style>
    /* Mencegah klik kanan dan seleksi teks pada area canvas invoice */
    #pdf-wrapper {
        user-select: none;
        -webkit-user-select: none;
    }

    /* Pengaturan CSS Media Print: Hanya cetak bagian Canvas Invoice saja */
    @media print {
        body * {
            visibility: hidden; /* Sembunyikan seluruh elemen halaman (navbar, tombol, dll) */
        }
        #pdf-canvas, #pdf-canvas * {
            visibility: visible; /* Hanya tampilkan canvas PDF */
        }
        #pdf-canvas {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
            height: auto !important;
        }
    }
</style>
@endpush

@push('js')
{{-- Memuat Library PDF.js dari CDN Mozilla --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.min.js"></script>

<script>
    pdfjsLib.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/3.11.174/pdf.worker.min.js';

    const url = "{{ $pdfUrl }}";
    const pdfPassword = "{{ $pdfPassword }}"; // Password yang dikirim dari controller
    const canvas = document.getElementById('pdf-canvas');
    const ctx = canvas.getContext('2d');

    // Memuat dokumen PDF dengan menyertakan opsi 'password'
    pdfjsLib.getDocument({
        url: url,
        password: pdfPassword // PDF.js otomatis membuka kunci PDF untuk tampilan web
    }).promise.then(function(pdf) {
        pdf.getPage(1).then(function(page) {
            const scale = 1.8;
            const viewport = page.getViewport({ scale: scale });

            canvas.height = viewport.height;
            canvas.width = viewport.width;

            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            page.render(renderContext).promise.then(function() {
                document.getElementById('loadingPdf').style.display = 'none';
            });
        });
    }).catch(function(error) {
        console.error('Gagal memuat PDF:', error);
        document.getElementById('loadingPdf').innerHTML = '<p class="text-danger">Gagal memuat dokumen invoice. (Password salah atau file rusak)</p>';
    });

    function cetakInvoiceHalamanIni() {
        window.print();
    }

    document.addEventListener('contextmenu', function(e) {
        e.preventDefault();
    });
</script>
@endpush
