<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Filtering;

use Medas\EntityManager\Selector\Operants\Property;
use Medas\EntityManager\Selector\Sorting\SortBy;
use Medas\EntityManager\Selector\Sorting\SortDirection;
use Medas\RestRequestHandler\Exceptions\InvalidSortDirection;
use Medas\ServiceManager\Attributes\Service;

#[Service]
class MultisortParser
{
    public function parse(QuerySelector $querySelector, string $value): void
    {
        $parts = explode(',', $value);

        foreach ($parts as $part) {
            $direction = substr($part, 0, 1);

            if ($direction !== '<' && $direction !== '>') {
                throw new InvalidSortDirection($direction);
            }

            $property = substr($part, 1);

            $querySelector->definition()->add(
                SortBy::c(
                    Property::c($property),
                    $direction === '>' ? SortDirection::DESC : SortDirection::ASC
                )
            );
        }
    }
}
