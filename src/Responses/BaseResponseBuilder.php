<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\core\Interfaces\Serializer;
use Medas\RestRequestHandler\Interfaces\Normalizer;
use Medas\RestRequestHandler\Serializers\JsonSerializer;
use Medas\ServiceManager\Attributes\PreferredDefault;

abstract class BaseResponseBuilder
{
    public function __construct(
        #[PreferredDefault(JsonSerializer::class)]
        private readonly Serializer $serializer,
    )
    {
    }

    protected function serialize(object $entity, object $controller): array
    {
        // Normalize the object to an array of values
        $data = $controller instanceof Normalizer
            ? $controller->normalize($entity)
            : get_object_vars($entity);

        // Then, serialize each value. Don't recurse, the serializer should flatten the values
        array_walk($data, fn($value) => $this->serializer->serialize($value));

        return $data;
    }
}
