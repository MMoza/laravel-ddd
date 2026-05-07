<?php

namespace App\Domains\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Collection;

interface RepositoryInterface
{
    public function find(string $id): ?Model;

    public function all(): Collection;

    public function create(array $data): Model;

    public function update(string $id, array $data): ?Model;

    public function delete(string $id): bool;

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator;
}