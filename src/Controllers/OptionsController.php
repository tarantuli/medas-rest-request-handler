<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\Core\Attributes\ConfigValue;
use Medas\Core\Attributes\Service;
use Medas\HttpRequestHandler\Request\RequestDataManager;
use Medas\HttpRequestHandler\ResponseTypes\Response;
use Medas\RestRequestHandler\ConfigOptions\AllowedOrigins;
use Medas\RestRequestHandler\Responses\BadRequestResponse;
use Medas\RestRequestHandler\Responses\OptionsResponse;
use Medas\Routing\{Methods\Options, Parameters\Anything, Route};

#[Service, Route(new Anything())]
class OptionsController
{
    public function __construct(
        private readonly RequestDataManager $requestDataManager,
        #[ConfigValue(AllowedOrigins::class)]
        private readonly string             $allowedOrigins,
    )
    {
    }

    #[Options]
    public function getOptions(): Response
    {
        $serverData = $this->requestDataManager->get()->serverData;

        if (!preg_match($this->allowedOrigins, $serverData['HTTP_ORIGIN'])) {
            return new BadRequestResponse();
        }

        return new OptionsResponse(
            $serverData['HTTP_ORIGIN'],
            $serverData['HTTP_ACCESS_CONTROL_REQUEST_METHOD'],
            $serverData['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'] ?? null,
        );
    }
}
