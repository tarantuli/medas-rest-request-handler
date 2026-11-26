<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\Service,
    Date,
    Exceptions\InvalidDate,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Boolean,
    Types\Date as DateType,
    Types\DateTime,
    Types\FloatingPoint,
    Types\Integer,
    Types\Uuid as UuidType
};
use Medas\RestRequestHandler\Exceptions\{
    InvalidDateTimeFormat,
    QueryValueIsNotABoolean,
    QueryValueIsNotADate,
    QueryValueIsNotAnInteger,
    QueryValueIsNotANumber
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
            return $this->unserializeInteger($value);
        }

        if ($type instanceof FloatingPoint) {
            return $this->unserializeNumber($value);
        }

        if ($type instanceof DateType) {
            return $this->unserializeDate($value);
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

    // A plain (int) cast read "abc" as 0 and "12abc" as 12, so a mistyped filter compared
    // against a number nobody asked for (#407). The type's range is not checked: a filter
    // outside it, such as a count >= -1, is a valid question with a plain answer. Up to 18
    // digits, as more would not fit an int.
    private function unserializeInteger(mixed $value): int
    {
        if (!is_string($value) || !preg_match('/^-?\d{1,18}$/', $value)) {
            throw new QueryValueIsNotAnInteger((string) $value);
        }

        return (int) $value;
    }

    private function unserializeNumber(mixed $value): float
    {
        if (!is_numeric($value)) {
            throw new QueryValueIsNotANumber((string) $value);
        }

        return (float) $value;
    }

    // Passed on as text, a value that is not a date was compared by the database as it saw
    // fit (#407). Read as a Date, so the store writes it like any other date; one that does
    // not exist, such as 30 February, is refused rather than rolled over into March.
    private function unserializeDate(mixed $value): Date
    {
        if (!is_string($value) || !preg_match('/^(\d{4})-(\d{2})-(\d{2})$/', $value, $parts)) {
            throw new QueryValueIsNotADate((string) $value);
        }

        try {
            return new Date((int) $parts[1], (int) $parts[2], (int) $parts[3]);
        }
        catch (InvalidDate) {
            throw new QueryValueIsNotADate($value);
        }
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
