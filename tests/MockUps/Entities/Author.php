<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\MockUps\Entities;

use Medas\EntityManager\Attributes\{Entity, Id};

#[Entity('authors')]
class Author
{
    #[Id]
    public int $id;

    public string $name;
}
