<?php

namespace Tests\Unit\Domains\Users\Entities;

use Tests\TestCase;
use App\Domains\Users\Entities\User;

class UserTest extends TestCase
{
    public function test_User_can_be_created(): void
    {
        $entity = new User([
            'id' => 'test-uuid',
        ]);

        $this->assertEquals('test-uuid', $entity->getId());
    }
}