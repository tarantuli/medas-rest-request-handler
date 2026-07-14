<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

class UnauthorizedResponse extends EmptyResponse
{
    public function responseCode(): int
    {
        return 403;
    }
}
