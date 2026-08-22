<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsurePrivilegeRequestBelongsToUser
{
    public function handle(Request $request, Closure $next): Response
    {
        $privilegeRequest = $request->route('privilegeRequest');
        $user = $request->user();

        if (!$privilegeRequest || !$user->families()->whereKey($privilegeRequest->child->family_id)->exists()) {
            return response()->json(['message' => 'Anda tidak memiliki akses ke request ini.'], 403);
        }

        return $next($request);
    }
}