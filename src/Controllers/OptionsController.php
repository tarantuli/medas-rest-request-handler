<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\HttpRequestHandler\{
    Authorization\PublicResource,
    RequestDataManager,
    ResponseTypes\Response
};
use Medas\RestRequestHandler\{
    ConfigOptions\AllowedOrigins,
    Responses\BadRequestResponse,
    Responses\OptionsResponse
};
use Medas\Routing\{Methods\Options, Parameters\Anything, Route};

#[Service, Route(new Anything())]
readonly class OptionsController
{
    public function __construct(
        private RequestDataManager $requestDataManager,

        #[ConfigValue(AllowedOrigins::class)]
        private string             $allowedOrigins,
    )
    {
    }

    #[Options, PublicResource]
    public function getOptions(): Response
    {
        $serverData = $this->requestDataManager->get()->serverData;

        // Validate origin structure
        $origin = $this->validateOrigin($serverData['HTTP_ORIGIN'] ?? null);

        if ($origin === null) {
            return new BadRequestResponse();
        }

        // Additional regex check if configured
        if ($this->allowedOrigins !== '' && !preg_match($this->allowedOrigins, $origin)) {
            return new BadRequestResponse();
        }

        // Validate method
        $method = $serverData['HTTP_ACCESS_CONTROL_REQUEST_METHOD'] ?? null;
        $allowedMethods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH', 'OPTIONS', 'HEAD'];

        if ($method === null || !in_array($method, $allowedMethods, true)) {
            return new BadRequestResponse();
        }

        // Sanitize headers if present
        $headers = $serverData['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? null;

        if ($headers !== null) {
            $headers = str_replace(["\r", "\n", "\0"], '', $headers);
        }

        return new OptionsResponse($origin, $method, $headers);
    }

    private function validateOrigin(string|null $origin): string|null
    {
        // 1. Check origin exists
        if ($origin === null || $origin === '') {
            return null;
        }

        // 2. CRITICAL: Remove newlines to prevent header injection
        $origin = str_replace(["\r", "\n", "\0"], '', $origin);

        // 3. Validate it's a well-formed URL with an http/https scheme
        $parsed = parse_url($origin);

        if ($parsed === false) {
            // Malformed URL
            return null;
        }

        // 4. Ensure it has required components
        if (!isset($parsed['scheme']) || !isset($parsed['host'])) {
            return null;
        }

        // 5. Only allow http/https schemes
        if (!in_array($parsed['scheme'], ['http', 'https'], true)) {
            return null;
        }

        // 6. Reject if it has user/pass (unusual for origins)
        if (isset($parsed['user']) || isset($parsed['pass'])) {
            return null;
        }

        // 7. Reject if it has a path / query / fragment (origins shouldn't have these)
        if (isset($parsed['path']) || isset($parsed['query']) || isset($parsed['fragment'])) {
            return null;
        }

        // 8. Validate host is not empty and doesn't contain control characters
        if ($parsed['host'] === '' || preg_match('/[\x00-\x1F\x7F]/', $parsed['host'])) {
            return null;
        }

        // 9. Reconstruct the origin to normalize it
        $normalizedOrigin = $parsed['scheme'] . '://' . $parsed['host'];

        if (isset($parsed['port'])) {
            $normalizedOrigin .= ':' . $parsed['port'];
        }

        return $normalizedOrigin;
    }
}
