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
use Medas\RestRequestHandler\Exceptions\{InvalidDateTimeFormat, QueryValueIsNotABoolean};

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
            return $this->unserializeBoolean($value);
        }

        if ($type instanceof DateTime) {
            return $this->unserializeDateTime($value);
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

    // A query value is a raw string, and a plain (bool) cast reads "false" as true. Both
    // spellings are accepted: true/false as a response serves a boolean, 1/0 as clients
    // sent it before. Anything else is refused rather than guessed.
    private function unserializeBoolean(mixed $value): bool
    {
        return match ($value) {
            'true', '1' => true,
            'false', '0' => false,
            default => throw new QueryValueIsNotABoolean((string) $value),
        };
    }

    // createFromFormat() answers false for a value it cannot read; compared as is, that
    // false selected nothing, so a mistyped date-time looked like an empty result.
    private function unserializeDateTime(mixed $value): \DateTime
    {
        $dateTime = \DateTime::createFromFormat(
            \DateTimeInterface::RFC3339_EXTENDED,
            (string) $value
        );

        if ($dateTime === false) {
            throw new InvalidDateTimeFormat((string) $value);
        }

        return $dateTime;
    }
}
