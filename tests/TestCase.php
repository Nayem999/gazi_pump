<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\Cache;

abstract class TestCase extends BaseTestCase
{
    /**
     * phpunit.xml pins the `array` cache store, which lives for the whole
     * PHP process — RefreshDatabase truncates tables between tests but
     * never touches the cache, so anything cached in one test (e.g.
     * Setting::current(), hit by every HTTP request via the global
     * ApplySettingsToConfig middleware) would otherwise leak into the next
     * test's now-different database state. Module 19 introduced that
     * caching, so this reset is required for it, not just defensive.
     */
    protected function setUp(): void
    {
        parent::setUp();

        Cache::flush();
    }
}
