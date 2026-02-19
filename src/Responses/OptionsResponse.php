<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\{HtmlResponse, JsonResponse};

readonly class OptionsResponse implements HtmlResponse, JsonResponse
{
    public function __construct(
        private string      $origin,
        private string      $method,
        private string|null $headers,
    )
    {
    }

    public function outputHtmlResponse(): void
    {
        // Defense in depth: sanitize all values even though controller should have done it
        $origin = str_replace(["\r", "\n", "\0"], '', $this->origin);
        $method = str_replace(["\r", "\n", "\0"], '', $this->method);

        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: ' . $method);

        if ($this->headers !== null) {
            $headers = str_replace(["\r", "\n", "\0"], '', $this->headers);

            header('Access-Control-Allow-Headers: ' . $headers);
        }
    }

    public function getJsonResponse(): string
    {
        $this->outputHtmlResponse();

        return '';
    }
}
