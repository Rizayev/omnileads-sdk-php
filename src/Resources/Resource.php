<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Resources;

use Madtec\OmniLeads\Http\HttpClientInterface;
use Madtec\OmniLeads\Http\RequestFactory;

abstract class Resource
{
    public function __construct(
        protected readonly HttpClientInterface $http,
        protected readonly RequestFactory $factory,
    ) {}
}
