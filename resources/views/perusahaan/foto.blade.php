
<div class="modal fade" id="imageModal" tabindex="-1" aria-labelledby="imageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="imageModalLabel">Foto Barang</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="image-container">
                    <img id="zoomableImage" src="" alt="Foto Barang" class="img-fluid">
                </div>
                <input type="range" id="zoomSlider" class="form-range mt-3" min="1" max="3" step="0.1" value="1">
            </div>
        </div>
    </div>
</div>

<style>
    .image-container {
        overflow: hidden;
        display: inline-block;
    }

    .image-container img {
        transition: transform 0.2s ease;
    }
</style>
