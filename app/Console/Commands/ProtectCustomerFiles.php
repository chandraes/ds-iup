<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class ProtectCustomerFiles extends Command
{
    protected $signature = 'customers:protect-files';

    protected $description = 'Move existing customer KTP and documents to private storage; safe to rerun';

    public function handle(): int
    {
        $moved = 0;
        $missing = 0;
        foreach (['konsumens' => 'upload_ktp', 'konsumen_docs' => 'file_path'] as $table => $column) {
            DB::table($table)->whereNotNull($column)->select(['id', $column])->orderBy('id')->chunkById(100, function ($rows) use ($column, &$moved, &$missing): void {
                foreach ($rows as $row) {
                    $path = $row->{$column};
                    if (! preg_match('~\A(?:konsumen|konsumen_docs)/[^/]+\z~', $path) || str_contains($path, '..')) {
                        throw new RuntimeException('Unsafe customer file path on record '.$row->id);
                    }
                    $public = Storage::disk('public');
                    $private = Storage::disk('local');
                    if (! $public->exists($path)) {
                        if (! $private->exists($path)) {
                            $missing++;
                        }

                        continue;
                    }
                    $sourceHash = hash_file('sha256', $public->path($path));
                    if (! $private->exists($path)) {
                        $stream = $public->readStream($path);
                        try {
                            if (! $private->put($path, $stream)) {
                                throw new RuntimeException('Failed to copy customer file.');
                            }
                        } finally {
                            if (is_resource($stream)) {
                                fclose($stream);
                            }
                        }
                    }
                    if ($sourceHash !== hash_file('sha256', $private->path($path))) {
                        throw new RuntimeException('Private file differs; public copy retained: '.$path);
                    }
                    if (! $public->delete($path)) {
                        throw new RuntimeException('Failed to remove public copy: '.$path);
                    }
                    $moved++;
                }
            });
        }
        if ($missing > 0) {
            Log::warning('Customer file migration: existing files missing', ['count' => $missing]);
        }
        $this->info("Customer files moved: {$moved}. Missing existing files: {$missing}.");

        return self::SUCCESS;
    }
}
