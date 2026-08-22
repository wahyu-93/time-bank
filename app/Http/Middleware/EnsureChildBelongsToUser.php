<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureChildBelongsToUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $child = $request->route('child');
        $user = $request->user();

        if (!$child || !$user->families()->whereKey($child->family_id)->exists()) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke anak ini.',
            ], 403);
        }

        return $next($request);
    }
}