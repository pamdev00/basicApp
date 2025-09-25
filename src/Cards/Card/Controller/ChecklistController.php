<?php

declare(strict_types=1);

namespace App\Cards\Card\Controller;

use App\Cards\Card\Formatter\ChecklistFormatter;
use App\Cards\Card\Request\CreateChecklistRequest;
use App\Cards\Card\Request\UpdateChecklistRequest;
use App\Cards\Card\Service\CardService;
use App\Cards\Card\Service\ChecklistService;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface as Response;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Input\Http\Attribute\Parameter\Body;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

#[OA\Tag(name: 'checklist', description: 'Checklist management')]
final readonly class ChecklistController
{
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private ChecklistService $checklistService,
        private CardService $cardService,
        private ChecklistFormatter $checklistFormatter,
    ) {
    }

    #[OA\Get(
        path: '/api/cards/{cardId}/checklists',
        description: 'Get all checklists for a specific card',
        summary: 'Get card checklists',
        tags: ['checklist'],
        parameters: [
            new OA\Parameter(
                name: 'cardId',
                description: 'Card ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
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
                                    new OA\Property(
                                        property: 'checklists',
                                        type: 'array',
                                        items: new OA\Items(ref: '#/components/schemas/Checklist')
                                    ),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '404',
                description: 'Card not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function index(#[RouteArgument('cardId')] string $cardId): Response
    {
        $card = $this->cardService->getFullCard($cardId);

        if ($card === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Card not found'],
                Status::NOT_FOUND
            );
        }

        $checklists = [];
        foreach ($card->getChecklists() as $checklist) {
            $checklists[] = $this->checklistFormatter->format($checklist);
        }

        return $this->responseFactory->createResponse([
            'data' => [
                'checklists' => $checklists,
            ]
        ]);
    }

    #[OA\Get(
        path: '/api/checklists/{id}',
        description: 'Get checklist by ID with all items',
        summary: 'Get checklist details',
        tags: ['checklist'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
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
                                    new OA\Property(
                                        property: 'checklist',
                                        ref: '#/components/schemas/Checklist'
                                    ),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '404',
                description: 'Checklist not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function view(#[RouteArgument('id')] string $id): Response
    {
        $checklist = $this->checklistService->getFullChecklist($id);

        if ($checklist === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist not found'],
                Status::NOT_FOUND
            );
        }

        return $this->responseFactory->createResponse([
            'data' => [
                'checklist' => $this->checklistFormatter->formatDetailed($checklist),
            ]
        ]);
    }

    #[OA\Post(
        path: '/api/cards/{cardId}/checklists',
        description: 'Create new checklist for a card',
        summary: 'Create checklist',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/CreateChecklistRequest')
        ),
        tags: ['checklist'],
        parameters: [
            new OA\Parameter(
                name: 'cardId',
                description: 'Card ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: '201',
                description: 'Checklist created',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'title', type: 'string'),
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
                description: 'Card not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function create(
        #[RouteArgument('cardId')] string $cardId,
        CreateChecklistRequest $request
    ): Response {
        $card = $this->cardService->getFullCard($cardId);

        if ($card === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Card not found1'],
                Status::NOT_FOUND
            );
        }

        try {
            $checklist = $this->checklistService->create($card, $request);

            return $this->responseFactory->createResponse([
                'id' => $checklist->getId(),
                'title' => $checklist->getTitle(),
            ], Status::CREATED);
        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Put(
        path: '/api/checklists/{id}',
        description: 'Update checklist title',
        summary: 'Update checklist',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/UpdateChecklistRequest')
        ),
        tags: ['checklist'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Checklist updated',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'id', type: 'string', format: 'uuid'),
                                    new OA\Property(property: 'title', type: 'string'),
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
                description: 'Checklist not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function update(
        #[RouteArgument('id')] string $id,
        UpdateChecklistRequest $request
    ): Response {
        $checklist = $this->checklistService->getFullChecklist($id);

        if ($checklist === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist not found'],
                Status::NOT_FOUND
            );
        }

        try {
            $checklist = $this->checklistService->update($checklist, $request);

            return $this->responseFactory->createResponse([
                'id' => $checklist->getId(),
                'title' => $checklist->getTitle(),
                'updated_at' => $checklist->getUpdatedAt()->format('d.m.Y H:i:s'),
            ]);
        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Delete(
        path: '/api/checklists/{id}',
        description: 'Delete checklist',
        summary: 'Delete checklist',
        tags: ['checklist'],
        parameters: [
            new OA\Parameter(
                name: 'id',
                description: 'Checklist ID',
                in: 'path',
                required: true,
                schema: new OA\Schema(type: 'string', format: 'uuid')
            ),
        ],
        responses: [
            new OA\Response(response: '204', description: 'Checklist deleted'),
            new OA\Response(
                response: '404',
                description: 'Checklist not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function delete(#[RouteArgument('id')] string $id): Response
    {
        $result = $this->checklistService->delete($id);

        if (!$result) {
            return $this->responseFactory->createResponse(
                ['error' => 'Checklist not found'],
                Status::NOT_FOUND
            );
        }
        return $this->responseFactory->createResponse(null, Status::NO_CONTENT);
    }
}
