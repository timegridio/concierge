<?php

use Timegridio\Concierge\Booking\Timetable;

class TimetableTest extends TestCaseDB
{
    protected $timetable;

    public function setUp()
    {
        parent::setUp();

        $this->timetable = new Timetable();
    }

    /**
     * @test
     */
    public function it_builds_a_date_range()
    {
        $days = 10;

        $timetable = $this->timetable->from('tomorrow')->future($days)->get();

        $this->assertInternalType('array', $timetable);
    }

    /**
     * @test
     */
    public function it_sets_a_slot_value()
    {
        $timetable = $this->timetable->from('tomorrow')->future(10)->get();

        $capacityValue = 1;

        $this->timetable->capacity('2016-02-03', '09:00', 'a-service', $capacityValue);

        $capacity = $this->timetable->capacity('2016-02-03', '09:00', 'a-service');

        $this->assertEquals($capacityValue, $capacity);
    }

    /**
     * @test
     */
    public function it_builds_in_default_dimensions_format()
    {
        $this->timetable->from('today')->future(10);

        $this->timetable->capacity('2016-02-03', '09:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-04', '09:30', 'a-service', 1);
        $this->timetable->capacity('2016-02-05', '10:30', 'a-service', 2);

        $timetable = $this->timetable->get();

        $this->assertInternalType('array', $timetable);
        $this->assertArraySubset(['2016-02-03' => ['a-service' => ['09:00' => 1]]], $timetable);
        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['09:30' => 1]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['10:30' => 2]]], $timetable);
    }

    /**
     * @test
     */
    public function it_builds_in_arbitrary_dimensions_format()
    {
        $this->timetable->format(':service:.:date:.:time:')->from('today')->future(10);

        $this->timetable->capacity('2016-02-03', '09:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-04', '09:30', 'a-service', 1);
        $this->timetable->capacity('2016-02-05', '10:30', 'a-service', 2);

        $timetable = $this->timetable->get();

        $this->assertInternalType('array', $timetable);

        $this->assertArraySubset(['a-service' => ['2016-02-03' => ['09:00' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-04' => ['09:30' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-05' => ['10:30' => 2]]], $timetable);
    }
}
