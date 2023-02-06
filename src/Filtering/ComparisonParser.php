<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\Selector\Conditions\WhereIs;
use Medas\EntityManager\Selector\Operants\Property;
use Medas\EntityManager\Selector\Operants\Value;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class ComparisonParser
{
    public function parse(QuerySelector $querySelector, string $name, string $value): void
    {
        $querySelector->definition()->add(new WhereIs(
            Property::c($name),
            Value::c($value),
        ));
    }
}
