<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\Core\{Attributes\PreferredDefault, Attributes\Service, Interfaces\Serializer};
use Medas\EntityManager\Exceptions\ClassIsNotAnEntity;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\WhereContains,
    Conditions\WhereEndsWith,
    Conditions\WhereIs,
    Conditions\WhereIsAtLeast,
    Conditions\WhereIsAtMost,
    Conditions\WhereIsLessThan,
    Conditions\WhereIsMoreThan,
    Conditions\WhereIsNot,
    Conditions\WhereStartsWith,
    Operants\Property,
    Operants\Value};
use Medas\EntityManager\Types\Relation;
use Medas\RestRequestHandler\Serializers\JsonSerializer;

#[Service]
readonly class ComparisonParser
{
    public function __construct(
        private MetaDataManager $metaDataManager,

        #[PreferredDefault(JsonSerializer::class)]
        private Serializer      $serializer,
    )
    {
    }

    public function parse(QuerySelector $querySelector, string $name, string $value): void
    {
        $comparisonType = $this->getComparisonType($name);
        $type = null;

        try {
            $metaData = $this->metaDataManager->get($querySelector->entity());
            $type = $metaData->property($name)->type;

            if ($type instanceof Relation) {
                $type = $this->metaDataManager->get($type->entity)->idProperty->type;
            }
        }
        catch (ClassIsNotAnEntity) {
            // Do nothing
        }

        $value = $this->serializer->unserialize($value, $type);

        $element = new $comparisonType(
            Property::c($name),
            Value::c($value),
        );

        $querySelector->definition()->add($element);
    }

    private function getComparisonType(&$name): string
    {
        if (str_ends_with($name, '<<')) {
            $name = substr($name, 0, -2);

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

        return WhereIs::class;
    }
}
