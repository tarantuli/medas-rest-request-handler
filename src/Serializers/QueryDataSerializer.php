<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{Attributes\Service, Interfaces\Type};
use Medas\EntityManager\Types\{Boolean, DateTime, Integer};

#[Service]
readonly class QueryDataSerializer extends JsonSerializer
{
    public function unserialize(mixed $value, Type $type = null): mixed
    {
        if ($value === 'null') {
            return null;
        }

        if ($type instanceof Integer) {
            return (int) $value;
        }

        if ($type instanceof Boolean) {
            return (bool) $value;
        }

        if ($type instanceof DateTime) {
            return \DateTime::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $value);
        }

        return $value;
    }
}
