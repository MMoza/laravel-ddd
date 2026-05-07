<?php

namespace Tests\Feature\Domains\Posts;

use Tests\TestCase;

class PostFeatureTest extends TestCase
{
    public function test_Post_index_returns_json(): void
    {
        $response = $this->getJson('/api/posts');
        $response->assertStatus(200);
    }
}