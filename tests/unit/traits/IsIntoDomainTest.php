<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Tests\Models\IntoDomainStub;

class IsIntoDomainTest extends TestCaseDB
{
    use DatabaseTransactions;

    /**
     * @test
     */
    public function it_is_into_a_domain()
    {
        $intoDomain = new IntoDomainStub();

        $this->assertInstanceOf(BelongsTo::class, $intoDomain->domain());
    }
}
