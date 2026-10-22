<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

// Raised in development only: a collection query returned a row its read vote then
// refused. The row was counted and paged as if the caller could see it, so the
// entity misses an ownership filter (or has one that is too wide).
class ReadVoteRefusedSelectedRow extends BaseException
{
    public function __construct(string $entityClass, string $readVoteClass)
    {
        parent::__construct($readVoteClass, $entityClass);
    }

    public function pattern(): string
    {
        return '%s refused a row of %s that the collection query selected: add or narrow its ownership filter';
    }
}
