<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\Service,
    Interfaces\Guid,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type
};
use Medas\EntityManager\Types\{Boolean, DateTime, Integer};

#[Service]
readonly class QueryDataSerializer implements Serializer
{
    public function serialize(mixed $value): mixed
    {
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof Guid) {
            return (string) $value;
        }

        if (is_bool($value)) {
            $value = (int) $value;
        }

        if ($value instanceof \DateTimeInterface) {
            $value = $value->format(\DateTimeInterface::RFC3339_EXTENDED);
        }

        return $value;
    }

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
