<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\HandlerGenerator;

use Medas\Core\Attributes\Service;

#[Service]
readonly class Templates
{
    public function getInstanceByUuid(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\RestRequestHandler\Responses\{EntityResponse, EntityResponseBuilder};
use Medas\Routing\{Methods\Get, Parameters\Uuid, Route};

#[Route('{{routePath')]
readonly class {{shortClassName}}
{
    public function __construct(
        protected EntityResponseBuilder $entityResponseBuilder,
    )
    {
    }

    #[Get(new Uuid('id'))]
    public function handle(\Medas\Core\Interfaces\Uuid $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get({{entityClassName}}::class, $id), $this);
    }
}

PHP;
    }

    public function getInstanceByInteger(): string
    {
        return <<<'PHP'
<?php

declare(strict_types=1);

namespace {{namespace}};

use Medas\RestRequestHandler\Responses\{EntityResponse, EntityResponseBuilder};
use Medas\Routing\{Methods\Get,Parameters\Integer, Route};

#[Route('{{routePath')]
readonly class {{shortClassName}}
{
    public function __construct(
        protected EntityResponseBuilder $entityResponseBuilder,
    )
    {
    }

    #[Get(new Integer('id'))]
    public function handle(int $id): EntityResponse
    {
        return $this->entityResponseBuilder->build(em()->get({{entityClassName}}::class, $id), $this);
    }
}

PHP;
    }
}
