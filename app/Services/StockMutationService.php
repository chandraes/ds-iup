<?php

namespace App\Services;

use App\Models\db\Barang\Barang;
use App\Models\db\Barang\BarangStokHarga;
use App\Models\db\Karyawan;
use App\Models\db\Pajak;
use App\Models\GantiRugi;
use App\Models\HargaSubmission;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class StockMutationService
{
    public function revision(BarangStokHarga $stock): string
    {
        $values = [];
        foreach (['id', 'barang_id', 'stok_awal', 'stok', 'harga', 'harga_beli', 'min_jual', 'hide', 'updated_at'] as $key) {
            $values[] = (string) $stock->getRawOriginal($key);
        }
        $proposal = HargaSubmission::where('barang_stok_harga_id', $stock->id)->where('status', 1)->first();
        foreach (['id', 'harga_ajuan', 'min_jual_ajuan', 'updated_at'] as $key) {
            $values[] = (string) $proposal?->getRawOriginal($key);
        }

        return hash('sha256', json_encode($values));
    }

    public function permissions(User $user, BarangStokHarga $stock): array
    {
        $admin = in_array($user->role, ['su', 'admin'], true);

        return ['edit_price' => $admin, 'correct' => $user->role === 'su', 'history' => $admin,
            'loss' => $admin && $stock->stok > 0, 'hide' => $admin && $stock->stok == 0,
            'approve' => $admin, 'propose' => $user->role === 'asisten-admin' && $stock->harga == 0];
    }

    public function validatedInput(string $action, array $input): array
    {
        foreach (['harga', 'harga_ajuan', 'harga_beli', 'min_jual', 'min_jual_ajuan', 'stok_awal', 'stok', 'jumlah_hilang'] as $field) {
            if (isset($input[$field]) && is_string($input[$field]) && preg_match('/^\d{1,3}(?:\.\d{3})+$/D', $input[$field])) {
                $input[$field] = str_replace('.', '', $input[$field]);
            }
        }
        $number = ['required', 'integer', 'between:0,2147483647'];
        $positive = ['required', 'integer', 'between:1,2147483647'];
        $rules = match ($action) {
            'price' => ['harga' => $number, 'min_jual' => $positive],
            'propose', 'approve' => ['harga_ajuan' => $number, 'min_jual_ajuan' => $positive],
            'correct' => ['stok_awal' => $number, 'stok' => $number, 'harga_beli' => ['nullable', 'numeric', 'between:0,2147483647']],
            'loss' => ['jumlah_hilang' => $positive, 'karyawan_id' => $positive, 'aksi' => ['required', 'integer', 'in:1,2'], 'kas_ppn' => ['sometimes', 'integer', 'in:0,1']],
            'hide' => [],
        };

        return Validator::make($input, $rules)->validate();
    }

    public function mutate(User $actor, int $id, string $action, array $data, ?string $revision = null): array
    {
        abort_unless(in_array($actor->role, $action === 'propose' ? ['su', 'admin', 'asisten-admin'] : ['su', 'admin'], true), 403);
        abort_if($action === 'correct' && $actor->role !== 'su', 403);

        $data = $this->validatedInput($action, $data);

        return DB::transaction(function () use ($actor, $id, $action, $data, $revision): array {
            $productId = BarangStokHarga::findOrFail($id)->barang_id;
            Barang::whereKey($productId)->lockForUpdate()->firstOrFail();
            $stock = BarangStokHarga::whereKey($id)->lockForUpdate()->firstOrFail();
            if ($stock->hide || ($revision !== null && ! hash_equals($this->revision($stock), $revision))) {
                throw ValidationException::withMessages(['revision' => 'Data stok atau ajuan telah berubah. Tutup modal dan muat ulang data terlebih dahulu.']);
            }
            if ($action === 'hide') {
                $batches = BarangStokHarga::where('barang_id', $productId)->where('hide', 0)->orderBy('id')->lockForUpdate()->get();
                if ($stock->stok != 0 || $batches->count() <= 1) {
                    throw ValidationException::withMessages(['stok' => 'Hanya stok nol yang dapat disembunyikan dan minimal satu data stok harus tetap terlihat.']);
                }
                $stock->update(['hide' => 1]);
            } elseif ($action === 'correct') {
                $stock->update(array_filter($data, fn ($value): bool => $value !== null));
            } elseif (in_array($action, ['price', 'propose', 'approve'], true)) {
                $price = (int) ($data['harga'] ?? $data['harga_ajuan']);
                $minimum = (int) ($data['min_jual'] ?? $data['min_jual_ajuan']);
                if ($actor->role !== 'su' && $price < $stock->harga_beli) {
                    throw ValidationException::withMessages(['harga' => 'Harga jual tidak boleh lebih kecil dari harga beli.']);
                }
                if ($action === 'propose') {
                    if ($actor->role === 'asisten-admin' && $stock->harga != 0) {
                        throw ValidationException::withMessages(['harga' => 'Ajuan asisten hanya untuk stok dengan harga jual nol.']);
                    }
                    HargaSubmission::updateOrCreate(['barang_stok_harga_id' => $stock->id], ['user_id' => $actor->id,
                        'harga_ajuan' => $price, 'min_jual_ajuan' => $minimum, 'status' => 1]);
                } else {
                    if ($action === 'approve') {
                        $proposal = HargaSubmission::where('barang_stok_harga_id', $stock->id)->where('status', 1)->lockForUpdate()->first();
                        if (! $proposal) {
                            throw ValidationException::withMessages(['ajuan' => 'Ajuan sudah diproses atau tidak tersedia.']);
                        }
                        $proposal->update(['status' => 2]);
                    }
                    $stock->update(['harga' => $price, 'min_jual' => $minimum]);
                }
            } elseif ($action === 'loss') {
                if (! Karyawan::whereKey($data['karyawan_id'])->where('status', 1)->lockForUpdate()->first()) {
                    throw ValidationException::withMessages(['karyawan_id' => 'Pegawai tidak aktif atau tidak tersedia.']);
                }
                if ($data['jumlah_hilang'] > $stock->stok) {
                    throw ValidationException::withMessages(['jumlah_hilang' => 'Jumlah hilang melebihi stok terbaru.']);
                }
                $cashType = (int) ($data['kas_ppn'] ?? 1);
                $price = (int) round($stock->harga_beli * ($cashType === 1 ? 1 + (float) Pajak::where('untuk', 'ppn')->firstOrFail()->persen / 100 : 1));
                $total = $price * $data['jumlah_hilang'];
                $paid = (int) $data['aksi'] === 1;

                return (new GantiRugi)->ganti_rugi(['harga' => $price, 'jumlah' => (int) $data['jumlah_hilang'], 'total' => $total,
                    'karyawan_id' => (int) $data['karyawan_id'], 'kas_ppn' => $cashType, 'lunas' => $paid ? 1 : 0,
                    'total_bayar' => $paid ? $total : 0, 'sisa' => $paid ? 0 : $total,
                    'barang_stok_harga_id' => $stock->id, 'barang_id' => $stock->barang_id]);
            }

            return ['status' => 'success', 'message' => 'Data stok berhasil diperbarui.'];
        });
    }
}
