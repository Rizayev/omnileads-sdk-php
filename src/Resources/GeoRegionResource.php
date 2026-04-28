<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Responses\GeoRegion;
use Madtec\OmniLeads\Enums\GeoRegions;
use Madtec\OmniLeads\Http\AuthType;

final class GeoRegionResource extends Resource
{
    /**
     * @return list<GeoRegion>
     */
    public function all(): array
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY);
        $response = $this->http->request('GET', '/GeoRegion', $options);

        $items = [];
        foreach ($response as $row) {
            if (is_array($row)) {
                $items[] = GeoRegion::fromArray($row);
            }
        }

        return $items;
    }

    /**
     * @return list<GeoRegion>
     */
    public function builtin(): array
    {
        $items = [];
        foreach (GeoRegions::all() as $id => $name) {
            $items[] = new GeoRegion(id: $id, name: $name);
        }

        return $items;
    }
}
