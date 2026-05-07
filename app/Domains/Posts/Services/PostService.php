<?php

namespace App\Domains\Posts\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\Posts\Repositories\PostRepositoryInterface;
use Illuminate\Support\Collection;

class PostService extends BaseService
{
    public function __construct(
        protected PostRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?mixed
    {
        return $this->repository->find($id);
    }

    public function create(array $data): mixed
    {
        return $this->repository->create($data);
    }

    public function update(string $id, array $data): ?mixed
    {
        return $this->repository->update($id, $data);
    }

    public function delete(string $id): bool
    {
        return $this->repository->delete($id);
    }
}