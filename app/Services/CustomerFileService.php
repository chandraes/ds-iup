<?php

namespace App\Services;

use App\Models\db\Konsumen;
use App\Models\db\KonsumenDoc;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Throwable;

class CustomerFileService
{
    /** @param array{konsumen_id: int|string, nama: string, barang_unit_id?: int|string|null} $data */
    public function storeDocument(array $data, UploadedFile $file): void
    {
        $path = $file->store('konsumen_docs', 'local');
        if (! $path) {
            throw new RuntimeException('Customer document could not be stored.');
        }
        try {
            DB::transaction(fn () => KonsumenDoc::create([...$data, 'file_path' => $path]), 3);
        } catch (Throwable $exception) {
            $this->removeUnusedFile($path);
            throw $exception;
        }
    }

    public function deleteDocument(KonsumenDoc $document): void
    {
        $path = DB::transaction(function () use ($document): ?string {
            $locked = KonsumenDoc::whereKey($document->id)->lockForUpdate()->firstOrFail();
            $path = $locked->file_path;
            $locked->delete();

            return $path;
        }, 3);
        DB::afterCommit(fn () => $this->removeUnusedFile($path));
    }

    public function replaceKtp(Konsumen $customer, UploadedFile $file): void
    {
        $path = $file->store('konsumen', 'local');
        if (! $path) {
            throw new RuntimeException('Customer KTP could not be stored.');
        }
        try {
            $old = DB::transaction(function () use ($customer, $path): ?string {
                $locked = Konsumen::whereKey($customer->id)->lockForUpdate()->firstOrFail();
                $old = $locked->upload_ktp;
                $locked->update(['upload_ktp' => $path]);

                return $old;
            }, 3);
        } catch (Throwable $exception) {
            $this->removeUnusedFile($path);
            throw $exception;
        }
        DB::afterCommit(fn () => $this->removeUnusedFile($old));
    }

    private function removeUnusedFile(?string $path): void
    {
        if (! $path) {
            return;
        }
        try {
            if (! preg_match('~\\A(?:konsumen|konsumen_docs)/[^/]+\\z~', $path) || str_contains($path, '..')) {
                throw new RuntimeException('Unsafe customer cleanup path.');
            }
            if (! Storage::disk('local')->delete($path)) {
                throw new RuntimeException('Customer file cleanup failed.');
            }
        } catch (Throwable $exception) {
            Log::warning('Customer private file requires cleanup', ['path' => $path, 'exception' => $exception->getMessage()]);
        }
    }
}
