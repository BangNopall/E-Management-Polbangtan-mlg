<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    /**
     * Read by Illuminate\Foundation\Testing\Traits\CanConfigureMigrationCommands
     * ::shouldSeed() — only affects test classes that `use RefreshDatabase`.
     * Laravel13SmokeTest/KonselingHandoffTest don't use that trait, so this
     * is a no-op for them.
     *
     * Why this matters: RefreshDatabase::$migrated is a STATIC flag shared
     * across the whole PHPUnit process. The first RefreshDatabase test that
     * runs triggers `migrate:fresh` once; every RefreshDatabase test after it
     * just reuses that same migrated schema inside its own transaction. If
     * that first migrate:fresh doesn't seed, the DB stays empty of the
     * admin/student accounts that Laravel13SmokeTest and KonselingHandoffTest
     * fetch via User::where(...)->firstOrFail() (they don't use
     * RefreshDatabase themselves, but they DO share the same test-run
     * database connection once a RefreshDatabase test has wiped it).
     * Setting $seed = true here makes that one process-wide migrate:fresh run
     * with --seed, so DatabaseSeeder::run() (unchanged) always populates the
     * DB before any test — RefreshDatabase or not — executes.
     */
    protected $seed = true;
}
