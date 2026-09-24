<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Foundation\Testing\Concerns\InteractsWithAuthentication;
use Illuminate\Foundation\Testing\Concerns\MakesHttpRequests;

/**
 * @method $this actingAs(\Illuminate\Contracts\Auth\Authenticatable $user, ?string $guard = null)
 * @method \Illuminate\Testing\TestResponse get(string $uri, array $headers = [])
 * @method \Illuminate\Testing\TestResponse post(string $uri, array $data = [], array $headers = [])
 */
abstract class TestCase extends BaseTestCase
{
    use InteractsWithAuthentication, MakesHttpRequests;
}
