<?php

namespace Tests\Feature\Domains\Users;

use Tests\TestCase;

class UserFeatureTest extends TestCase
{
    public function test_User_index_returns_json(): void
    {
        $response = $this->getJson('/api/users');
        $response->assertStatus(200);
    }
}