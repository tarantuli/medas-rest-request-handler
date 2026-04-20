<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\ConsoleCommands;

use Medas\Console\Commands\{BaseConsoleCommand, CommandInput, ConsoleCommandGroup, Option, Range};
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

    public function allowedArgumentCount(): Range
    {
        return new Range(1);
    }

    public function options(): array
    {
        return [new Option('id')];
    }

    public function process(CommandInput $input): void
    {
        $entityClassName = $input->getArgument(1);

        $this->requestHandlers($entityClassName, $input->hasOption('id'));
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
        $instanceTemplate = $this->templates->crudVote();
        $dataArrayTemplate = $this->templates->crudDataVote();

        $prefixes = [
            'Create' => $dataArrayTemplate,
            'Read' => $instanceTemplate,
            'Update' => $instanceTemplate,
            'Delete' => $instanceTemplate,
        ];

        foreach ($prefixes as $prefix => $template) {
            $voteClassName = $this->classGenerator->generate(
                $entityClassName,
                $template,
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
