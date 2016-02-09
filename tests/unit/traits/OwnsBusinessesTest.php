<?php

use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Tests\Models\BusinessOwnerStub;

class OwnsBusinessesTest extends TestCaseDB
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_owns_a_business()
    {
        $owner = new BusinessOwnerStub();

        $this->assertInstanceOf(BelongsToMany::class, $owner->businesses());
    }
}
