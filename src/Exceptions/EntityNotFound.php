<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Exceptions;

use Medas\Core\Exceptions\BaseException;
use Medas\HttpRequestHandler\Exceptions\DeclaresResponseCode;

/**
 * The entity a request addresses by id does not exist. Answered with 404.
 */
class EntityNotFound extends BaseException implements DeclaresResponseCode
{
    private const int NOT_FOUND = 404;

    public function __construct(string $entity, mixed $id)
    {
        // The short class name: enough for the client to tell what was missing,
        // without exposing the backend's namespaces.
        parent::__construct(substr(strrchr('\\' . $entity, '\\'), 1), (string) $id);
    }

    public function pattern(): string
    {
        return 'no %s found with id %s';
    }

    public function responseCode(): int
    {
        return self::NOT_FOUND;
    }
}
