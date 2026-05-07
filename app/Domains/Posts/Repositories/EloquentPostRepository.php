<?php

namespace App\Domains\Posts\Repositories;

use App\Models\Post as Model;
use App\Domains\Posts\Entities\Post as Entity;

class EloquentPostRepository implements PostRepositoryInterface
{
    public function find(string $id): ?Entity
    {
        $model = Model::find($id);
        return $model ? new Entity($model->toArray()) : null;
    }

    public function all(): \Illuminate\Support\Collection
    {
        return Model::all()->map(fn($m) => new Entity($m->toArray()));
    }

    public function create(array $data): Entity
    {
        $model = Model::create($data);
        return new Entity($model->toArray());
    }

    public function update(string $id, array $data): ?Entity
    {
        $model = Model::find($id);
        if (!$model) return null;

        $model->update($data);
        return new Entity($model->toArray());
    }

    public function delete(string $id): bool
    {
        return Model::find($id)?->delete() ?? false;
    }

    public function paginate(int $perPage = 15): \Illuminate\Pagination\LengthAwarePaginator
    {
        return Model::paginate($perPage);
    }
}