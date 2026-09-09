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

            {{-- Container dinamis untuk menampung seluruh halaman canvas --}}
            <div id="pdf-wrapper" style="overflow-x: auto; background-color: #e9ecef; padding: 20px; border-radius: 8px;">
                <div id="pdf-container" class="d-flex flex-column align-items-center"></div>
            </div>

        </div>
    </div>
</div>
@endsection

@push('css')
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.bootstrap5.css')}}">
<link rel="stylesheet" href="{{asset('assets/plugins/select2/select2.min.css')}}">

<style>
    /* Mencegah klik kanan dan seleksi teks pada area canvas invoice */
    #pdf-wrapper {
        user-select: none;
        -webkit-user-select: none;
    }

    /* Styling canvas di layar monitor */
    .pdf-page-canvas {
        background-color: white;
        border: 1px solid #ccc;
        margin-bottom: 20px;
    }

    /* Pengaturan CSS Media Print untuk Eliminasi Halaman Kosong Extra */
    @media print {
        @page {
            margin: 0;
            size: auto;
        }

        html, body {
            background-color: white !important;
            margin: 0 !important;
            padding: 0 !important;
            height: auto !important;
            overflow: visible !important;
        }

        /* Sembunyikan elemen antarmuka web dan indikator loading */
        body * {
            visibility: hidden !important;
        }

        #loadingPdf {
            display: none !important;
        }

        /* Tampilkan hanya wadah penampung canvas */
        #pdf-wrapper, #pdf-container {
            background-color: transparent !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            box-shadow: none !important;
            /* Menghilangkan spasi teks tersembunyi */
            font-size: 0 !important;
            line-height: 0 !important;
        }

        #pdf-container, #pdf-container * {
            visibility: visible !important;
        }

        #pdf-container {
            position: absolute;
            left: 0;
            top: 0;
            width: 100% !important;
        }

        /* Pengaturan canvas standar (halaman 1 hingga n-1) */
        .pdf-page-canvas {
            margin: 0 auto !important;
            padding: 0 !important;
            border: none !important;
            outline: none !important;
            box-shadow: none !important;
            width: 100% !important;
            height: auto !important;
            display: block !important;
            vertical-align: bottom !important;
            page-break-after: always !important;
            break-after: page !important;
        }

        /* Pengaturan khusus canvas halaman terakhir */
        .pdf-page-canvas:last-child {
            page-break-after: avoid !important;
            break-after: avoid !important;
            page-break-inside: avoid !important;
            break-inside: avoid !important;
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
    const pdfPassword = "{{ $pdfPassword }}";
    const container = document.getElementById('pdf-container');

    // Memuat dokumen PDF dengan menyertakan opsi 'password'
    pdfjsLib.getDocument({
        url: url,
        password: pdfPassword
    }).promise.then(async function(pdf) {
        // Kosongkan container sebelum merender
        container.innerHTML = '';

        // Iterasi/Looping dari halaman 1 sampai halaman terakhir
        for (let pageNum = 1; pageNum <= pdf.numPages; pageNum++) {
            const page = await pdf.getPage(pageNum);
            const scale = 1.8;
            const viewport = page.getViewport({ scale: scale });

            // Buat elemen canvas baru untuk setiap halaman
            const canvas = document.createElement('canvas');
            canvas.className = 'pdf-page-canvas';
            const ctx = canvas.getContext('2d');

            canvas.height = viewport.height;
            canvas.width = viewport.width;

            // Masukkan canvas ke dalam container
            container.appendChild(canvas);

            // Render halaman PDF ke canvas masing-masing
            const renderContext = {
                canvasContext: ctx,
                viewport: viewport
            };

            await page.render(renderContext).promise;
        }

        // Sembunyikan indikator loading setelah semua halaman selesai dirender
        document.getElementById('loadingPdf').style.display = 'none';

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
