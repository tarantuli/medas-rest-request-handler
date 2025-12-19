<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\Service,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\Collection,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Boolean,
    Types\DateTime,
    Types\Integer,
    Types\Relation,
    Types\Uuid as UuidType
};

#[Service]
readonly class RestSerializer implements Serializer
{
    public function __construct(
        private UuidProvider|null $UuidProvider,
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

        if ($value instanceof Collection) {
            $value = array_map(fn($value) => $this->serialize($value), iterator_to_array($value));
        }

        return $value;
    }

    public function unserialize(mixed $value, Type|null $type = null): mixed
    {
        if ($type instanceof UuidType) {
            if ($this->UuidProvider === null) {
                throw new UuidProviderIsNotAvailable();
            }

            $value = $this->UuidProvider->fromString($value);
        }

        if ($type instanceof Relation) {
            if (enum_exists($type->entity)) {
                $reflection = new \ReflectionEnum($type->entity);
                $backingType = (string) $reflection->getBackingType();

                if ($backingType === 'string' && !is_string($value)) {
                    $value = (string) $value;
                }
                elseif ($backingType === 'int' && !is_int($value)) {
                    $value = (int) $value;
                }

                $value = ($type->entity)::from($value);
            }
            else {
                $value = em()->get($type->entity, $value);
            }
        }

        if ($type instanceof Boolean) {
            $value = (bool) $value;
        }

        if ($type instanceof Integer) {
            $value = (int) $value;
        }

        if ($type instanceof DateTime) {
            $value = \DateTime::createFromFormat(\DateTimeInterface::RFC3339_EXTENDED, $value);
        }

        return $value;
    }
}
