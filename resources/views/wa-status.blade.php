<div class="col-md-5 mb-3">
    <div class="card shadow-sm" style="max-width: 300px;">
        <div class="card-body d-flex align-items-center p-2">
            <!-- Bulatan Status -->
            <div class="d-flex justify-content-center align-items-center rounded-circle bg-success text-white"
                 id="connection-status-indicator" style="width: 12px; height: 12px;">
            </div>

            <!-- Status Teks -->
            <div class="ms-2 small">
                <span id="connection-status-text" class="truncated-text" title="Connected">Connected</span>
            </div>

            <!-- Tombol Refresh -->
            <button class="btn btn-outline-primary btn-sm ms-auto" id="refresh-status" onclick="refreshStatus()">
                Refresh
            </button>
        </div>
    </div>
</div>

<style>
    .truncated-text {
        display: inline-block;
        max-width: 150px; /* Adjust as needed */
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
        vertical-align: bottom;
    }

    .truncated-text:hover::after {
        content: attr(title);
        white-space: normal;
        position: absolute;
        background-color: #fff;
        border: 1px solid #ccc;
        padding: 5px;
        z-index: 1000;
        max-width: 300px; /* Adjust as needed */
    }
</style>

@push('js')
    <script>
        function refreshStatus() {
            const url = "{{ route('status-wa') }}"; // Endpoint API
            const indicator = document.getElementById('connection-status-indicator');
            const statusText = document.getElementById('connection-status-text');

            // Indikator status memuat
            indicator.className = 'd-flex justify-content-center align-items-center rounded-circle bg-warning text-white';
            statusText.textContent = 'Checking...';

            // Lakukan permintaan AJAX
            fetch(url)
                .then(response => response.json())
                .then(data => {
                    if (data.success === true && data.data.device.status === 'connected') {
                        indicator.className = 'd-flex justify-content-center align-items-center rounded-circle bg-success text-white';
                        statusText.textContent = 'WhatsApp Terhubung';
                        statusText.setAttribute('title', 'WhatsApp Terhubung');
                    } else if(data.success === true && data.data.device.status !== 'connected') {
                        indicator.className = 'd-flex justify-content-center align-items-center rounded-circle bg-danger text-white';
                        statusText.textContent = "Whatsapp Tidak Terhubung";
                        statusText.setAttribute('title', 'Whatsapp Tidak Terhubung');
                    } else {
                        indicator.className = 'd-flex justify-content-center align-items-center rounded-circle bg-danger text-white';
                        const message = data.message;
                        const truncatedMessage = message.length > 100 ? message.substring(0, 100) + '...' : message;
                        statusText.textContent = truncatedMessage;
                        statusText.setAttribute('title', message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    indicator.className = 'd-flex justify-content-center align-items-center rounded-circle bg-danger text-white';
                    statusText.textContent = 'Error';
                    statusText.setAttribute('title', 'Error');
                });
        }

        // Perbarui status saat halaman dimuat
        document.addEventListener('DOMContentLoaded', refreshStatus);
    </script>
@endpush
