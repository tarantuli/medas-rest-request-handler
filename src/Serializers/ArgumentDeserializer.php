<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\Attributes\{PreferredDefault, Service};
use Medas\Core\Interfaces\{ArgumentProcessor, Serializer};
use Medas\EntityManager\Types\DateTime;

#[Service]
class ArgumentDeserializer implements ArgumentProcessor
{
    public function __construct(
        #[PreferredDefault(JsonSerializer::class)]
        private readonly Serializer $serializer,
    )
    {
    }

    public function priority(): int
    {
        return -50;
    }

    public function process(\ReflectionParameter|\ReflectionProperty $parameter, mixed $argument): mixed
    {
        if (is_object($argument)) {
            return $argument;
        }

        // Should be TypeFinder, but for parameters instead of just properties
        $type = null;

        if (parameterTypes($parameter)[0]->getName() === \DateTime::class) {
            $type = new DateTime();
        }

        return $this->serializer->unserialize($argument, $type);
    }
}
