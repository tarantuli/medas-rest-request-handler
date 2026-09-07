<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    Attributes\DataHolder as DataHolderAttribute,
    Attributes\EventListener,
    Attributes\Service,
    Date,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\Collection,
    Interfaces\HasId,
    Interfaces\Serializer,
    Interfaces\Type,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    Period,
    Types\Boolean,
    Types\Collection as TypesCollection,
    Types\DataHolder as DataHolderType,
    Types\Date as DateType,
    Types\DateTime,
    Types\Integer,
    Types\Period as PeriodType,
    Types\Relation,
    Types\Uuid as UuidType
};
use Medas\EntityManager\{Entities\IdCaster, Events\FindEntity};
use Medas\ObjectToArraySerializer\ObjectToArraySerializer;
use Medas\RestRequestHandler\Exceptions\{EntityNotFound, InvalidDateFormat};

#[Service]
readonly class RestSerializer implements Serializer
{
    public function __construct(
        private IdCaster                $idCaster,
        private ObjectToArraySerializer $objectToArraySerializer,
        private UuidProvider|null       $uuidProvider,
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

        if ($value instanceof Date) {
            $value = sprintf('%04d-%02d-%02d', $value->year, $value->month, $value->day);
        }

        if ($value instanceof Period) {
            $value = $value->toString();
        }

        if (is_object($value) && attribute(DataHolderAttribute::class, new \ReflectionClass($value::class))) {
            return $this->objectToArraySerializer->serialize($value);
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
            // resolveRelation branches on enum vs entity and, for an entity,
            // coerces the scalar to the entity's own id type via IdCaster.
            $value = $this->resolveRelation($type, $value);
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
                $newValue[] = $this->resolveRelation(new Relation($type->contentType), $item);
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

        if ($type instanceof DateType) {
            $parsed = \DateTime::createFromFormat('!Y-m-d', $value);

            if ($parsed === false) {
                throw new InvalidDateFormat($value);
            }

            $value = new Date(
                (int) $parsed->format('Y'),
                (int) $parsed->format('m'),
                (int) $parsed->format('d'),
            );
        }

        if ($type instanceof PeriodType) {
            $value = Period::fromString($value);
        }

        if ($type instanceof DataHolderType) {
            $value = $this->objectToArraySerializer->unserialize($value, $type, $type->className);
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
            // The related entity decides its own id type - Uuid, string or int -
            // so coerce the incoming scalar to match rather than assuming Uuid.
            dispatch($event = new FindEntity($type->entity, $this->idCaster->cast($type->entity, $value)));

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
