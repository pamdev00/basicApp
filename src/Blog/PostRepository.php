<?php

declare(strict_types=1);

namespace App\Blog;

use Cycle\ORM\ORMInterface;
use Cycle\ORM\Select;
use Cycle\ORM\Transaction;
use Yiisoft\Data\Cycle\Reader\EntityReader;
use Yiisoft\Data\Cycle\Writer\EntityWriter;

final class PostRepository extends Select\Repository
{


    public function __construct(private EntityWriter $entityWriter, Select $select, )
    {
        parent::__construct($select);
    }

    /**
     * @psalm-return EntityReader<array-key, Post>
     */
    public function findAll(array $scope = [], array $orderBy = []): EntityReader
    {
        /** @psalm-var EntityReader<array-key, Post> */
        return new EntityReader(
            $this
                ->select()
                ->where($scope)
                ->orderBy($orderBy)
        );
    }

    public function save(Post $user): void
    {
        $this->entityWriter->write([$user]);
    }
}
