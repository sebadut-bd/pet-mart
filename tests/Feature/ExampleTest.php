<?php

namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_admin_login()
    {
        $this->get('/')->assertRedirect('/login');
        $this->get('/login')->assertOk();
    }
}
