<?php

declare(strict_types=1);

namespace App\Auth;

use App\Auth\Exception\InvalidTokenException;
use App\Auth\Exception\TokenExpiredException;
use App\Auth\Exception\TokenUsedException;
use App\User\UserRequest;
use App\User\UserService;
use OpenApi\Attributes as OA;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Throwable;
use Yiisoft\DataResponse\DataResponseFactoryInterface as ResponseFactory;
use Yiisoft\Router\HydratorAttribute\RouteArgument;
use Yiisoft\Validator\ValidatorInterface;
use App\Auth\ResendVerificationService;
use App\Dto\ProblemDetails;
use App\Auth\ResendRequest;

#[OA\Tag(name: 'auth', description: 'Authentication')]
#[OA\SecurityScheme(securityScheme: 'ApiKey', type: 'apiKey', name: 'X-Api-Key', in: 'header')]
final readonly class AuthController
{
    public function __construct(
        private ResponseFactory $responseFactory,
        private UserService $userService,
        private RegisterUserService $registerUserService,
        private VerifyEmailService $verifyEmailService,
        private ResendVerificationService $resendVerificationService,
        private ValidatorInterface $validator
    ) {
    }

    #[OA\Post(
        path: '/register/',
        description: 'Register a new user',
        summary: 'User registration',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/RegisterRequest')
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: '202',
                description: 'Accepted',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'verification_status', type: 'string', example: 'pending'),
                        new OA\Property(property: 'message', type: 'string', example: 'Verification email sent.'),
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '422',
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/BadResponse')
            ),
            new OA\Response(
                response: '409',
                description: 'Conflict - user with this email already exists',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            )
        ]
    )]
    public function register(RegisterRequest $request): ResponseInterface
    {
        $result = $this->validator->validate($request);
        if (!$result->isValid()) {
           return $this->responseFactory->createResponse(
                $result,422
            );
        }

        try {
            $this->registerUserService->register(
                $request->getLogin(),
                $request->getPassword(),
                $request->getEmail(),
            );
        } catch (Throwable $e) {
            $problemDetails = new ProblemDetails(
                type: '/docs/errors/user-already-exists',
                title: 'User Already Exists',
                status: 409,
                detail: $e->getMessage()
            );
            return $this->responseFactory->createResponse($problemDetails);
        }


        return $this->responseFactory->createResponse(
            [
                'verification_status' => 'pending',
                'message' => 'Verification email sent.',
            ],
            202
        );
    }

    #[OA\Post(
        path: '/auth/',
        description: '',
        summary: 'Authenticate by params',
        requestBody: new OA\RequestBody(required: true, content: new OA\JsonContent(
            allOf: [
                new OA\Schema(ref: '#/components/schemas/AuthRequest'),
            ]
        )),
        tags: ['auth'],
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
                                    new OA\Property(property: 'token', type: 'string', example: 'uap4X5Bd7078lxIFvxAflcGAa5D95iSSZkNjg3XFrE2EBRBlbj'),
                                ],
                                type: 'object'
                            ),
                        ]),
                    ]
                )
            ),
            new OA\Response(
                response: '400',
                description: 'Bad request',
                content: new OA\JsonContent(ref:  '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function login(AuthRequest $request): ResponseInterface
    {
        return $this->responseFactory->createResponse(
            [
                'token' => $this->userService
                    ->login(
                        $request->getLogin(),
                        $request->getPassword()
                    )
                    ->getToken(),
            ]
        );
    }

    #[OA\Post(
        path: '/logout/',
        description: '',
        summary: 'Logout',
        security: [new OA\SecurityScheme(ref: '#/components/securitySchemes/ApiKey')],
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Success',
                content: new OA\JsonContent(ref:  '#/components/schemas/Response')
            ),
            new OA\Response(
                response: '400',
                description: 'Bad request',
                content: new OA\JsonContent(ref:  '#/components/schemas/BadResponse')
            ),
        ]
    )]
    public function logout(UserRequest $request): ResponseInterface
    {
        $this->userService->logout($request->getUser());

        return $this->responseFactory->createResponse();
    }

    #[OA\Get(
        path: '/verify-email/{token}',
        description: 'Verify user email with a token from the verification email',
        summary: 'Verify user email',
        parameters: [
            new OA\Parameter(
                name: 'token',
                in: 'path',
                required: true,
                description: 'The verification token',
                schema: new OA\Schema(type: 'string')
            )
        ],
        tags: ['auth'],
        responses: [
            new OA\Response(
                response: '200',
                description: 'Email successfully verified',
                content: new OA\JsonContent(
                    properties: [
                        new OA\Property(property: 'status', type: 'string', example: 'ok')
                    ],
                    type: 'object'
                )
            ),
            new OA\Response(
                response: '422',
                description: 'Invalid, used, or expired token',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
            new OA\Response(
                response: '500',
                description: 'Internal server error',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            )
        ]
    )]
    public function verifyEmail(#[RouteArgument('token')] string $token): ResponseInterface
    {
        try {
            $this->verifyEmailService->verify($token);
        } catch (InvalidTokenException|TokenUsedException|TokenExpiredException $e) {
            $problemDetails = new ProblemDetails(
                type: '/docs/errors/invalid-verification-token',
                title: 'Invalid Verification Token',
                status: 422,
                detail: $e->getMessage()
            );
            return $this->responseFactory->createResponse($problemDetails);
        } catch (Throwable $e) {
            $problemDetails = new ProblemDetails(
                type: '/docs/errors/unexpected-error',
                title: 'An unexpected error occurred',
                status: 500,
                detail: 'An unexpected error occurred.'
            );
            return $this->responseFactory->createResponse($problemDetails);
        }

        return $this->responseFactory->createResponse(['status' => 'ok']);
    }

    #[OA\Post(
        path: '/auth/resend-verification',
        description: 'Resend verification email',
        summary: 'Resend verification email',
        requestBody: new OA\RequestBody(
            required: true,
            content: new OA\JsonContent(ref: '#/components/schemas/ResendRequest')
        ),
        tags: ['auth'],
        responses: [
            new OA\Response(response: '202', description: 'Accepted'),
            new OA\Response(
                response: '404',
                description: 'User not found',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
            new OA\Response(
                response: '422',
                description: 'Validation error',
                content: new OA\JsonContent(ref: '#/components/schemas/ProblemDetails')
            ),
        ]
    )]
    public function resendVerification(ResendRequest $request): ResponseInterface
    {
        $this->resendVerificationService->resend($request->getEmail());
        return $this->responseFactory->createResponse(null, 202);
    }
}