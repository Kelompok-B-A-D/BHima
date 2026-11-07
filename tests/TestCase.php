<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (! file_exists(public_path('build/manifest.json'))) {
            $this->withoutVite();
        }
    }
}
