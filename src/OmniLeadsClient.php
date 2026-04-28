<?php

declare(strict_types=1);

namespace Madtec\OmniLeads;

use GuzzleHttp\ClientInterface as GuzzleClientInterface;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Http\HttpClient;
use Madtec\OmniLeads\Http\HttpClientInterface;
use Madtec\OmniLeads\Http\RequestFactory;
use Madtec\OmniLeads\Resources\AuthResource;
use Madtec\OmniLeads\Resources\GeoRegionResource;
use Madtec\OmniLeads\Resources\LimitTypeResource;
use Madtec\OmniLeads\Resources\ProjectDataResource;
use Madtec\OmniLeads\Resources\ProjectResource;
use Madtec\OmniLeads\Resources\ProjectSourceResource;
use Madtec\OmniLeads\Resources\SyncResource;
use Psr\Log\LoggerInterface;

final class OmniLeadsClient
{
    private readonly HttpClientInterface $http;

    private readonly RequestFactory $factory;

    private ?ProjectResource $projectResource = null;

    private ?ProjectSourceResource $projectSourceResource = null;

    private ?ProjectDataResource $projectDataResource = null;

    private ?AuthResource $authResource = null;

    private ?LimitTypeResource $limitTypeResource = null;

    private ?GeoRegionResource $geoRegionResource = null;

    private ?SyncResource $syncResource = null;

    public function __construct(
        public readonly OmniLeadsConfig $config,
        ?HttpClientInterface $http = null,
        ?GuzzleClientInterface $guzzle = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->factory = new RequestFactory($config);
        $this->http = $http ?? new HttpClient(
            config: $config,
            guzzle: $guzzle,
            logger: $logger,
        );
    }

    public function projects(): ProjectResource
    {
        return $this->projectResource ??= new ProjectResource($this->http, $this->factory);
    }

    public function sources(): ProjectSourceResource
    {
        return $this->projectSourceResource ??= new ProjectSourceResource($this->http, $this->factory);
    }

    public function data(): ProjectDataResource
    {
        return $this->projectDataResource ??= new ProjectDataResource($this->http, $this->factory);
    }

    public function auth(): AuthResource
    {
        return $this->authResource ??= new AuthResource($this->http, $this->factory);
    }

    public function limitTypes(): LimitTypeResource
    {
        return $this->limitTypeResource ??= new LimitTypeResource($this->http, $this->factory);
    }

    public function geoRegions(): GeoRegionResource
    {
        return $this->geoRegionResource ??= new GeoRegionResource($this->http, $this->factory);
    }

    public function sync(): SyncResource
    {
        return $this->syncResource ??= new SyncResource($this->http, $this->factory);
    }

    public function http(): HttpClientInterface
    {
        return $this->http;
    }

    public function factory(): RequestFactory
    {
        return $this->factory;
    }
}
