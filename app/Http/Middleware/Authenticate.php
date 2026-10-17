<?php

namespace App\Http\Middleware;

use App\Models\db\Karyawan;
use Illuminate\Auth\Middleware\Authenticate as Middleware;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class Authenticate extends Middleware
{
    protected function authenticate($request, array $guards): void
    {
        parent::authenticate($request, $guards);
        $user = $request->user();
        if ($user->karyawan_id && ! Karyawan::whereKey($user->karyawan_id)->where('status', 1)->exists()) {
            Auth::guard()->logout();
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }
            throw ValidationException::withMessages(['username' => 'Pegawai sudah nonaktif. Hubungi admin.']);
        }
    }

    /**
     * Get the path the user should be redirected to when they are not authenticated.
     */
    protected function redirectTo(Request $request): ?string
    {
        return $request->expectsJson() ? null : route('login');
    }
}
