<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

class DeserializeRequest
{
    public mixed $result = null;

    public function __construct(
        public mixed $argument,
        public mixed $type,
    )
    {
    }
}
