<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\EventListener,
    Attributes\Service,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\Collection,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Types\Boolean,
    Types\Collection as TypesCollection,
    Types\DateTime,
    Types\Integer,
    Types\Relation,
    Types\Uuid as UuidType
};
use Medas\EntityManager\Events\FindEntity;
use Medas\RestRequestHandler\Exceptions\EntityNotFound;

#[Service]
readonly class RestSerializer implements Serializer
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

        if ($value instanceof Collection) {
            $value = array_map(fn($value) => $this->serialize($value), iterator_to_array($value));
        }

        return $value;
    }

    public function unserialize(mixed $value, Type|null $type = null): mixed
    {
        if ($value === null) {
            return null;
        }

        if ($type instanceof UuidType) {
            if ($this->uuidProvider === null) {
                throw new UuidProviderIsNotAvailable();
            }

            if (!is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'UUID value must be a string, %s given',
                    get_debug_type($value)
                ));
            }

            $value = $this->uuidProvider->fromString($value);
        }

        if ($type instanceof Relation) {
            if (!is_string($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Relation value must be a string UUID, %s given',
                    get_debug_type($value)
                ));
            }

            $value = $this->resolveRelation($type, $this->uuidProvider->fromString($value));
        }

        if ($type instanceof TypesCollection) {
            if (!is_array($value)) {
                throw new \InvalidArgumentException(sprintf(
                    'Collection value must be an array, %s given',
                    get_debug_type($value)
                ));
            }

            /** @var Collection $newValue */
            $newValue = new ($type->collectionType)();

            foreach ($value as $item) {
                if (!is_string($item)) {
                    throw new \InvalidArgumentException(sprintf(
                        'Collection items must be string UUIDs, %s given',
                        get_debug_type($item)
                    ));
                }

                $newValue[] = $this->resolveRelation(
                    new Relation($type->contentType),
                    $this->uuidProvider->fromString($item)
                );
            }

            $value = $newValue;
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

    private function resolveRelation(Relation $type, mixed $value): object
    {
        if (enum_exists($type->entity)) {
            $reflection = new \ReflectionEnum($type->entity);
            $backingType = (string) $reflection->getBackingType();

            if ($backingType === 'string' && !is_string($value)) {
                $value = (string) $value;
            }
            elseif ($backingType === 'int' && !is_int($value)) {
                $value = (int) $value;
            }

            /** @noinspection PhpUndefinedMethodInspection */
            $value = ($type->entity)::from($value);
        }
        else {
            dispatch($event = new FindEntity($type->entity, $value));

            if (!$event->entity) {
                throw new EntityNotFound($type->entity, $value);
            }

            $value = $event->entity;
        }

        return $value;
    }

    #[EventListener]
    public function handleEvent(DeserializeRequest $request): void
    {
        $request->result = $this->unserialize($request->argument, $request->type);
    }
}
