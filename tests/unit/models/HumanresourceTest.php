<?php

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Humanresource;

class HumanresourceTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateHumanresource;

    /**
     * @test
     */
    public function it_has_a_name()
    {
        $humanresource = $this->createHumanresource(['name' => 'John Doe']);

        $this->assertEquals('John Doe', $humanresource->name);
    }

    /**
     * @test
     */
    public function it_is_sluggable()
    {
        $humanresource = $this->createHumanresource(['name' => 'John Doe']);

        $this->assertEquals('john-doe', $humanresource->slug);
    }

    /**
     * @test
     */
    public function it_belongs_to_a_business()
    {
        $humanresource = $this->createHumanresource();

        $this->assertInstanceOf(BelongsTo::class, $humanresource->business());
    }
}
