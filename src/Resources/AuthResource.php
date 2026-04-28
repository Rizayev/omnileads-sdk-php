<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\DTO\Requests\WebhookSettingsRequest;
use Madtec\OmniLeads\DTO\Responses\WebhookSettings;
use Madtec\OmniLeads\Http\AuthType;

final class AuthResource extends Resource
{
    public function getWebhookSettings(): WebhookSettings
    {
        $options = $this->factory->buildOptions(AuthType::JWT);
        $response = $this->http->request('GET', '/Auth/me/webhook-settings', $options);

        return WebhookSettings::fromArray($response);
    }

    public function updateWebhookSettings(WebhookSettingsRequest $request): WebhookSettings
    {
        $options = $this->factory->buildOptions(AuthType::JWT, body: $request->toArray());
        $response = $this->http->request('PUT', '/Auth/me/webhook-settings', $options);

        return WebhookSettings::fromArray($response);
    }
}
