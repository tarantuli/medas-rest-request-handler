<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\HttpRequestHandler\ResponseTypes\HtmlResponse;

class BadRequestResponse implements HtmlResponse
{
    public function outputHtmlResponse(): void
    {
        http_response_code(400);
    }
}
