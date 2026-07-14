<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

class UnauthenticatedResponse extends EmptyResponse
{
    public function responseCode(): int
    {
        return 401;
    }
}
