<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Testler derlenmiş ön yüz dosyalarına bağlı olmasın.
        $this->withoutVite();
    }
}
