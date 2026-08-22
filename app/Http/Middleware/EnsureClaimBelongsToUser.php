<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureClaimBelongsToUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $claim = $request->route('claim');
        $user = $request->user();

        if (!$claim || !$user->families()->whereKey($claim->child->family_id)->exists()) {
            return response()->json([
                'message' => 'Anda tidak memiliki akses ke aktivitas ini.',
            ], 403);
        }

        return $next($request);
    }
}