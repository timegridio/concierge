<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Presenters\BusinessPresenter;

class BusinessPresenterTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateBusiness;

    protected $businessPresenter;

    public function setUp()
    {
        parent::setUp();

        $this->businessPresenter = $this->createBusinessPresenter();
    }

    /**
     * @test
     */
    public function it_has_a_presenter()
    {
        $business = $this->createBusinessPresenter();

        $this->assertEquals(BusinessPresenter::class, $business->getPresenterClass());
    }

    /**
     * @test
     */
    public function it_has_a_facebook_image_with_profile_alias()
    {
        $business = $this->createBusinessPresenter([
            'social_facebook' => 'https://www.facebook.com/timegrid.io/',
            ]);

        $img = $business->facebookImg();

        $expected = "<img class=\"img-thumbnail media-object\" src=\"http://graph.facebook.com/timegrid.io/picture?type=square\" height=\"100\" width=\"100\" alt=\"{$business->name}\"/>";

        $this->assertInternalType('string', $img);
        $this->assertEquals($expected, $img);
    }

    /**
     * @test
     */
    public function it_has_a_facebook_image_with_profile_id()
    {
        $business = $this->createBusinessPresenter([
            'social_facebook' => 'https://www.facebook.com/profile.php?id=1000000000000000',
            ]);

        $img = $business->facebookImg();

        $expected = "<img class=\"img-thumbnail media-object\" src=\"http://graph.facebook.com/1000000000000000/picture?type=square\" height=\"100\" width=\"100\" alt=\"{$business->name}\"/>";

        $this->assertInternalType('string', $img);
        $this->assertEquals($expected, $img);
    }

    /**
     * @test
     */
    public function it_has_a_facebook_empty_image_placeholder()
    {
        $business = $this->createBusinessPresenter([
            'social_facebook' => null,
            ]);

        $img = $business->facebookImg();

        $expected = "<img class=\"img-thumbnail\" src=\"//placehold.it/100x100\" height=\"100\" width=\"100\" alt=\"{$business->name}\"/>";

        $this->assertInternalType('string', $img);
        $this->assertEquals($expected, $img);
    }

    /**
     * @test
     */
    public function it_has_a_static_map()
    {
        $business = $this->createBusinessPresenter();

        $map = $business->staticMap();

        $this->assertInternalType('string', $map);
    }

    /**
     * @test
     */
    public function it_has_a_industry_icon()
    {
        $business = $this->createBusinessPresenter([
            'category_id' => 'factory:Timegridio\Concierge\Models\Category',
            ]);

        $icon = $business->industryIcon();

        $this->assertInternalType('string', $icon);
    }

    /////////////
    // Helpers //
    /////////////

    protected function createBusinessPresenter($overrides = [])
    {
        return new BusinessPresenter($this->createBusiness($overrides));
    }
}
