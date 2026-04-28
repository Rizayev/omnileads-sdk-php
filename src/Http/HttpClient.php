<?php

declare(strict_types=1);

namespace Madtec\OmniLeads\Http;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Madtec\OmniLeads\Config\OmniLeadsConfig;
use Madtec\OmniLeads\Exceptions\ApiException;
use Madtec\OmniLeads\Exceptions\AuthenticationException;
use Madtec\OmniLeads\Exceptions\NotFoundException;
use Madtec\OmniLeads\Exceptions\ServerException;
use Madtec\OmniLeads\Exceptions\ValidationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use Psr\Log\NullLogger;
use Throwable;

final class HttpClient implements HttpClientInterface
{
    private readonly ClientInterface $guzzle;

    private readonly LoggerInterface $logger;

    public function __construct(
        private readonly OmniLeadsConfig $config,
        ?ClientInterface $guzzle = null,
        ?LoggerInterface $logger = null,
    ) {
        $this->guzzle = $guzzle ?? new GuzzleClient([
            'base_uri' => $config->baseUrl.'/',
            'timeout' => $config->timeout,
            'connect_timeout' => $config->timeout,
            'http_errors' => true,
        ]);

        $this->logger = $logger ?? new NullLogger;
    }

    /**
     * @param  array<string, mixed>  $options
     * @return array<string, mixed>|list<mixed>
     */
    public function request(string $method, string $uri, array $options = []): array
    {
        $maxAttempts = $this->config->retry->enabled ? max(1, $this->config->retry->times) : 1;
        $attempt = 0;
        $lastThrowable = null;

        while ($attempt < $maxAttempts) {
            $attempt++;

            try {
                $response = $this->dispatch($method, $uri, $options);

                $this->logger->debug('OmniLeads request OK', [
                    'method' => $method,
                    'uri' => $uri,
                    'status' => $response->getStatusCode(),
                    'attempt' => $attempt,
                ]);

                return $this->parseBody($response);
            } catch (BadResponseException $e) {
                $statusCode = $e->getResponse()->getStatusCode();

                if ($this->shouldRetry($statusCode) && $attempt < $maxAttempts) {
                    $this->logger->warning('OmniLeads retryable HTTP error', [
                        'method' => $method,
                        'uri' => $uri,
                        'status' => $statusCode,
                        'attempt' => $attempt,
                    ]);

                    $this->backoff($attempt);

                    continue;
                }

                throw $this->mapHttpException($e, $method, $uri);
            } catch (ConnectException $e) {
                $lastThrowable = $e;

                if ($attempt < $maxAttempts) {
                    $this->logger->warning('OmniLeads connection error, retrying', [
                        'method' => $method,
                        'uri' => $uri,
                        'attempt' => $attempt,
                        'error' => $e->getMessage(),
                    ]);

                    $this->backoff($attempt);

                    continue;
                }

                break;
            } catch (RequestException $e) {
                if ($e->hasResponse()) {
                    $response = $e->getResponse();
                    if ($response !== null) {
                        $statusCode = $response->getStatusCode();

                        if ($this->shouldRetry($statusCode) && $attempt < $maxAttempts) {
                            $this->backoff($attempt);

                            continue;
                        }

                        throw $this->mapHttpException($e, $method, $uri);
                    }
                }

                $lastThrowable = $e;

                if ($attempt < $maxAttempts) {
                    $this->backoff($attempt);

                    continue;
                }

                break;
            } catch (GuzzleException $e) {
                $lastThrowable = $e;

                break;
            }
        }

        throw new ApiException(
            statusCode: 0,
            responseBody: '',
            requestMethod: $method,
            requestUri: $uri,
            errorMessage: $lastThrowable?->getMessage() ?? 'Network error',
            previous: $lastThrowable instanceof Throwable ? $lastThrowable : null,
        );
    }

    /**
     * @param  array<string, mixed>  $options
     */
    private function dispatch(string $method, string $uri, array $options): ResponseInterface
    {
        return $this->guzzle->request($method, ltrim($uri, '/'), $options);
    }

    private function shouldRetry(int $statusCode): bool
    {
        return $statusCode >= 500 && $statusCode < 600;
    }

    private function backoff(int $attempt): void
    {
        $base = max(0, $this->config->retry->sleepMs);
        if ($base === 0) {
            return;
        }

        $delayMs = $base * (2 ** ($attempt - 1));
        usleep($delayMs * 1000);
    }

    /**
     * @return array<string, mixed>|list<mixed>
     */
    private function parseBody(ResponseInterface $response): array
    {
        $body = (string) $response->getBody();

        if ($body === '') {
            return [];
        }

        $decoded = json_decode($body, true);

        if (! is_array($decoded)) {
            throw new ApiException(
                statusCode: $response->getStatusCode(),
                responseBody: $body,
                requestMethod: '',
                requestUri: '',
                errorMessage: 'Failed to decode JSON response: '.json_last_error_msg(),
            );
        }

        return $decoded;
    }

    private function mapHttpException(BadResponseException|RequestException $e, string $method, string $uri): ApiException
    {
        $response = $e->getResponse();
        if ($response === null) {
            return new ApiException(
                statusCode: 0,
                responseBody: '',
                requestMethod: $method,
                requestUri: $uri,
                errorMessage: $e->getMessage(),
                previous: $e,
            );
        }

        $statusCode = $response->getStatusCode();
        $body = (string) $response->getBody();

        return match (true) {
            $statusCode === 400 => ValidationException::fromResponse($statusCode, $body, $method, $uri, $e),
            $statusCode === 401, $statusCode === 403 => AuthenticationException::fromResponse($statusCode, $body, $method, $uri, $e),
            $statusCode === 404 => NotFoundException::fromResponse($statusCode, $body, $method, $uri, $e),
            $statusCode >= 500 => ServerException::fromResponse($statusCode, $body, $method, $uri, $e),
            default => ApiException::fromResponse($statusCode, $body, $method, $uri, $e),
        };
    }
}
