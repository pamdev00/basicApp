<?php

declare(strict_types=1);

namespace App\Cards\Card\Controller;

use App\Cards\Card\CardBuilder;
use App\Cards\Card\Entity\Card;
use App\Cards\Card\Entity\Tag;
use App\Cards\Card\Formatter\CardFormatter;
use App\Cards\Card\Request\EditCardRequest;
use App\Cards\Card\Service\CardService;
use App\Formatter\PaginatorFormatter;
use Exception;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface;
use Yiisoft\DataResponse\DataResponseFactoryInterface;
use Yiisoft\Http\Status;
use Yiisoft\Input\Http\Attribute\Parameter\Query;
use Yiisoft\Router\HydratorAttribute\RouteArgument;

#[OA\Tag(name: 'card', description: 'Card management')]
final readonly class CardController
{
    public function __construct(
        private DataResponseFactoryInterface $responseFactory,
        private CardService $cardService,
        private CardFormatter $cardFormatter,
        private CardBuilder $cardBuilder,
    ) {}

    #[OA\Get(
        path: '/api/cards',
        description: 'Get cards list with optional filtering',
        summary: 'Get cards list',
        tags: ['card'],
        parameters: [
            new OA\Parameter(name: 'status', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'priority', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'user_id', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'tag_id', in: 'query', schema: new OA\Schema(type: 'string')),
            new OA\Parameter(name: 'overdue', in: 'query', schema: new OA\Schema(type: 'boolean')),
            new OA\Parameter(name: 'due_soon_days', in: 'query', schema: new OA\Schema(type: 'integer')),
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
                                    new OA\Property(property: 'cards', type: 'array', items: new OA\Items(ref:'#/components/schemas/Card')),
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
    public function index(PaginatorFormatter $paginatorFormatter,
        ServerRequestInterface $request,
        #[Query('page')] int $page = 1
    ): Response
    {
        $paginator = $this->cardService->getAllPreloaded( page: $page, params: $request->getQueryParams());
        $cards = [];
        foreach ($paginator->read() as $post) {
            $cards[] = $this->cardFormatter->format($post);
        }

        return $this->responseFactory->createResponse(
            [
                'paginator' => $paginatorFormatter->format($paginator),
                '$request' => $request->getQueryParams(),
                'cards' => $cards,
            ]
        );
    }


    #[OA\Get(
        path: '/api/cards/{id}',
        description: 'Get full card by ID',
        summary: 'Returns a card with a given ID',
        tags: ['card'],
        parameters: [
            new OA\Parameter(parameter: 'id', name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string', example: '2')),
        ],
        responses: [
            new OA\Response(
                response:'200',
                description:'Success',
                content: new OA\JsonContent(
                    allOf: [
                        new OA\Schema(ref: '#/components/schemas/Response'),
                        new OA\Schema(properties: [
                            new OA\Property(
                                property: 'data',
                                properties: [
                                    new OA\Property(property: 'card', ref: '#/components/schemas/Card', type: 'object'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                ),
            ),
            new OA\Response(
                response: '404',
                description: 'Not found',
                content: new OA\JsonContent(allOf: [
                    new OA\Schema(ref:  '#/components/schemas/BadResponse'),
                    new OA\Schema(properties: [
                        new OA\Property(property:'error_message', example:'Entity not found'),
                        new OA\Property(property: 'error_code', example: 404, nullable: true),
                    ]),
                ])
            ),
        ]
    )]
    public function view(#[RouteArgument('id')] string $id): Response
    {
        $card = $this->cardService->getFullCard($id);

        if ($card === null) {
            return $this->responseFactory->createResponse(
                ['error' => 'Card not found'],
                Status::NOT_FOUND
            );
        }

        return $this->responseFactory->createResponse([

                'id' => $card->getId(),
                'title' => $card->getTitle(),
                'description' => $card->getDescription(),
                'status' => $card->getStatus(),
                'priority' => $card->getPriority(),
                'created_at' => $card->getCreatedAt()->format('d.m.Y H:i:s'),
                'due_date' => $card->getDueDate()?->format('d.m.Y H:i:s'),
//                'tags' => $card->getTags(),
//                'checklists' => $card->getChecklists(),

        ]);
    }


    #[OA\Post(
        path: '/api/cards',
        description: 'Create new card',
        summary: 'Create card',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/EditCardRequest'),
            ]
        )),
        tags: ['card'],
        responses: [
            new OA\Response(
                response: '201',
                description: 'Card created',
                content: new OA\JsonContent(ref: '#/components/schemas/Response')
            ),
            new OA\Response(
                response: '400',
                description: 'Bad request',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
            new OA\Response(
                response: '404',
                description: 'Not found',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function create(EditCardRequest $cardRequest): Response
    {

        try {
            $card = $this->cardService->create($cardRequest);

            return $this->responseFactory->createResponse([
                'id' => $card->getId(),
                'title' => $card->getTitle(),
                'description' => $card->getDescription(),
                'status' => $card->getStatus(),
                'priority' => $card->getPriority(),
                'updated_at' => $card->getUpdatedAt()->format('d.m.Y H:i:s'),
                'tags' => array_map(static fn(Tag $tag) => $tag->getName(), $card->getTags()),
            ], Status::CREATED);

        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        } catch (\Throwable $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Put(
        path: '/api/cards/{id}',
        description: 'Update card',
        summary: 'Update card',
        requestBody: new OA\RequestBody(
            content: new OA\JsonContent(
                properties: [
                    new OA\Property(property: 'title', type: 'string'),
                    new OA\Property(property: 'description', type: 'string'),
                    new OA\Property(property: 'status', type: 'string'),
                    new OA\Property(property: 'priority', type: 'string'),
                    new OA\Property(property: 'due_date', type: 'string', format: 'date-time', nullable: true),
                ]
            )
        ),
        tags: ['card'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: '200', description: 'Card updated'),
            new OA\Response(response: '404', description: 'Card not found'),
            new OA\Response(response: '400', description: 'Validation error'),
        ]
    )]
    public function update(#[RouteArgument('id')] string $id, EditCardRequest $cardRequest): ResponseInterface
    {

        /** @var Card $card */
        $card = $this->cardService->getFullCard($id);
        try {
            $card = $this->cardService->update($card,$cardRequest);

            return $this->responseFactory->createResponse([
                'id' => $card->getId(),
                'title' => $card->getTitle(),
                'description' => $card->getDescription(),
                'status' => $card->getStatus(),
                'priority' => $card->getPriority(),
                'updated_at' => $card->getUpdatedAt()->format('d.m.Y H:i:s'),
            ]);
        } catch (Exception $e) {
            return $this->responseFactory->createResponse(
                ['error' => $e->getMessage()],
                Status::BAD_REQUEST
            );
        }
    }

    #[OA\Delete(
        path: '/api/cards/{id}',
        description: 'Delete card',
        summary: 'Delete card',
        tags: ['card'],
        parameters: [
            new OA\Parameter(name: 'id', in: 'path', required: true, schema: new OA\Schema(type: 'string')),
        ],
        responses: [
            new OA\Response(response: '204', description: 'Card deleted'),
            new OA\Response(response: '404', description: 'Card not found'),
        ]
    )]
    public function delete(#[RouteArgument('id')] string $id): Response
    {
        $result = $this->cardService->delete($id);

        if (!$result) {
            return $this->responseFactory->createResponse(
                ['error' => 'Card not found'],
                Status::NOT_FOUND
            );
        }
        return $this->responseFactory->createResponse(null, Status::NO_CONTENT);
    }

}
