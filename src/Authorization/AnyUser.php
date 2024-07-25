<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Authorization;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD)]
class AnyUser
{
}
