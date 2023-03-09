<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;

class GuidProviderIsNotAvailable extends BaseException
{
    public function pattern(): string
    {
        return 'no GuidProvider is provided, but it is needed to denormalize a Guid value';
    }
}
