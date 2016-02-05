<?php

use Illuminate\Support\Facades\Artisan;

class MigrationTest extends TestCaseDB
{
    /**
     * @test
     */
    public function it_refreshes_rollbacks_and_seeds_the_database()
    {
        $this->assertNotNull($this->database);

        $exitCode = Artisan::call('migrate:refresh', ['--database' => $this->database]);

        $this->assertEquals(0, $exitCode);

        $exitCode = Artisan::call('migrate:rollback', ['--database' => $this->database]);

        $this->assertEquals(0, $exitCode);

        $exitCode = Artisan::call('migrate', ['--database' => $this->database]);

        $this->assertEquals(0, $exitCode);
    }
}
