<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Serializers;

use Medas\Core\{
    CompiledValue,
    Exceptions\ServiceNotFoundByType,
    Exceptions\UuidProviderIsNotAvailable,
    Interfaces\CompilableArgumentProcessor,
    Interfaces\Uuid,
    Interfaces\UuidProvider,
    ProcessesByCompiling,
    Types\DateTime,
    Types\Integer
};

readonly class ArgumentDeserializer implements CompilableArgumentProcessor
{
    use ProcessesByCompiling;

    public function priority(): int
    {
        return -50;
    }

    /**
     * Only parameters whose (first) type is one this processor converts to are compiled; for all others it would
     * return the argument unchanged.
     */
    public function compile(\ReflectionParameter|\ReflectionProperty $parameter): CompiledValue|null
    {
        // Should be TypeFinder, but for parameters instead of just properties
        $types = normalizeType($parameter->getType());

        if ($types === []) {
            return null;
        }

        $name = $types[0]->getName();

        if ($name === \DateTime::class || $name === 'int' || is_a($name, Uuid::class, true)) {
            return new CompiledValue($name);
        }

        return null;
    }

    /**
     * @param CompiledValue $compiled
     */
    public function processCompiled(object $compiled, mixed $argument): mixed
    {
        if (is_object($argument)) {
            return $argument;
        }

        $type = null;
        $name = $compiled->value;

        if ($name === \DateTime::class) {
            $type = new DateTime();
        }
        elseif ($name === 'int') {
            $type = new Integer();
        }

        if (is_string($argument) && is_a($name, Uuid::class, true)) {
            try {
                $uuidProvider = service(UuidProvider::class);
            }
            catch (ServiceNotFoundByType) {
                throw new UuidProviderIsNotAvailable();
            }

            $argument = $uuidProvider->fromString($argument);
        }

        if ($type === null) {
            return $argument;
        }

        dispatch($request = new DeserializeRequest($argument, $type));

        return $request->result;
    }
}
