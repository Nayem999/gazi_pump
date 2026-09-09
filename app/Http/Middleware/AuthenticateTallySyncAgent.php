<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\TallyConnection;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * The Sync Agent is a machine identity tied to one tally_connections row,
 * never a human User — so it authenticates against its own hashed
 * sync_agent_token (generated once, shown only at creation/regeneration:
 * see TallyConnectionService), not a Sanctum PersonalAccessToken. Bearer
 * token in, hashed, matched against exactly one active connection; the
 * resolved connection is bound onto the request for the controller to use.
 */
class AuthenticateTallySyncAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (! $token) {
            throw new AuthenticationException('Missing Sync Agent credential.');
        }

        $connection = TallyConnection::query()
            ->where('sync_agent_token', hash('sha256', $token))
            ->where('is_active', true)
            ->first();

        if (! $connection) {
            throw new AuthenticationException('Invalid or inactive Sync Agent credential.');
        }

        $request->attributes->set('tallyConnection', $connection);

        return $next($request);
    }
}
