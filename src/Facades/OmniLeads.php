<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Facades;

use Illuminate\Contracts\Container\Container;
use Illuminate\Support\Facades\Facade;
use Madtec\OmniLeads\OmniLeadsClient;
use Madtec\OmniLeads\OmniLeadsManager;
use Madtec\OmniLeads\Resources\AuthResource;
use Madtec\OmniLeads\Resources\GeoRegionResource;
use Madtec\OmniLeads\Resources\LimitTypeResource;
use Madtec\OmniLeads\Resources\ProjectDataResource;
use Madtec\OmniLeads\Resources\ProjectResource;
use Madtec\OmniLeads\Resources\ProjectSourceResource;
use Madtec\OmniLeads\Resources\SyncResource;

/**
 * @method static OmniLeadsClient client()
 * @method static ProjectResource projects()
 * @method static ProjectSourceResource sources()
 * @method static ProjectDataResource data()
 * @method static AuthResource auth()
 * @method static LimitTypeResource limitTypes()
 * @method static GeoRegionResource geoRegions()
 * @method static SyncResource sync()
 * @method static void swap(OmniLeadsClient $client)
 *
 * @see OmniLeadsManager
 */
final class OmniLeads extends Facade
{
    public static function fake(?OmniLeadsClient $client = null): OmniLeadsClient
    {
        $app = self::getFacadeApplication();
        if ($app === null) {
            throw new \RuntimeException('Cannot fake OmniLeads outside of a Laravel application');
        }

        $client ??= self::buildFakeClient($app);

        $manager = $app->make(OmniLeadsManager::class);
        if ($manager instanceof OmniLeadsManager) {
            $manager->swap($client);
        }

        return $client;
    }

    protected static function getFacadeAccessor(): string
    {
        return 'omnileads';
    }

    private static function buildFakeClient(Container $app): OmniLeadsClient
    {
        /** @var OmniLeadsClient $current */
        $current = $app->make(OmniLeadsClient::class);

        return $current;
    }
}
