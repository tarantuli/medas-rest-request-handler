<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\HttpRequestHandler\Exceptions\BadRequest;

/**
 * A GetCollection request asks for an include the route does not offer. Refused
 * rather than ignored, so a client asking for a misspelt or removed value learns that
 * it will not get it.
 */
class UnknownInclude extends BadRequest
{
    /** @param list<string> $offered */
    public function __construct(string $include, array $offered)
    {
        parent::__construct($include, $offered === [] ? 'none' : implode(', ', $offered));
    }

    public function pattern(): string
    {
        return 'there is no include %s here; this collection offers: %s';
    }
}
