<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Responses\LimitType;
use Madtec\OmniLeads\Http\AuthType;

final class LimitTypeResource extends Resource
{
    /**
     * @return list<LimitType>
     */
    public function all(): array
    {
        $options = $this->factory->buildOptions(AuthType::API_KEY);
        $response = $this->http->request('GET', '/LimitType', $options);

        $items = [];
        foreach ($response as $row) {
            if (is_array($row)) {
                $items[] = LimitType::fromArray($row);
            }
        }

        return $items;
    }
}
