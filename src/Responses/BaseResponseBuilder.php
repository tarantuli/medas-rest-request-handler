<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Responses;

use Medas\Core\{Attributes\PreferredDefault, Interfaces\Serializer};
use Medas\RestRequestHandler\{Interfaces\Normalizer, Serializers\RestSerializer};

abstract class BaseResponseBuilder
{
    public function __construct(
        #[PreferredDefault(RestSerializer::class)]
        private readonly Serializer $serializer,
    )
    {
    }

    protected function serialize(object $entity, object|null $normalizer): array
    {
        // Normalize the object to an array of values
        $data = $normalizer instanceof Normalizer
            ? $normalizer->normalize($entity)
            : get_object_vars($entity);

        // Then, serialize each value. Don't recurse, the serializer should flatten the values
        array_walk($data, fn(&$value) => $value = $this->serializer->serialize($value));

        return $data;
    }
}
