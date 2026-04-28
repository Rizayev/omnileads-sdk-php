<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Exceptions;

use Throwable;

class ApiException extends OmniLeadsException
{
    public function __construct(
        public readonly int $statusCode,
        public readonly string $responseBody,
        public readonly string $requestMethod,
        public readonly string $requestUri,
        public readonly ?string $errorMessage = null,
        public readonly ?string $requestId = null,
        ?Throwable $previous = null,
    ) {
        $message = sprintf(
            'OmniLeads API error [%d] on %s %s%s',
            $statusCode,
            $requestMethod,
            $requestUri,
            $errorMessage !== null && $errorMessage !== '' ? ': '.$errorMessage : '',
        );

        parent::__construct($message, $statusCode, $previous);
    }

    /**
     * @return static
     */
    public static function fromResponse(
        int $statusCode,
        string $responseBody,
        string $requestMethod,
        string $requestUri,
        ?Throwable $previous = null,
    ): self {
        [$errorMessage, $requestId] = self::extractDetails($responseBody);

        /** @phpstan-ignore-next-line new.static */
        return new static(
            statusCode: $statusCode,
            responseBody: $responseBody,
            requestMethod: $requestMethod,
            requestUri: $requestUri,
            errorMessage: $errorMessage,
            requestId: $requestId,
            previous: $previous,
        );
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private static function extractDetails(string $body): array
    {
        if ($body === '') {
            return [null, null];
        }

        $decoded = json_decode($body, true);
        if (! is_array($decoded)) {
            return [null, null];
        }

        $message = null;
        if (isset($decoded['message']) && is_string($decoded['message'])) {
            $message = $decoded['message'];
        } elseif (isset($decoded['error']) && is_string($decoded['error'])) {
            $message = $decoded['error'];
        } elseif (isset($decoded['errors'])) {
            $errors = $decoded['errors'];
            if (is_array($errors)) {
                $flat = [];
                array_walk_recursive($errors, function ($value) use (&$flat): void {
                    if (is_string($value)) {
                        $flat[] = $value;
                    }
                });
                $message = $flat === [] ? null : implode('; ', $flat);
            } elseif (is_string($errors)) {
                $message = $errors;
            }
        }

        $requestId = null;
        if (isset($decoded['requestId']) && is_string($decoded['requestId'])) {
            $requestId = $decoded['requestId'];
        }

        return [$message, $requestId];
    }
}
