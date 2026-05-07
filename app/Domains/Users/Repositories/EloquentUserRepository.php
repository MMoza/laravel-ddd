<?php

namespace App\Domains\Users\Repositories;

use App\Models\User as Model;
use App\Domains\Users\Entities\User as Entity;
use Illuminate\Support\Facades\Hash;

class EloquentUserRepository implements UserRepositoryInterface
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

    public function findByEmail(string $email): ?Entity
    {
        $model = Model::where('email', $email)->first();
        return $model ? new Entity($model->toArray()) : null;
    }

    public function createWithPassword(array $data): Entity
    {
        if (isset($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        }

        $model = Model::create($data);
        return new Entity($model->toArray());
    }
}