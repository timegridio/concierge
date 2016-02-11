<?php

use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Category;

/**
 * @covers Timegridio\Concierge\Models\Category
 */
class CategoryTest extends TestCaseDB
{
    use DatabaseTransactions;

    /** @test */
    public function it_has_many_businesses()
    {
        $category = new Category();

        $this->assertInstanceOf(HasMany::class, $category->businesses());
    }
}
