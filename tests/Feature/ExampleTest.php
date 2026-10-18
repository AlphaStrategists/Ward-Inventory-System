<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_the_root_url_shows_the_login_page(): void
    {
        $this->get('/')->assertOk()->assertSee('Sign In');
    }
}
