<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\{ConfigValue, Service};
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
        private NameConverter       $storeNameConverter,
        private FileNameFinder      $fileNameFinder,

        #[ConfigValue(HandlerClassNamePattern::class)]
        private string              $handlerClassNamePattern,

        #[ConfigValue(NormalizerClassNamePattern::class)]
        private string              $normalizerClassNamePattern,
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
        $pattern = '#^(' . str_replace('\\', '\\\\', $prefix) . ')(\\\\.+)?\\\\(\w+)$#';

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

        $handlerClassName = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->handlerClassNamePattern
        );

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
