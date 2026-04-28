<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Webhook;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

final class ImportCompletedReceived
{
    use Dispatchable;
    use SerializesModels;

    public function __construct(
        public readonly ImportCompletedEvent $event,
    ) {}
}
