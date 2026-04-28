# OmniLeads SDK for PHP

[![Latest Version on Packagist](https://img.shields.io/packagist/v/madtec/omnileads-sdk.svg?style=flat-square)](https://packagist.org/packages/madtec/omnileads-sdk)
[![Tests](https://github.com/Rizayev/omnileads-sdk-php/actions/workflows/tests.yml/badge.svg)](https://github.com/Rizayev/omnileads-sdk-php/actions/workflows/tests.yml)
[![Static Analysis](https://github.com/Rizayev/omnileads-sdk-php/actions/workflows/static-analysis.yml/badge.svg)](https://github.com/Rizayev/omnileads-sdk-php/actions/workflows/static-analysis.yml)
[![Total Downloads](https://img.shields.io/packagist/dt/madtec/omnileads-sdk.svg?style=flat-square)](https://packagist.org/packages/madtec/omnileads-sdk)
[![License](https://img.shields.io/packagist/l/madtec/omnileads-sdk.svg?style=flat-square)](LICENSE)

Production-ready PHP SDK for the [OmniLeads API](https://api.madtec.ru/api). Ships with
first-class Laravel integration (ServiceProvider, Facade, config publishing) but the
client itself is framework-agnostic and works in any PHP 8.2+ project.

## Requirements

- PHP **8.2+**
- Laravel **10.x / 11.x / 12.x** (optional — only required when using the Laravel
  integration)
- Guzzle HTTP **7.8+**

## Installation

```bash
composer require madtec/omnileads-sdk
```

Publish the Laravel config (optional but recommended):

```bash
php artisan vendor:publish --tag=omnileads-config
```

Set the environment variables in `.env`:

```dotenv
OMNILEADS_BASE_URL=https://api.madtec.ru/api
OMNILEADS_API_KEY=your-api-key-here
OMNILEADS_JWT=your-jwt-here          # only for /Auth/me/webhook-settings
OMNILEADS_TIMEOUT=30
OMNILEADS_RETRY_ENABLED=true
OMNILEADS_RETRY_TIMES=3
OMNILEADS_RETRY_SLEEP_MS=200
OMNILEADS_LOG_CHANNEL=stack          # optional: PSR-3 channel for request logs
```

## Quick start

### 1. Create a project

```php
use Madtec\OmniLeads\DTO\Requests\CreateProjectRequest;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Facades\OmniLeads;

$result = OmniLeads::projects()->create(new CreateProjectRequest(
    name: 'CRM import',
    limit: 1000,
    limitTypeId: 1,
    description: 'Imported daily',
    state: ProjectState::ACTIVE,
    geoRegions: [77, 78],
));

$projectId = $result->id;
```

### 2. Add a source

```php
use Madtec\OmniLeads\DTO\Requests\CreateSourceRequest;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Facades\OmniLeads;

OmniLeads::sources()->add($projectId, new CreateSourceRequest(
    sourceTypeId: SourceType::DOMAIN,
    sourceDataId: SourceData::MEGAFON,
    sourceValues: ['example.com'],
    limit: 500,
));
```

### 3. Fetch data for a day

```php
use Madtec\OmniLeads\Facades\OmniLeads;

// All projects, no pagination
$data = OmniLeads::data()->byDay('2026-04-08');

// Or stream through paginated pages
foreach (OmniLeads::data()->iterateByDay('2026-04-08', pageSize: 1000) as $project) {
    foreach ($project->segments as $segment) {
        foreach ($segment->phones as $phone) {
            // process $phone->phone, $phone->createdAt
        }
    }
}
```

### 4. Sync projects in bulk

```php
use Madtec\OmniLeads\DTO\Requests\SyncProjectDTO;
use Madtec\OmniLeads\DTO\Requests\SyncProjectsRequest;
use Madtec\OmniLeads\DTO\Requests\SyncSegmentDTO;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;
use Madtec\OmniLeads\Facades\OmniLeads;

$result = OmniLeads::sync()->push(new SyncProjectsRequest([
    new SyncProjectDTO(
        name: 'Daily import',
        limit: 5000,
        limitType: 'Дневной',
        segments: [
            SyncSegmentDTO::fromEnums(SourceType::DOMAIN, SourceData::MEGAFON, ['a.com'], 500),
            SyncSegmentDTO::fromEnums(SourceType::PHONE,  SourceData::MTS,     ['79001234567']),
        ],
    ),
]));

if (! $result->success) {
    foreach ($result->errors as $error) {
        report(new \RuntimeException($error));
    }
}
```

### 5. Receive webhook notifications

See [Webhook handler](#webhook-handler) below.

## Available methods

```php
use Madtec\OmniLeads\Facades\OmniLeads;

// Projects
OmniLeads::projects()->list();
OmniLeads::projects()->get($projectId);
OmniLeads::projects()->create($createProjectRequest);
OmniLeads::projects()->update($projectId, $updateProjectRequest);
OmniLeads::projects()->changeState($projectId, 'paused');
OmniLeads::projects()->activate($projectId);   // shortcut
OmniLeads::projects()->pause($projectId);      // shortcut

// Sources
OmniLeads::sources()->add($projectId, $createSourceRequest);
OmniLeads::sources()->update($projectId, $sourceId, $updateSourceRequest);
OmniLeads::sources()->delete($projectId, $sourceId);

// Data
OmniLeads::data()->byDay('2026-04-08');
OmniLeads::data()->byDayPaged('2026-04-08', page: 1, pageSize: 1000);
OmniLeads::data()->iterateByDay('2026-04-08');

// Reference data
OmniLeads::limitTypes()->all();
OmniLeads::geoRegions()->all();           // remote API
OmniLeads::geoRegions()->builtin();       // hardcoded list, no HTTP

// Webhook settings (Bearer JWT auth)
OmniLeads::auth()->getWebhookSettings();
OmniLeads::auth()->updateWebhookSettings($webhookSettingsRequest);

// Sync
OmniLeads::sync()->push($syncProjectsRequest);
OmniLeads::sync()->pull();
```

## Built-in enums

```php
use Madtec\OmniLeads\Enums\DayOfWeek;
use Madtec\OmniLeads\Enums\GeoRegions;
use Madtec\OmniLeads\Enums\ProjectState;
use Madtec\OmniLeads\Enums\SourceData;
use Madtec\OmniLeads\Enums\SourceType;

SourceType::DOMAIN->value;       // 3
SourceData::MEGAFON->value;      // 8
ProjectState::ACTIVE;            // active|paused
DayOfWeek::MONDAY->value;        // 1

GeoRegions::name(77);            // "Москва"
GeoRegions::all();               // [0 => "Все регионы", 1 => "Адыгея", ...]
```

## Error handling

Every error raised by the SDK extends
`Madtec\OmniLeads\Exceptions\OmniLeadsException`. The HTTP-derived ones extend
`ApiException`:

```php
use Madtec\OmniLeads\Exceptions\ApiException;
use Madtec\OmniLeads\Exceptions\AuthenticationException;
use Madtec\OmniLeads\Exceptions\ConfigurationException;
use Madtec\OmniLeads\Exceptions\NotFoundException;
use Madtec\OmniLeads\Exceptions\ServerException;
use Madtec\OmniLeads\Exceptions\ValidationException;
use Madtec\OmniLeads\Facades\OmniLeads;

try {
    $project = OmniLeads::projects()->get($id);
} catch (ConfigurationException $e) {
    // Local pre-flight validation failed (bad GUID, missing JWT, ...)
} catch (ValidationException $e) {
    // 400 Bad Request — $e->errorMessage holds the API message
} catch (AuthenticationException $e) {
    // 401/403
} catch (NotFoundException $e) {
    // 404
} catch (ServerException $e) {
    // 5xx — already retried per `OMNILEADS_RETRY_*`
} catch (ApiException $e) {
    // any other HTTP error or network failure ($e->statusCode === 0)
}
```

`ApiException` exposes `statusCode`, `responseBody`, `requestMethod`, `requestUri`,
`errorMessage`, and `requestId` (when present in the body).

## Retry behaviour

Retries are enabled by default for **5xx** responses and network failures
(connection refused, DNS timeouts, etc.). Backoff is exponential:
`sleep_ms * 2^(attempt - 1)`. **4xx responses are never retried** because they signal a
client-side error.

Disable retries entirely:

```dotenv
OMNILEADS_RETRY_ENABLED=false
```

Or per-request, by constructing a fresh `OmniLeadsClient` with
`RetryConfig::disabled()`.

## Webhook handler

The OmniLeads platform pushes an `import.completed` event after each finished import.
Below is a complete example of how to receive it in Laravel.

### Route

```php
// routes/web.php

use App\Http\Controllers\OmniLeadsWebhookController;

Route::post(
    '/webhooks/omnileads/{secret}',
    OmniLeadsWebhookController::class,
)->where('secret', '[a-f0-9]{32}')->name('webhooks.omnileads');
```

> The OmniLeads API does not currently sign webhook payloads. Protect the endpoint with
> a shared secret embedded in the URL (as above) and/or an IP allow list. See
> [SECURITY.md](SECURITY.md).

### Controller

```php
// app/Http/Controllers/OmniLeadsWebhookController.php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Madtec\OmniLeads\Exceptions\ValidationException;
use Madtec\OmniLeads\Webhook\ImportCompletedReceived;
use Madtec\OmniLeads\Webhook\WebhookPayloadParser;

final class OmniLeadsWebhookController
{
    public function __construct(
        private readonly WebhookPayloadParser $parser,
    ) {}

    public function __invoke(Request $request, string $secret): JsonResponse
    {
        if (! hash_equals(config('services.omnileads.webhook_secret', ''), $secret)) {
            return response()->json(['error' => 'invalid secret'], 403);
        }

        try {
            $event = $this->parser->parse($request->getContent());
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errorMessage], 400);
        }

        ImportCompletedReceived::dispatch($event);

        return response()->json(['ok' => true]);
    }
}
```

### Listener

```php
// app/Listeners/ProcessOmniLeadsImport.php

namespace App\Listeners;

use Illuminate\Contracts\Queue\ShouldQueue;
use Madtec\OmniLeads\Webhook\ImportCompletedReceived;

final class ProcessOmniLeadsImport implements ShouldQueue
{
    public function handle(ImportCompletedReceived $eventEnvelope): void
    {
        $event = $eventEnvelope->event;

        foreach ($event->projects as $project) {
            foreach ($project->segments as $segment) {
                foreach ($segment->phones as $phone) {
                    // store $phone->phone, $phone->createdAt
                }
            }
        }
    }
}
```

Register it in `EventServiceProvider`:

```php
protected $listen = [
    \Madtec\OmniLeads\Webhook\ImportCompletedReceived::class => [
        \App\Listeners\ProcessOmniLeadsImport::class,
    ],
];
```

## Testing your application

The Facade exposes a `fake()` helper plus a `swap()` method on the manager so you can
inject a custom client in tests:

```php
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\Facades\OmniLeads;
use Madtec\OmniLeads\OmniLeadsClient;

$mock = new MockHandler([
    new Response(200, [], json_encode([])),
]);

$guzzle = new GuzzleClient([
    'base_uri' => 'https://api.test/api/',
    'handler'  => HandlerStack::create($mock),
]);

OmniLeads::fake(new OmniLeadsClient(
    config: new OmniLeadsConfig(
        baseUrl: 'https://api.test/api',
        apiKey: 'test',
        jwt: null,
        timeout: 5,
        retry: RetryConfig::disabled(),
    ),
    guzzle: $guzzle,
));

$result = OmniLeads::projects()->list(); // returns []
```

## Without Laravel

The package works in any PHP project — Laravel only adds sugar:

```php
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Config\RetryConfig;
use Madtec\OmniLeads\OmniLeadsClient;

$client = new OmniLeadsClient(
    config: new OmniLeadsConfig(
        baseUrl: 'https://api.madtec.ru/api',
        apiKey: getenv('OMNILEADS_API_KEY') ?: null,
        jwt: getenv('OMNILEADS_JWT') ?: null,
        timeout: 30,
        retry: new RetryConfig(enabled: true, times: 3, sleepMs: 200),
    ),
);

$projects = $client->projects()->list();
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md) for the full release history.

## Credits

Contributions are welcome — see [CONTRIBUTING.md](CONTRIBUTING.md). A list of
contributors is generated automatically by [contrib.rocks](https://contrib.rocks).

## Security

If you discover a security vulnerability, please follow the disclosure process in
[SECURITY.md](SECURITY.md). Do **not** open a public GitHub issue.

## License

Released under the MIT License — see [LICENSE](LICENSE) for the full text.
