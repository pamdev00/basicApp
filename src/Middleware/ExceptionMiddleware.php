<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Dto\ProblemDetails;
use App\Exception\ApplicationException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Psr\Http\Server\MiddlewareInterface;
use Psr\Http\Server\RequestHandlerInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Input\Http\InputValidationException;
use Yiisoft\Validator\Error;

final class ExceptionMiddleware implements MiddlewareInterface
{
    private DataResponseFactoryInterface $dataResponseFactory;

    public function __construct(DataResponseFactoryInterface $dataResponseFactory)
    {
        $this->dataResponseFactory = $dataResponseFactory;
    }

    public function process(ServerRequestInterface $request, RequestHandlerInterface $handler): ResponseInterface
    {
        try {
            return $handler->handle($request);
        } catch (ApplicationException $e) {
            $problemDetails = new ProblemDetails(
                type: '/docs/errors/application-error',
                title: 'Application Error',
                status: $e->getCode() > 0 ? $e->getCode() : Status::INTERNAL_SERVER_ERROR,
                detail: $e->getMessage()
            );
            return $this->dataResponseFactory->createResponse($problemDetails);
        } catch (InputValidationException $e) {
            $errors = [];
            foreach ($e->getResult()->getErrors() as $error) {
                if ($error instanceof Error) {
                    $field = $error->getValuePath()[0] ?? 'general';
                    $errors[$field][] = $error->getMessage();
                }
            }

            $problemDetails = (new ProblemDetails(
                type: '/docs/errors/validation-error',
                title: 'Validation Failed',
                status: Status::UNPROCESSABLE_ENTITY,
                detail: 'One or more validation errors occurred.'
            ))->withData(['errors' => $errors]);

            return $this->dataResponseFactory->createResponse($problemDetails);
        } catch (\Throwable $e) {
            $problemDetails = new ProblemDetails(
                type: '/docs/errors/unexpected-error',
                title: 'An unexpected error occurred',
                status: Status::INTERNAL_SERVER_ERROR,
                detail: 'Please try again later.'
            );
            return $this->dataResponseFactory->createResponse($problemDetails);
        }
    }
}
