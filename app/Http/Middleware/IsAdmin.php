<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class IsAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $role = Role::find(Auth::user()->role_id);
        if (!($role->name == UserRole::ADMIN->value)) {
            abort(403, "You don't have admin access.");
        }
        return $next($request);
    }
}
