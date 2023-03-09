<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\EntityManager\Types\{Boolean, Guid as GuidType, Relation};
use Medas\RamseyUuidBridge\GuidProvider;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Exceptions\GuidProviderIsNotAvailable;
use Medas\ServiceManager\Interfaces\{Guid, HasId, Serializer, Type};

#[Service]
class JsonSerializer implements Serializer
{
    public function __construct(
        private readonly GuidProvider|null $guidProvider,
    )
    {
    }

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

        return $value;
    }

    public function unserialize(Type $type, mixed $value): mixed
    {
        if ($type instanceof GuidType) {
            if ($this->guidProvider === null) {
                throw new GuidProviderIsNotAvailable();
            }

            $value = $this->guidProvider->fromString($value);
        }

        if ($type instanceof Relation) {
            $value = em()->get($type->entity, $value);
        }

        if ($type instanceof Boolean) {
            $value = (bool) $value;
        }

        return $value;
    }
}
