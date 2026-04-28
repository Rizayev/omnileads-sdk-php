<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Http;

interface HttpClientInterface
{
    /**
     * Perform an HTTP request and return decoded JSON.
     *
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|list<mixed>
     */
    public function request(string $method, string $uri, array $options = []): array;
}
