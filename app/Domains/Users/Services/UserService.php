<?php

namespace App\Domains\Users\Services;

use App\Domains\Base\Service as BaseService;
use App\Domains\Users\Repositories\UserRepositoryInterface;
use Illuminate\Support\Collection;

class UserService extends BaseService
{
    public function __construct(
        protected UserRepositoryInterface $repository
    ) {}

    public function getAll(): Collection
    {
        return $this->repository->all();
    }

    public function find(string $id): ?mixed
    {
        return $this->repository->find($id);
    }

    public function findByEmail(string $email): ?mixed
    {
        return $this->repository->findByEmail($email);
    }

    public function create(array $data): mixed
    {
        return $this->repository->createWithPassword($data);
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