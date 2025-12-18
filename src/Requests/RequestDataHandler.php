<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Requests;

use Medas\Core\{Attributes\PreferredDefault, Attributes\Service, Interfaces\Serializer};
use Medas\HttpRequestHandler\RequestDataManager;
use Medas\RestRequestHandler\{Interfaces\Denormalizer, Serializers\RestSerializer};

#[Service]
readonly class RequestDataHandler
{
    public function __construct(
        protected RequestDataManager $requestDataManager,

        #[PreferredDefault(RestSerializer::class)]
        private Serializer           $serializer,
    )
    {
    }

    public function deserialize(array $data, object|null $denormalizer): array
    {
        // Deserialize each value. Don't recurse, the serializer should handle depth
        array_walk($data, fn($value) => $this->serializer->unserialize($value));

        // Then, let the controller denormalize the values, if it can
        if ($denormalizer instanceof Denormalizer) {
            $data = $denormalizer->denormalize($data);
        }

        return $data;
    }

    public function getBodyData(object $controller): array
    {
        return $this->deserialize($this->requestDataManager->get()->bodyData->data(), $controller);
    }

    public function getQueryData(): array
    {
        return $this->requestDataManager->get()->uri->query;
    }
}
