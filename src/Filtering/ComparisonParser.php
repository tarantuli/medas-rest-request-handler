<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Selector\{Conditions\WhereIs, Operants\Property, Operants\Value};
use Medas\EntityManager\Types\{Guid as GuidType, Relation};
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Values\Interfaces\GuidProvider;

#[Service]
class ComparisonParser
{
    public function __construct(
        private readonly MetaDataManager $metaDataManager,
        private readonly GuidProvider    $guidProvider,
    )
    {
    }

    public function parse(QuerySelector $querySelector, string $name, string $value): void
    {
        $metaData = $this->metaDataManager->get($querySelector->definition()->entity);
        $type = $metaData->property($name)->type;

        if ($type instanceof Relation) {
            $type = $this->metaDataManager->get($type->entity)->idProperty->type;
        }

        if ($type instanceof GuidType) {
            $value = $this->guidProvider->fromString($value);
        }

        $querySelector->definition()->add(new WhereIs(
            Property::c($name),
            Value::c($value),
        ));
    }
}
