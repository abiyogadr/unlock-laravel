<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Upanel;

class IsAdmin
{
    public function handle(Request $request, Closure $next)
    {
        if (!Auth::check()) {
            return redirect('/login');
        }

        $isAdmin = Upanel::where('user_id', Auth::id())->exists();
        
        if (!$isAdmin) {
            abort(403, 'Access denied. Admin privileges required.');
        }

        return $next($request);
    }
}
