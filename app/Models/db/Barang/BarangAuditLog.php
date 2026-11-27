<?php

namespace App\Models\db\Barang;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Auth;

class BarangAuditLog extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'old_values' => 'array',
        'new_values' => 'array',
        'context' => 'array',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Record who changed a barang, what changed and from which request.
     *
     * @param  array<string, mixed>  $oldValues
     * @param  array<string, mixed>  $newValues
     * @param  array<string, mixed>  $extraContext
     */
    public static function record(string $action, ?int $barangId, array $oldValues = [], array $newValues = [], array $extraContext = []): self
    {
        $request = request();
        $user = Auth::user();

        return self::create([
            'barang_id' => $barangId,
            'user_id' => $user?->id,
            'user_name' => $user?->name,
            'action' => $action,
            'old_values' => $oldValues ?: null,
            'new_values' => $newValues ?: null,
            'context' => array_filter([
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'url' => $request->fullUrl(),
                'referer' => $request->headers->get('referer'),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'input' => $request->except(['_token', '_method', 'password', 'password_confirmation']),
                'files' => array_map(fn ($file) => $file->getClientOriginalName(), array_filter($request->allFiles(), fn ($file) => ! is_array($file))),
                ...$extraContext,
            ], fn ($value) => $value !== null && $value !== []),
        ]);
    }
}
