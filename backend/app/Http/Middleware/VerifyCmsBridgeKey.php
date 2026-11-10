<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Guards the headless WordPress CMS bridge endpoints.
 *
 * The CMS bridge exposes aggregate user and financial reporting data, so it
 * must never be publicly readable. The WordPress plugin authenticates with a
 * shared secret supplied through the X-CMS-Bridge-Key header.
 */
class VerifyCmsBridgeKey
{
    public function handle(Request $request, Closure $next): Response
    {
        $expected = (string) config('services.cms_bridge.key');

        // Fail closed: a missing key means the bridge is not provisioned yet.
        if ($expected === '') {
            return response()->json([
                'success' => false,
                'message' => 'CMS bridge is not configured on this environment.',
                'data'    => null,
            ], 503);
        }

        $provided = (string) $request->header('X-CMS-Bridge-Key', '');

        if ($provided === '' || !hash_equals($expected, $provided)) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized CMS bridge request.',
                'data'    => null,
            ], 401);
        }

        return $next($request);
    }
}
