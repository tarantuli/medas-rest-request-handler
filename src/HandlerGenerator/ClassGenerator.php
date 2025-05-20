<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\Service;
use Medas\EntityManager\Entities\Generator\{
    ClassNameNormalizer,
    Exceptions\ClassHasNoNamespace,
    FileNameFinder,
    NameConverters\NameConverter
};

#[Service]
readonly class ClassGenerator
{
    public function __construct(
        private ClassNameNormalizer $classNameNormalizer,
        private NameConverter       $storeNameConverter,
        private FileNameFinder      $fileNameFinder,
    )
    {
    }

    public function generate(
        string $entityClassName,
        string $template,
        string $handlerPrefix,
        string $handlerSuffix
    ): void
    {
        $entityClassName = $this->classNameNormalizer->normalize($entityClassName);
        $prefix = rtrim($this->fileNameFinder->findPrefix($entityClassName), '\\');

        $pattern = '#^('
            . str_replace('\\', '\\\\', $prefix)
            . ')('
            . '\\\\'
            . '.+)?'
            . '\\\\'
            . '(\w+)$#';

        if (!preg_match($pattern, $entityClassName, $match)) {
            exit($entityClassName . ' is not a valid entity class name');
        }

        [, $entityShortClassName] = $this->splitClassName($entityClassName);

        $handlerClassName = $prefix
            . '\RestControllers'
            . ($match[2] ?? '\\')
            . '\\'
            . $entityShortClassName
            . '\\'
            . $handlerPrefix
            . $match[3]
            . $handlerSuffix;

        $normalizerClassName = $prefix
            . '\RestControllers'
            . ($match[2] ?? '\\')
            . '\\'
            . $entityShortClassName
            . '\\'
            . $match[3]
            . 'Normalizer';

        $code = $this->compile(
            $entityClassName,
            $handlerClassName,
            $normalizerClassName,
            $template
        );

        $fileName = $this->fileNameFinder->find($handlerClassName);

        $this->fileNameFinder->writeToFile($code, $fileName);
    }

    public function compile(
        string $entityClassName,
        string $handlerClassName,
        string $normalizerClassName,
        string $template
    ): string
    {
        [, $entityShortClassName] = $this->splitClassName($entityClassName);
        $routePath = $this->storeNameConverter->convert($entityShortClassName);
        [$namespace, $shortClassName] = $this->splitClassName($handlerClassName);

        $replacements = [
            '{{namespace}}' => $namespace,
            '{{routePath}}' => $routePath,
            '{{shortClassName}}' => $shortClassName,
            '{{entityClassName}}' => $entityClassName,
            '{{normalizerClassName}}' => $normalizerClassName,
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
