<?php

declare(strict_types=1);

namespace Medas\RestRequestHandlerTest\MockUps\Entities;

use Medas\EntityManager\Attributes\{Entity, Id};

#[Entity('books')]
class Book
{
    #[Id]
    public int $id;

    public string $name;
    public Author $author;
}
