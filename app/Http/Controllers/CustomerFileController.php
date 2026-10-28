<?php

namespace App\Http\Controllers;

use App\Models\db\Konsumen;
use App\Models\db\KonsumenDoc;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CustomerFileController extends Controller
{
    public function ktp(Konsumen $konsumen): BinaryFileResponse
    {
        return $this->serve($konsumen->upload_ktp, true);
    }

    public function document(KonsumenDoc $dokumen): BinaryFileResponse
    {
        return $this->serve($dokumen->file_path, false);
    }

    private function serve(?string $path, bool $inline): BinaryFileResponse
    {
        abort_unless($path && preg_match('~\A(?:konsumen|konsumen_docs)/[^/]+\z~', $path) && ! str_contains($path, '..'), 404);
        abort_unless(Storage::disk('local')->exists($path), 404, 'File konsumen tidak tersedia.');
        $inline = $inline && in_array(Storage::disk('local')->mimeType($path), ['image/jpeg', 'image/png'], true);
        $response = $inline ? response()->file(Storage::disk('local')->path($path)) : response()->download(Storage::disk('local')->path($path));
        $response->headers->set('Cache-Control', 'private, no-store');
        $response->headers->set('X-Content-Type-Options', 'nosniff');

        return $response;
    }
}
