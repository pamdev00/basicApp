<?php

declare(strict_types=1);

namespace App\Cards\Card\Controller;

use App\Cards\Card\Formatter\ChecklistItemFormatter;
use App\Cards\Card\Request\CreateChecklistItemRequest;
use App\Cards\Card\Request\UpdateChecklistItemRequest;
use App\Cards\Card\Service\ChecklistItemService;
use App\Cards\Card\Service\ChecklistService;
use App\Formatter\PaginatorFormatter;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Input\Http\Attribute\Parameter\Query;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

#[OA\Tag(name: 'checklist-item', description: 'Checklist item management')]
final readonly class ChecklistItemController
{
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private ChecklistItemService $checklistItemService,
        private ChecklistService $checklistService,
        private ChecklistItemFormatter $checklistFormatter,
    ) {
    }


    #[OA\Get(
        path: '/api/checklists/{checklistId}/items',
        description: 'Get items list with optional filtering',
        summary: 'Get items list',
        tags: ['checklist-item'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'priority', in: 'query', schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Success',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'cards', type: 'array', items: new OA\Items(ref:'#/components/schemas/ChecklistItem')),
                                    new OA\Property(property: 'paginator', ref: '#/components/schemas/Paginator', type: 'object'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
        ]
    )]
    public function index(
        PaginatorFormatter $paginatorFormatter,
        ServerRequestInterface $request,
        #[RouteArgument('checklistId')]
        string $checklistId
    ): Response {
        $paginator = $this->checklistItemService->getFullItems(checklistId: $checklistId);
        $cards = [];
        foreach ($paginator->read() as $post) {
            $cards[] = $this->checklistFormatter->format($post);
        }

        return $this->responseFactory->createResponse(
            [
                'paginator' => $paginatorFormatter->format($paginator),
                '$request' => $request->getQueryParams(),
                'cards' => $cards,
            ]
        );
    }
    #[OA\Post(
        path: '/api/checklists/{checklistId}/items',
        description: 'Create new checklist item',
        summary: 'Create checklist item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateChecklistItemRequest')
        ),
        tags: ['checklist-item'],
        parameters: [
            new OA\Parameter(
                name: 'checklistId',
                description: 'Checklist ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: '201',
                description: 'Checklist item created',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'is_completed', type: 'boolean'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '400',
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
            new OA\Response(
                response: '404',
                description: 'Checklist not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function create(
        #[RouteArgument('id')]
        string $checklistId,
        CreateChecklistItemRequest $request
    ): Response {
        $checklist = $this->checklistService->getFullChecklist($checklistId);

        if ($checklist === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist not found'],
                Status::NOT_FOUND
            );
        }

        try {
            $item = $this->checklistItemService->create($checklist, $request);

            return $this->responseFactory->createResponse([
                'id' => $item->getId(),
                'description' => $item->getDescription(),
                'is_completed' => $item->isCompleted(),
            ], Status::CREATED);

        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Put(
        path: '/api/checklist-items/{id}',
        description: 'Update checklist item',
        summary: 'Update checklist item',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateChecklistItemRequest')
        ),
        tags: ['checklist-item'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist item ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Checklist item updated',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'description', type: 'string'),
                                    new OA\Property(property: 'is_completed', type: 'boolean'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '400',
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
            new OA\Response(
                response: '404',
                description: 'Checklist item not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function update(
        #[RouteArgument('id')]
        string $id,
        UpdateChecklistItemRequest $request
    ): Response {
        $item = $this->checklistItemService->getById($id);

        if ($item === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist item not found'],
                Status::NOT_FOUND
            );
        }

        try {
            $item = $this->checklistItemService->update($item, $request);

            return $this->responseFactory->createResponse([
                'id' => $item->getId(),
                'description' => $item->getDescription(),
                'is_completed' => $item->isCompleted(),
                'updated_at' => $item->getUpdatedAt()->format('d.m.Y H:i:s'),
            ]);

        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Patch(
        path: '/api/checklist-items/{id}/toggle',
        description: 'Toggle checklist item completion status',
        summary: 'Toggle checklist item',
        tags: ['checklist-item'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist item ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Checklist item toggled',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'is_completed', type: 'boolean'),
                                    new OA\Property(property: 'updated_at', type: 'string', format: 'date-time'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '404',
                description: 'Checklist item not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function toggle(#[RouteArgument('id')] string $id): Response
    {
        $item = $this->checklistItemService->getById($id);

        if ($item === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist item not found'],
                Status::NOT_FOUND
            );
        }

        try {
            $item = $this->checklistItemService->toggle($item);

            return $this->responseFactory->createResponse([
                'id' => $item->getId(),
                'is_completed' => $item->isCompleted(),
                'updated_at' => $item->getUpdatedAt()->format('d.m.Y H:i:s'),
            ]);

        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Delete(
        path: '/api/checklist-items/{id}',
        description: 'Delete checklist item',
        summary: 'Delete checklist item',
        tags: ['checklist-item'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist item ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: '204', description: 'Checklist item deleted'),
            new OA\Response(
                response: '404',
                description: 'Checklist item not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function delete(#[RouteArgument('id')] string $id): Response
    {
        $result = $this->checklistItemService->delete($id);

        if (!$result) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist item not found'],
                Status::NOT_FOUND
            );
        }

        return $this->responseFactory->createResponse(null, Status::NO_CONTENT);
    }
}
