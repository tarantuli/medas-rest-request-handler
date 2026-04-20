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
        string $handlerSuffix,
        array  $replacements = []
    ): string
    {
        $entityClassName = $this->classNameNormalizer->normalize($entityClassName);

        $handlerClassName = $this->generateClassName(
            $entityClassName,
            $handlerPrefix,
            $handlerSuffix,
            $replacements
        );

        $fileName = $this->fileNameFinder->find($handlerClassName);

        if (file_exists($fileName)) {
            return $handlerClassName;
        }

        $replacements = $this->gatherReplacements(
            $entityClassName,
            $replacements,
            $handlerPrefix,
            $handlerSuffix
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
            $template,
            $replacements
        );

        $this->fileNameFinder->writeToFile($code, $fileName);

        return $handlerClassName;
    }

    public function generateClassName(
        string $entityClassName,
        string $handlerPrefix,
        string $handlerSuffix,
        array  $replacements = []
    ): string|array
    {
        $replacements = $this->gatherReplacements(
            $entityClassName,
            $replacements,
            $handlerPrefix,
            $handlerSuffix
        );

        return str_replace(
            array_keys($replacements),
            array_values($replacements),
            $this->handlerClassNamePattern
        );
    }

    private function gatherReplacements(
        string $entityClassName,
        array  $replacements,
        string $handlerPrefix,
        string $handlerSuffix
    ): array
    {
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

        $replacements = array_merge($replacements, [
            '{{psr4Prefix}}' => $prefix,
            '{{subPath}}' => $match[2] ?? '',
            '{{entityName}}' => $entityShortClassName,
            '{{handlerPrefix}}' => $handlerPrefix,
            '{{handlerSuffix}}' => $handlerSuffix,
        ]);

        $replacements['{{handlerPrefix}}'] = str_replace(
            array_keys($replacements),
            array_values($replacements),
            $replacements['{{handlerPrefix}}']
        );

        return $replacements;
    }

    public function compile(
        string $entityClassName,
        string $handlerClassName,
        string $normalizerClassName,
        string $template,
        array  $replacements,
    ): string
    {
        [, $entityShortClassName] = $this->splitClassName($entityClassName);
        $routePath = $this->storeNameConverter->convert($entityShortClassName);
        [$namespace, $shortClassName] = $this->splitClassName($handlerClassName);
        $instanceVariable = '$' . lcfirst($entityShortClassName);

        $replacements = array_merge($replacements, [
            '{{namespace}}' => $namespace,
            '{{routePath}}' => $routePath,
            '{{shortClassName}}' => $shortClassName,
            '{{instanceVariable}}' => $instanceVariable,
            '{{entityClassName}}' => $entityClassName,
            '{{normalizerClassName}}' => $normalizerClassName,
        ]);

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
