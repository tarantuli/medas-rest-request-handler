<?php

declare(strict_types=1);

namespace Medas\RestRequestHandler\Controllers;

use Medas\EntityManager\EntityManager;
use Medas\EntityManager\Hydration\ValueSetter;
use Medas\EntityManager\MetaDataManager;
use Medas\EntityManager\Repository;
use Medas\HttpRequestHandler\Request\RequestDataManager;

abstract class BaseController
{
    private array $requestData;

    public function __construct(
        protected EntityManager      $entityManager,
        protected MetaDataManager    $metaDataManager,
        protected Repository         $repository,
        protected RequestDataManager $requestDataManager,
        protected ValueSetter        $valueSetter,
    )
    {
    }

    abstract public function entityClass(): string;

    protected function requestData(): array
    {
        if (!isset($this->requestData)) {
            $data = $this->requestDataManager->get();
            $this->requestData = array_merge(
                $data->postData->data(),
                $data->bodyData->data(),
                $data->fileData->data(),
            );
        }

        return $this->requestData;
    }
}
