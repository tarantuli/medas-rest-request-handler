<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering\FilterParser;

use Medas\Core\{Attributes\PreferredDefault, Attributes\Service, Interfaces\Serializer};
use Medas\EntityManager\Selector\{
    Conditions\WhereIn,
    Conditions\WhereIs,
    Conditions\WhereIsNot,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Conditions\WhereNotIn,
    Element,
    Operants\Property,
    Operants\Value,
    Operants\Values
};
use Medas\RestRequestHandler\Serializers\QueryDataSerializer;

#[Service]
readonly class ComparisonParser
{
    private const array ARRAY_VALUE_COMPARISON_TYPES = [WhereIn::class, WhereNotIn::class];

    public function __construct(
        #[PreferredDefault(QueryDataSerializer::class)]
        private Serializer $serializer,
    )
    {
    }

    public function parse(string $entity, string $name, string $comparisonType, string $value, mixed $type): Element
    {
        if (in_array($comparisonType, self::ARRAY_VALUE_COMPARISON_TYPES, true)) {
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
