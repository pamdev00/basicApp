<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Dto\ProblemDetails;
use InvalidArgumentException;
use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final readonly class ProblemDetailsFormatter implements DataResponseFormatterInterface
{
    private const string CONTENT_TYPE = 'application/problem+json';

    public function __construct(
        private ResponseFactoryInterface $responseFactory,
        private StreamFactoryInterface $streamFactory
    ) {
    }

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $data = $dataResponse->getData();
        if (!$data instanceof ProblemDetails) {
            throw new InvalidArgumentException(
                sprintf(
                    'The data must be an instance of %s.',
                    ProblemDetails::class
                )
            );
        }

        $content = json_encode($data, JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        $stream = $this->streamFactory->createStream($content);

        return $this->responseFactory
            ->createResponse($data->getStatus())
            ->withBody($stream)
            ->withHeader('Content-Type', self::CONTENT_TYPE);
    }
}
