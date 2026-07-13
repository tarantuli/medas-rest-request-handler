<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class TwoClassesMapToSameEntityName extends BaseException
{
    public function __construct(
        string $firstClass,
        string $secondClass,
        string $name,
    )
    {
        parent::__construct($firstClass, $secondClass, $name);
    }

    public function pattern(): string
    {
        return 'both %s and %s resolve to entity name "%s" - add #[EntityAlias] to one of them to disambiguate';
    }
}
