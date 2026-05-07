<?php

namespace App\Domains\Base;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Concerns\HasUuids;

abstract class Entity extends Model
{
    use HasUuids;

    public $incrementing = false;
    protected $keyType = 'string';

    abstract public function getId(): string;

    public function isSameEntity(self $entity): bool
    {
        return $this->getId() === $entity->getId();
    }
}