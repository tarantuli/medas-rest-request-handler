<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\{HtmlResponse, JsonResponse, SetsResponseCode};

class BadRequestResponse implements HtmlResponse, JsonResponse, SetsResponseCode
{
    public function outputHtmlResponse(): void
    {
        // Do nothing
    }

    public function getJsonResponse(): null
    {
        return null;
    }

    public function responseCode(): int
    {
        return 400;
    }
}
