<?php

declare(strict_types=1);

namespace App\Factory;

use App\Dto\ApiResponseData;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\Http\Status;
use Yiisoft\Validator\Error;

final class ApiResponseDataFactory
{
    public function createFromResponse(DataResponse $response): ApiResponseData
    {
        $statusCode = $response->getStatusCode();
        if (!in_array($statusCode, [Status::OK, Status::CREATED, Status::NO_CONTENT, Status::ACCEPTED], true)) {
            $errorData = $this->createErrorResponse()->setErrorCode($statusCode);

            // Special handling for validation errors
            if ($statusCode === Status::UNPROCESSABLE_ENTITY) {
                $validationErrors = $response->getData();
                $formattedErrors = [];
                if (is_array($validationErrors)) {
                    foreach ($validationErrors as $error) {
                        if ($error instanceof Error) {
                            $field = $error->getValuePath()[0] ?? 'general';
                            if (isset($formattedErrors[$field])) {
                                $formattedErrors[$field] .= ' ' . $error->getMessage();
                            } else {
                                $formattedErrors[$field] = $error->getMessage();
                            }
                        }
                    }
                }

                return $errorData
                    ->setErrorMessage('Validation failed')
                    ->setData($formattedErrors);
            }

            return $errorData->setErrorMessage($this->getErrorMessage($response));
        }

        return $this
            ->createSuccessResponse()
            ->setData($response->getData());
    }

    public function createSuccessResponse(): ApiResponseData
    {
        return $this
            ->createResponse()
            ->setStatus('success');
    }

    public function createErrorResponse(): ApiResponseData
    {
        return $this
            ->createResponse()
            ->setStatus('failed');
    }

    public function createResponse(): ApiResponseData
    {
        return new ApiResponseData();
    }

    private function getErrorMessage(DataResponse $response): string
    {
        $data = $response->getData();
        if (is_string($data) && !empty($data)) {
            return $data;
        }

        if (is_array($data) && isset($data['error']) && is_string($data['error'])) {
            return $data['error'];
        }

        return 'Unknown error';
    }
}
