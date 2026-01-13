<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\EntityManager\ConfigOptions\GeneratorRootNamespace;
use Medas\EntityManager\Entities\Generator\{
    ClassNameNormalizer,
    Exceptions\ClassHasNoNamespace,
    FileNameFinder,
    NameConverters\NameConverter
};
use Medas\RestRequestHandler\ConfigOptions\ClassGenerators\{
    HandlerClassNamePattern,
    NormalizerClassNamePattern
};
use Medas\RestRequestHandler\Exceptions\StringIsNotAValidEntityClassName;

#[Service]
readonly class ClassGenerator
{
    public function __construct(
        private ClassNameNormalizer $classNameNormalizer,
        private FileNameFinder      $fileNameFinder,
        private NameConverter       $storeNameConverter,

        #[ConfigValue(HandlerClassNamePattern::class)]
        private string              $handlerClassNamePattern,

        #[ConfigValue(NormalizerClassNamePattern::class)]
        private string              $normalizerClassNamePattern,

        #[ConfigValue(GeneratorRootNamespace::class)]
        private string|null         $rootNamespace,
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
        $prefix = $this->fileNameFinder->findPrefix($entityClassName);

        if ($prefix === null) {
            throw new StringIsNotAValidEntityClassName($entityClassName);
        }

        $prefix = rtrim($prefix, '\\');

        $pattern = '#^('
            . str_replace('\\', '\\\\', $this->rootNamespace ?? $prefix)
            . ')(\\\\.+)?\\\\(\w+)$#';

        if (!preg_match($pattern, $entityClassName, $match)) {
            throw new StringIsNotAValidEntityClassName($entityClassName);
        }

        [, $entityShortClassName] = $this->splitClassName($entityClassName);

        $replacements = [
            '{{psr4Prefix}}' => $prefix,
            '{{subPath}}' => $match[2] ?? '',
            '{{entityName}}' => $entityShortClassName,
            '{{handlerPrefix}}' => $handlerPrefix,
            '{{handlerSuffix}}' => $handlerSuffix,
        ];

        $replacements['{{handlerPrefix}}'] = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $replacements['{{handlerPrefix}}']
        );

        $handlerClassName = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->handlerClassNamePattern
        );

        $fileName = $this->fileNameFinder->find($handlerClassName);

        if (file_exists($fileName)) {
            return;
        }

        $normalizerClassName = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->normalizerClassNamePattern
        );

        $code = $this->compile(
            $entityClassName,
            $handlerClassName,
            $normalizerClassName,
            $template
        );

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
        $instanceVariable = '$' . lcfirst($shortClassName);

        $replacements = [
            '{{namespace}}' => $namespace,
            '{{routePath}}' => $routePath,
            '{{shortClassName}}' => $shortClassName,
            '{{instanceVariable}}' => $instanceVariable,
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
