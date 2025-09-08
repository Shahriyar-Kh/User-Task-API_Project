<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Fix JWT configuration type issues
        config([
            'jwt.ttl' => 60,
            'jwt.refresh_ttl' => 20160,
            'jwt.leeway' => 0,
            'jwt.blacklist_grace_period' => 0,
        ]);
    }
}