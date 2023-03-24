<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\EntityManager\Types\DateTime;
use Medas\ServiceManager\Attributes\PreferredDefault;
use Medas\ServiceManager\Attributes\Service;
use Medas\ServiceManager\Interfaces\Serializer;
use Medas\ServiceManager\ParameterResolving\ArgumentProcessor;

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
