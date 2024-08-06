<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\{HtmlResponse, JsonResponse};

class BadRequestResponse implements HtmlResponse, JsonResponse
{
    public function outputHtmlResponse(): void
    {
        if (!headers_sent()) {
            http_response_code(400);
        }
    }

    public function getJsonResponse(): null
    {
        $this->outputHtmlResponse();

        return null;
    }
}
