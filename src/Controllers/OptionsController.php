<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\Core\Attributes\{ConfigValue, Service};
use Medas\HttpRequestHandler\{RequestDataManager, ResponseTypes\Response};
use Medas\RestRequestHandler\{
    ConfigOptions\AllowedOrigins,
    Responses\BadRequestResponse,
    Responses\OptionsResponse
};
use Medas\Routing\{Methods\Options, Parameters\Anything, Route};

#[Service, Route(new Anything())]
readonly class OptionsController
{
    public function __construct(
        private RequestDataManager $requestDataManager,

        #[ConfigValue(AllowedOrigins::class)]
        private string             $allowedOrigins,
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
