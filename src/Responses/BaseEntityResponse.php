<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\RestRequestHandler\Interfaces\Normalizer;
use Medas\RestRequestHandler\Serializers\JsonSerializer;

abstract class BaseEntityResponse
{
    private readonly JsonSerializer $jsonSerializer;

    public function __construct(
        private readonly object $controller,
    )
    {
        $this->jsonSerializer = service(JsonSerializer::class);
    }

    public function normalizeEntity(object $entity): array
    {
        // Normalize the object to an array of values
        $data = $this->controller instanceof Normalizer
            ? $this->controller->normalize($entity)
            : get_object_vars($entity);

        // Then, serialize each value. Don't recurse, the serializer should flatten the values
        array_walk($data, fn($value) => $this->jsonSerializer->serialize($value));

        return $data;
    }
}
