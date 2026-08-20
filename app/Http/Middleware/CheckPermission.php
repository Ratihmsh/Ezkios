<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CheckPermission
{
    public function handle(Request $request, Closure $next, $permission)
    {
        // Cek apakah user sudah login
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        $user = Auth::user();

        // Log untuk debugging
        Log::info('CheckPermission Middleware', [
            'user_id' => $user->id,
            'user_email' => $user->email,
            'permission_required' => $permission,
            'url' => $request->fullUrl()
        ]);

        // Cek apakah user memiliki permission
        if ($user->hasPermission($permission)) {
            return $next($request);
        }

        // Jika tidak punya permission
        abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
    }
}
