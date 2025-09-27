<?php

declare(strict_types=1);

namespace App\Blog;

use App\Exception\NotFoundException;
use Yiisoft\Data\Paginator\OffsetPaginator;
use Yiisoft\Data\Paginator\PaginatorInterface;

final readonly class BlogService
{
    private const int POSTS_PER_PAGE = 10;

    public function __construct(private PostRepository $postRepository)
    {
    }

    /**
     * @psalm-return PaginatorInterface<array-key, Post>
     */
    public function getPosts(int $page): PaginatorInterface
    {
        $dataReader = $this->postRepository->findAll();

        /** @psalm-var PaginatorInterface<array-key, Post> */
        return (new OffsetPaginator($dataReader))
            ->withPageSize(self::POSTS_PER_PAGE)
            ->withCurrentPage($page);
    }

    /**
     *
     * @throws NotFoundException
     *
     */
    public function getPost(int $id): Post
    {
        /**
         * @var Post|null $post
         */
        $post = $this->postRepository->findOne(['id' => $id]);
        if ($post === null) {
            throw new NotFoundException();
        }

        return $post;
    }
}
