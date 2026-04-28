<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Requests\SyncProjectsRequest;
use Madtec\OmniLeads\DTO\Responses\SyncResult;
use Madtec\OmniLeads\Http\AuthType;

final class SyncResource extends Resource
{
    public function push(SyncProjectsRequest $request): SyncResult
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY, body: $request->toArray());
        $response = $this->http->request('POST', '/Project/sync', $options);

        return SyncResult::fromArray($response);
    }

    /**
     * Pull current sync snapshot from OmniLeads. Returns the raw projects payload as it
     * arrives — the format mirrors what `push()` accepts and is meant to round-trip.
     *
     * @return array<int|string, mixed>
     */
    public function pull(): array
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY);

        return $this->http->request('GET', '/Project/sync', $options);
    }
}
