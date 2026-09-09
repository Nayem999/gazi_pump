<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\TallyApiFormat;
use App\Models\TallyConnection;
use App\Repositories\Contracts\TallyConnectionRepositoryInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class TallyConnectionService extends BaseCrudService
{
    public function __construct(private readonly TallyConnectionRepositoryInterface $connections)
    {
        parent::__construct($connections);
    }

    /**
     * @param  array{search?: string, status?: string, trashed?: string}  $filters
     */
    public function paginate(array $filters, int $perPage = 15): LengthAwarePaginator
    {
        return $this->connections->paginateWithFilters($filters, $perPage);
    }

    /**
     * The Sync Agent identity/credential are never admin-entered — they're
     * generated here so the plain token can be shown exactly once, the same
     * pattern Sanctum uses for its own plainTextToken.
     *
     * @param  array<string, mixed>  $data
     * @return array{connection: TallyConnection, plain_token: string}
     */
    public function createWithAgentCredentials(array $data): array
    {
        $plainToken = Str::random(64);

        /** @var TallyConnection $connection */
        $connection = $this->create([
            ...$data,
            'sync_agent_id' => (string) Str::uuid(),
            'sync_agent_token' => hash('sha256', $plainToken),
        ]);

        return ['connection' => $connection, 'plain_token' => $plainToken];
    }

    public function regenerateAgentToken(TallyConnection $connection): string
    {
        $plainToken = Str::random(64);

        $this->update($connection, ['sync_agent_token' => hash('sha256', $plainToken)]);

        return $plainToken;
    }

    /**
     * Attempts a real HTTP round-trip to the connection's configured host,
     * using whichever request body its api_format calls for. Updates
     * last_heartbeat_at/last_successful_sync_at on success so the
     * dashboard's connection-health display reflects a manual test the same
     * way it would a real Sync Agent heartbeat.
     *
     * @return array{ok: bool, message: string}
     */
    public function testConnection(TallyConnection $connection): array
    {
        try {
            $response = match ($connection->api_format) {
                TallyApiFormat::Json => Http::timeout(10)->post($connection->baseUrl(), [
                    'request' => 'company-info',
                ]),
                TallyApiFormat::Xml => Http::timeout(10)
                    ->withBody($this->companyInfoXmlRequest(), 'text/xml')
                    ->post($connection->baseUrl()),
            };
        } catch (\Throwable $e) {
            return ['ok' => false, 'message' => "Could not reach {$connection->baseUrl()}: {$e->getMessage()}"];
        }

        if (! $response->successful()) {
            return ['ok' => false, 'message' => "Tally responded with HTTP {$response->status()}."];
        }

        $now = now();
        $this->update($connection, [
            'last_heartbeat_at' => $now,
            'last_successful_sync_at' => $now,
        ]);

        return ['ok' => true, 'message' => 'Connected successfully.'];
    }

    /**
     * A minimal, version-agnostic XML export request — just enough to prove
     * the gateway is alive and responding with a well-formed Tally envelope,
     * without depending on any customer-specific TDL/report configuration.
     */
    private function companyInfoXmlRequest(): string
    {
        return <<<'XML'
            <ENVELOPE>
             <HEADER>
              <VERSION>1</VERSION>
              <TALLYREQUEST>Export</TALLYREQUEST>
              <TYPE>Collection</TYPE>
              <ID>List of Companies</ID>
             </HEADER>
             <BODY>
              <DESC>
               <STATICVARIABLES>
                <SVCURRENTCOMPANY>##SVCurrentCompany</SVCURRENTCOMPANY>
               </STATICVARIABLES>
              </DESC>
             </BODY>
            </ENVELOPE>
            XML;
    }
}
