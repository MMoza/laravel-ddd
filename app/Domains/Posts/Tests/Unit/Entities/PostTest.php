<?php

namespace Tests\Unit\Domains\Posts\Entities;

use Tests\TestCase;
use App\Domains\Posts\Entities\Post;

class PostTest extends TestCase
{
    public function test_Post_can_be_created(): void
    {
        $entity = new Post([
            'id' => 'test-uuid',
        ]);

        $this->assertEquals('test-uuid', $entity->getId());
    }
}