<?php

namespace App\Domains\Posts\Entities;

use App\Domains\Base\Entity as BaseEntity;

class Post extends BaseEntity
{
    protected $table = 'posts';

    protected $fillable = [];

    public function getId(): string
    {
        return $this->id;
    }
}