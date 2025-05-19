<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\Generator\{
    Exceptions\ClassHasNoNamespace,
    NameConverters\NameConverter
};

#[Service]
readonly class ClassGenerator
{
    public function __construct(
        private NameConverter $storeNameConverter,
    )
    {
    }

    public function generate(string $entityClassName, string $handlerClassName, string $template): string
    {
        [, $entityShortClassName] = $this->splitClassName($entityClassName);
        $routePath = $this->storeNameConverter->convert($entityShortClassName);
        [$namespace, $shortClassName] = $this->splitClassName($handlerClassName);

        $replacements = [
            '{{namespace}}' => $namespace,
            '{{routePath}}' => $routePath,
            '{{shortClassName}}' => $shortClassName,
            '{{entityClassName}}' => $entityClassName,
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function splitClassName(string $className): array
    {
        $pos = strrpos($className, '\\');

        if ($pos === false) {
            throw new ClassHasNoNamespace($className);
        }

        return [
            substr($className, 0, $pos),
            substr($className, $pos + 1),
        ];
    }
}
