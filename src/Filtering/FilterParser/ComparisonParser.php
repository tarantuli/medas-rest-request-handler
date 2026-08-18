<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\Serializer
};
use Medas\EntityManager\Selector\{
    Conditions\IsArrayComparison,
    Conditions\WhereIs,
    Conditions\WhereIsNot,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Element,
    Operants\Property,
    Operants\Value,
    Operants\Values
};
use Medas\RestRequestHandler\{
    ConfigOptions\NullSentinel,
    Exceptions\CannotParseQueryValue,
    Serializers\QueryDataSerializer
};

#[Service]
readonly class ComparisonParser
{
    public function __construct(
        #[PreferredDefault(QueryDataSerializer::class)]
        private Serializer  $serializer,

        #[ConfigValue(NullSentinel::class)]
        private string|null $nullSentinel,
    )
    {
    }

    public function parse(string $entity, string $name, string $comparisonType, string $value, mixed $type): Element
    {
        // A value equal to the null sentinel means "is null" (with the equality
        // operator) or "is not null" (with the not-equal operator), handled here
        // before any type-aware deserialization.
        if ($this->nullSentinel !== null && $value === $this->nullSentinel) {
            return $this->createNullElement($entity, $name, $comparisonType, $value);
        }

        if (new \ReflectionClass($comparisonType)->implementsInterface(IsArrayComparison::class)) {
            $element = $this->createArrayValueElement(
                $entity,
                $name,
                $comparisonType,
                $value,
                $type
            );
        }
        else {
            $element = $this->createSingletonValueElement(
                $entity,
                $name,
                $comparisonType,
                $value,
                $type
            );
        }

        return $element;
    }

    private function createNullElement(string $entity, string $name, string $comparisonType, string $value): Element
    {
        // The null sentinel is only meaningful for equality: contains, ranges,
        // in/not-in etc. against null are nonsensical, so reject them.
        $conditionClass = match ($comparisonType) {
            WhereIs::class => WhereIsNull::class,
            WhereIsNot::class => WhereIsNotNull::class,
            default => throw new CannotParseQueryValue($name, $value),
        };

        // A null check on a non-nullable property can never match, so it's a
        // query mistake rather than a valid empty result.
        $propertyType = new \ReflectionProperty($entity, $name)->getType();

        if ($propertyType !== null && !$propertyType->allowsNull()) {
            throw new CannotParseQueryValue($name, $value);
        }

        return new $conditionClass(Property::c($name, $entity));
    }

    private function createArrayValueElement(
        string $entity,
        string $name,
        string $comparisonType,
        string $value,
        mixed  $type
    ): Element
    {
        $values = [];

        foreach (explode(',', $value) as $value) {
            $values[] = $this->serializer->unserialize($value, $type);
        }

        return new $comparisonType(
            Property::c($name, $entity),
            Values::c($values),
        );
    }

    private function createSingletonValueElement(
        string $entity,
        string $name,
        string $comparisonType,
        string $value,
        mixed  $type
    ): Element
    {
        $unserializedValue = $this->serializer->unserialize($value, $type);

        if ($unserializedValue === null) {
            if ($comparisonType === WhereIs::class) {
                $comparisonType = WhereIsNull::class;
            }
            elseif ($comparisonType === WhereIsNot::class) {
                $comparisonType = WhereIsNotNull::class;
            }
        }

        return new $comparisonType(
            Property::c($name, $entity),
            Value::c($unserializedValue),
        );
    }
}
