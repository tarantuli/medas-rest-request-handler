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
        header('Access-Control-Allow-Origin: ' . $this->origin);
        header('Access-Control-Allow-Methods: ' . $this->method);

        if ($this->headers !== null) {
            header('Access-Control-Allow-Headers: ' . $this->headers);
        }
    }

    public function getJsonResponse(): string
    {
        $this->outputHtmlResponse();

        return '';
    }
}
