<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Http;

use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Exceptions\ConfigurationException;

final class RequestFactory
{
    public function __construct(
        private readonly OmniLeadsConfig $config,
    ) {}

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, scalar|null>|null  $query
     * @param  array<string, string>  $extraHeaders
     * @return array<string, mixed>
     */
    public function buildOptions(
        AuthType $authType,
        ?array $body = null,
        ?array $query = null,
        array $extraHeaders = [],
    ): array {
        $headers = array_merge([
            'Accept' => 'application/json',
            'User-Agent' => 'madtec-omnileads-sdk/1.0 (php)',
        ], $extraHeaders);

        if ($authType === AuthType::API_KEY) {
            if ($this->config->apiKey === null) {
                throw new ConfigurationException(
                    'OMNILEADS_API_KEY is not configured but a request requires X-Api-Key authentication',
                );
            }

            $headers['X-Api-Key'] = $this->config->apiKey;
        } else {
            if ($this->config->jwt === null) {
                throw new ConfigurationException(
                    'OMNILEADS_JWT is not configured but a request requires Bearer authentication (webhook-settings)',
                );
            }

            $headers['Authorization'] = 'Bearer '.$this->config->jwt;
        }

        $options = [
            'headers' => $headers,
            'timeout' => $this->config->timeout,
            'connect_timeout' => $this->config->timeout,
            'http_errors' => true,
        ];

        if ($body !== null) {
            $options['json'] = $body;
        }

        if ($query !== null && $query !== []) {
            $options['query'] = array_filter(
                $query,
                static fn (mixed $value): bool => $value !== null,
            );
        }

        return $options;
    }
}
