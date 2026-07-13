<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Routes\GetEndpoints;

readonly class EndpointType
{
    public const string GetCollection = 'getCollection';
    public const string GetEntity = 'getEntity';
    public const string CreateEntity = 'createEntity';
    public const string UpdateEntity = 'updateEntity';
    public const string DeleteEntity = 'deleteEntity';
}
