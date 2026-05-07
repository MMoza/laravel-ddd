<?php

namespace App\Domains\Users\Repositories;

use App\Domains\Base\RepositoryInterface;
use App\Domains\Users\Entities\User;

interface UserRepositoryInterface extends RepositoryInterface
{
    public function findByEmail(string $email): ?User;

    public function createWithPassword(array $data): User;
}