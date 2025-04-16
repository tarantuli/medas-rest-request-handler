<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{
    Attributes\ConfigValue,
    Attributes\PreferredDefault,
    Attributes\Service,
    Interfaces\Serializer
};
use Medas\EntityManager\Selector\{
    Conditions\WhereContains,
    Conditions\WhereEndsWith,
    Conditions\WhereIn,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsNot,
    Conditions\WhereIsNotNull,
    Conditions\WhereIsNull,
    Conditions\WhereNotIn,
    Conditions\WhereStartsWith,
    Element,
    Operants\Property,
    Operants\Value,
    Operants\Values
};
use Medas\RestRequestHandler\ConfigOptions\ComparisonOperators\IsLessThanOperator;
use Medas\RestRequestHandler\Serializers\QueryDataSerializer;

#[Service]
readonly class ComparisonParser
{
    private const ARRAY_VALUE_COMPARISON_TYPES = [WhereIn::class, WhereNotIn::class];

    public function __construct(
        #[PreferredDefault(QueryDataSerializer::class)]
        private Serializer  $serializer,

        #[ConfigValue(IsLessThanOperator::class)]
        private string|null $isLessThanOperator,
    )
    {
    }

    public function parse(string $name, string $value, \Closure $typeFinder = null): Element
    {
        $comparisonType = $this->extractComparisonType($name);
        $type = $typeFinder ? $typeFinder($name) : null;

        if (in_array($comparisonType, self::ARRAY_VALUE_COMPARISON_TYPES, true)) {
            $element = $this->createArrayValueElement($name, $comparisonType, $value, $type);
        }
        else {
            $element = $this->createSingletonValueElement($name, $comparisonType, $value, $type);
        }

        return $element;
    }

    private function extractComparisonType(&$name): string
    {
        if ($this->isLessThanOperator !== null && str_ends_with($name, $this->isLessThanOperator)) {
            $name = substr($name, 0, -strlen($this->isLessThanOperator));

            return WhereIsLessThan::class;
        }

        if (str_ends_with($name, '<')) {
            $name = substr($name, 0, -1);

            return WhereIsAtMost::class;
        }

        if (str_ends_with($name, '>>')) {
            $name = substr($name, 0, -2);

            return WhereIsMoreThan::class;
        }

        if (str_ends_with($name, '>')) {
            $name = substr($name, 0, -1);

            return WhereIsAtLeast::class;
        }

        if (str_ends_with($name, '^')) {
            $name = substr($name, 0, -1);

            return WhereStartsWith::class;
        }

        if (str_ends_with($name, '$')) {
            $name = substr($name, 0, -1);

            return WhereEndsWith::class;
        }

        if (str_ends_with($name, '*')) {
            $name = substr($name, 0, -1);

            return WhereContains::class;
        }

        if (str_ends_with($name, '!')) {
            $name = substr($name, 0, -1);

            return WhereIsNot::class;
        }

        if (str_ends_with($name, '∈')) {
            $name = substr($name, 0, -strlen('∈'));

            return WhereIn::class;
        }

        if (str_ends_with($name, '∉')) {
            $name = substr($name, 0, -strlen('∉'));

            return WhereNotIn::class;
        }

        return WhereIs::class;
    }

    private function createArrayValueElement(
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
            Property::c($name),
            Values::c($values),
        );
    }

    private function createSingletonValueElement(
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
            Property::c($name),
            Value::c($unserializedValue),
        );
    }
}
