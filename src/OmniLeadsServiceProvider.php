<?php

declare(strict_types=1);

namespace Madtec\OmniLeads;

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Support\ServiceProvider;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Webhook\WebhookPayloadParser;
use Psr\Log\LoggerInterface;

final class OmniLeadsServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom($this->configPath(), 'omnileads');

        $this->app->singleton(OmniLeadsConfig::class, function (Container $app): OmniLeadsConfig {
            $config = $app->make('config')->get('omnileads', []);
            if (! is_array($config)) {
                $config = [];
            }

            return OmniLeadsConfig::fromArray($config);
        });

        $this->app->singleton(OmniLeadsClient::class, function (Container $app): OmniLeadsClient {
            $config = $app->make(OmniLeadsConfig::class);
            $logger = null;

            if ($app instanceof Application && $app->bound('config')) {
                $channel = $app->make('config')->get('omnileads.logger_channel');
                if (is_string($channel) && $channel !== '' && $app->bound('log')) {
                    $logger = $app->make('log')->channel($channel);
                    if (! $logger instanceof LoggerInterface) {
                        $logger = null;
                    }
                }
            }

            return new OmniLeadsClient(config: $config, logger: $logger);
        });

        $this->app->alias(OmniLeadsClient::class, 'omnileads.client');

        $this->app->singleton(OmniLeadsManager::class, function (Container $app): OmniLeadsManager {
            return new OmniLeadsManager($app);
        });

        $this->app->alias(OmniLeadsManager::class, 'omnileads');

        $this->app->singleton(WebhookPayloadParser::class, function (): WebhookPayloadParser {
            return new WebhookPayloadParser;
        });
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                $this->configPath() => $this->app->configPath('omnileads.php'),
            ], 'omnileads-config');
        }
    }

    /**
     * @return list<string>
     */
    public function provides(): array
    {
        return [
            OmniLeadsConfig::class,
            OmniLeadsClient::class,
            OmniLeadsManager::class,
            WebhookPayloadParser::class,
            'omnileads',
            'omnileads.client',
        ];
    }

    private function configPath(): string
    {
        return dirname(__DIR__).'/config/omnileads.php';
    }
}
