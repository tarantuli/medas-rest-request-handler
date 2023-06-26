<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Requests;

use Medas\Core\Attributes\Service;
use Medas\Core\Interfaces\Serializer;
use Medas\ObjectInstantiator\Attributes\PreferredDefault;
use Medas\RestRequestHandler\{Interfaces\Denormalizer, Serializers\JsonSerializer};

#[Service]
class RequestDataHandler
{
    public function __construct(
        #[PreferredDefault(JsonSerializer::class)]
        private readonly Serializer $serializer,
    )
    {
    }

    public function deserialize(array $data, object $controller): array
    {
        // Deserialize each value. Don't recurse, the serializer should handle depth
        array_walk($data, fn($value) => $this->serializer->serialize($value));

        // Then, let the controller denormalize the values, if it can
        if ($controller instanceof Denormalizer) {
            $data = $controller->denormalize($data);
        }

        return $data;
    }
}
