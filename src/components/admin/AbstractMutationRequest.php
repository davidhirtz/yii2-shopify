<?php

declare(strict_types=1);

namespace davidhirtz\yii2\shopify\components\admin;

abstract class AbstractMutationRequest
{
    protected array $errors = [];

    abstract protected function getResponse(): array;

    public function execute(): array
    {
        $data = $this->getResponse();
        $this->setErrorsFromResponse($data);

        return $data;
    }

    protected function setErrorsFromResponse(array $data): void
    {
        foreach ($data['userErrors'] ?? [] as $error) {
            $this->errors[] = $error['message'] ?? 'Unknown error';
        }
    }

    public function getErrors(): array
    {
        return $this->errors;
    }
}