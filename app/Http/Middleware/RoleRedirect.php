<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleRedirect {
    public function handle(Request $request, Closure $next) {
        if (auth()->check()) {
            $user = auth()->user();

            // Self-heal: ensure every authenticated user has at least 'employee' role
            if (!$user->hasRole('admin') && !$user->hasRole('employee')) {
                \Spatie\Permission\Models\Role::firstOrCreate(['name' => 'employee']);
                $user->assignRole('employee');
            }

            if ($user->hasRole('admin') && $request->is('/')) {
                return redirect()->route('admin.dashboard');
            }
            if ($user->hasRole('employee') && $request->is('/')) {
                return redirect()->route('portal.dashboard');
            }
        }
        return $next($request);
    }
}

