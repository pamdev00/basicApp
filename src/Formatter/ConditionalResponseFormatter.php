<?php

declare(strict_types=1);

namespace App\Formatter;

use App\Dto\ProblemDetails;
use Psr\Http\Message\ResponseInterface;
use Yiisoft\DataResponse\DataResponse;
use Yiisoft\DataResponse\DataResponseFormatterInterface;

final readonly class ConditionalResponseFormatter implements DataResponseFormatterInterface
{
    public function __construct(
        private ApiResponseFormatter $apiResponseFormatter,
        private ProblemDetailsFormatter $problemDetailsFormatter
    ) {
    }

    public function format(DataResponse $dataResponse): ResponseInterface
    {
        $data = $dataResponse->getData();

        if ($data instanceof ProblemDetails) {
            return $this->problemDetailsFormatter->format($dataResponse);
        }

        return $this->apiResponseFormatter->format($dataResponse);
    }
}
