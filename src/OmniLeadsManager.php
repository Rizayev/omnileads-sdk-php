<?php

declare(strict_types=1);

namespace Madtec\OmniLeads;

use Illuminate\Contracts\Container\Container;
use Madtec\OmniLeads\Resources\AuthResource;
use Madtec\OmniLeads\Resources\GeoRegionResource;
use Madtec\OmniLeads\Resources\LimitTypeResource;
use Madtec\OmniLeads\Resources\ProjectDataResource;
use Madtec\OmniLeads\Resources\ProjectResource;
use Madtec\OmniLeads\Resources\ProjectSourceResource;
use Madtec\OmniLeads\Resources\SyncResource;

/**
 * Laravel-aware bridge that resolves the OmniLeads client from the container
 * and exposes a stable surface for the facade. Keeps the client itself free
 * of Laravel dependencies.
 */
final class OmniLeadsManager
{
    public function __construct(
        private readonly Container $container,
    ) {}

    public function client(): OmniLeadsClient
    {
        /** @var OmniLeadsClient $client */
        $client = $this->container->make(OmniLeadsClient::class);

        return $client;
    }

    public function projects(): ProjectResource
    {
        return $this->client()->projects();
    }

    public function sources(): ProjectSourceResource
    {
        return $this->client()->sources();
    }

    public function data(): ProjectDataResource
    {
        return $this->client()->data();
    }

    public function auth(): AuthResource
    {
        return $this->client()->auth();
    }

    public function limitTypes(): LimitTypeResource
    {
        return $this->client()->limitTypes();
    }

    public function geoRegions(): GeoRegionResource
    {
        return $this->client()->geoRegions();
    }

    public function sync(): SyncResource
    {
        return $this->client()->sync();
    }

    /**
     * Replace the bound client with a custom instance — useful for tests.
     */
    public function swap(OmniLeadsClient $client): void
    {
        $this->container->instance(OmniLeadsClient::class, $client);
        $this->container->instance('omnileads.client', $client);
    }
}
