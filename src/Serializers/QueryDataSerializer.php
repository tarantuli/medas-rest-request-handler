<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\Service,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Boolean,
    Types\DateTime,
    Types\Integer,
    Types\Uuid as UuidType
};

#[Service]
readonly class QueryDataSerializer implements Serializer
{
    public function __construct(
        private UuidProvider|null $uuidProvider,
    )
    {
    }

    public function serialize(mixed $value): mixed
    {
        if ($value instanceof HasId) {
            $value = $value->id();
        }

        if ($value instanceof Uuid) {
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

    public function unserialize(mixed $value, Type|null $type = null): mixed
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

        if ($type instanceof UuidType) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $value)) {
                if (!$this->uuidProvider) {
                    throw new UuidProviderIsNotAvailable();
                }

                return $this->uuidProvider->fromString($value);
            }
        }

        return $value;
    }
}
