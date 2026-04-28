<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Http;

enum AuthType: string
{
    case API_KEY = 'api_key';
    case JWT = 'jwt';
}
