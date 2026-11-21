<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_halaman_awal_mengarah_ke_login(): void
    {
        $this->get('/')->assertRedirect('/login');

        $this->get('/dashboard')->assertRedirect('/login');
    }
}
