<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, ConsoleCommandGroup};
use Medas\Core\Attributes\Service;
use Medas\RestRequestHandler\HandlerGenerator\{ClassGenerator, Templates};

#[Service]
readonly class CreateAllControllers extends BaseConsoleCommand
{
    public function __construct(
        private ClassGenerator          $classGenerator,
        private RestRequestHandlerGroup $group,
        private Templates               $templates,
    )
    {
    }

    public function group(): ConsoleCommandGroup
    {
        return $this->group;
    }

    public function name(): string
    {
        return 'create-all-controllers';
    }

    public function aliases(): array
    {
        return ['c.rest-controllers'];
    }

    public function description(): string
    {
        return 'Creates all CRUD controllers for a given entity class';
    }

    public function process(array $arguments): void
    {
        $entityClassName = $arguments[1];
        $useUuid = ($arguments[2] ?? null) !== '--id';

        $this->requestHandlers($entityClassName, $useUuid);
        $this->helpers($entityClassName);
        $this->authorization($entityClassName);
    }

    private function requestHandlers(string $entityClassName, bool $useUuid): void
    {
        $this->classGenerator->generate(
            $entityClassName,
            $useUuid ? $this->templates->getInstanceByUuid() : $this->templates->getInstanceByInteger(),
            'Get',
            '',
        );

        $this->classGenerator->generate(
            $entityClassName,
            $useUuid ? $this->templates->putInstanceByUuid() : $this->templates->putInstanceByInteger(),
            'Put',
            '',
        );

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->createInstance(),
            'Create',
            '',
        );

        $this->classGenerator->generate(
            $entityClassName,
            $useUuid
                ? $this->templates->deleteInstanceByUuid()
                : $this->templates->deleteInstanceByInteger(),
            'Delete',
            '',
        );

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->getCollection(),
            'Get',
            'Collection',
        );

        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->getCollectionCount(),
            'Get',
            'Count',
        );
    }

    private function helpers(string $entityClassName): void
    {
        $this->classGenerator->generate(
            $entityClassName,
            $this->templates->entityNormalizer(),
            'Helpers\\',
            'Normalizer',
        );
    }

    private function authorization(string $entityClassName): void
    {
        $prefixes = ['Create', 'Read', 'Update', 'Delete'];

        foreach ($prefixes as $prefix) {
            $voteClassName = $this->classGenerator->generate(
                $entityClassName,
                $this->templates->crudVote(),
                'Authorization\\' . $prefix,
                'Vote',
            );

            $this->classGenerator->generate(
                $entityClassName,
                $this->templates->crudVoteHandler(),
                'Authorization\\' . $prefix,
                'VoteHandler',
                ['{{voteClassName}}' => $voteClassName]
            );
        }
    }
}
