<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\HtmlResponse;

class OptionsResponse implements HtmlResponse
{
    public function __construct(
        private readonly string      $origin,
        private readonly string      $method,
        private readonly string|null $headers,
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
}
