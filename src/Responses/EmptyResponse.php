<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\{HtmlResponse, JsonResponse, SetsResponseCode};

abstract class EmptyResponse implements HtmlResponse, JsonResponse, SetsResponseCode
{
    public function getHtmlResponse(): string
    {
        return '';
    }

    public function getJsonResponse(): null
    {
        return null;
    }
}
