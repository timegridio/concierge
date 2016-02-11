<?php

use Timegridio\Concierge\Exceptions\StrategyNotRecognizedException;
use Timegridio\Concierge\Timetable\Timetable;

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
     * @ExpectedException Timegridio\Concierge\Exceptions\StrategyNotRecognizedException
     */
    public function it_rejects_an_unknown_strategy()
    {
        new Timetable('unknown-strategy', []);
    }

    /**
     * @test
     */
    public function it_initializes_a_base_matrix()
    {
        $days = 3;

        $timetable = $this->timetable
                          ->from('2016-02-04')
                          ->future($days)
                          ->services(['a-service'])
                          ->startAt('10:00')
                          ->finishAt('11:00')
                          ->get();

        $this->assertInternalType('array', $timetable);

        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['10:30:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['10:30:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-06' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-06' => ['a-service' => ['10:30:00' => 0]]], $timetable);
    }

    /**
     * @test
     */
    public function it_initializes_a_base_matrix_with_interval_hourly()
    {
        $days = 3;

        $timetable = $this->timetable
                          ->from('2016-02-04')
                          ->interval(60)
                          ->future($days)
                          ->services(['a-service'])
                          ->startAt('10:00')
                          ->finishAt('12:00')
                          ->get();

        $this->assertInternalType('array', $timetable);

        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['11:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['11:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-06' => ['a-service' => ['10:00:00' => 0]]], $timetable);
        $this->assertArraySubset(['2016-02-06' => ['a-service' => ['11:00:00' => 0]]], $timetable);
    }

    /**
     * @test
     */
    public function it_builds_a_date_range()
    {
        $days = 10;

        $dates = $this->timetable->from('tomorrow')->future($days)->inflateDates();

        $this->assertInternalType('array', $dates);
    }

    /**
     * @test
     */
    public function it_builds_a_time_range()
    {
        $times = $this->timetable->inflateTimes(0);

        $this->assertInternalType('array', $times);
    }

    /**
     * @test
     */
    public function it_sets_a_slot_value()
    {
        $timetable = $this->timetable->from('tomorrow')->future(10)->get();

        $capacityValue = 1;

        $this->timetable->capacity('2016-02-03', '09:00:00', 'a-service', $capacityValue);

        $capacity = $this->timetable->capacity('2016-02-03', '09:00:00', 'a-service');

        $this->assertEquals($capacityValue, $capacity);
    }

    /**
     * @test
     */
    public function it_builds_in_default_dimensions_format()
    {
        $this->timetable->from('today')->future(10);

        $this->timetable->capacity('2016-02-03', '09:00:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-04', '09:30:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-05', '10:30:00', 'a-service', 2);

        $timetable = $this->timetable->get();

        $this->assertInternalType('array', $timetable);
        $this->assertArraySubset(['2016-02-03' => ['a-service' => ['09:00:00' => 1]]], $timetable);
        $this->assertArraySubset(['2016-02-04' => ['a-service' => ['09:30:00' => 1]]], $timetable);
        $this->assertArraySubset(['2016-02-05' => ['a-service' => ['10:30:00' => 2]]], $timetable);
    }

    /**
     * @test
     */
    public function it_builds_in_arbitrary_dimensions_format_array()
    {
        $this->timetable->format(['service', 'date', 'time'])->from('today')->future(10);

        $this->timetable->capacity('2016-02-03', '09:00:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-04', '09:30:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-05', '10:30:00', 'a-service', 2);

        $timetable = $this->timetable->get();

        $this->assertInternalType('array', $timetable);

        $this->assertArraySubset(['a-service' => ['2016-02-03' => ['09:00:00' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-04' => ['09:30:00' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-05' => ['10:30:00' => 2]]], $timetable);
    }

    /**
     * @test
     */
    public function it_builds_in_arbitrary_dimensions_format_string()
    {
        $this->timetable->format('service.date.time')->from('today')->future(10);

        $this->timetable->capacity('2016-02-03', '09:00:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-04', '09:30:00', 'a-service', 1);
        $this->timetable->capacity('2016-02-05', '10:30:00', 'a-service', 2);

        $timetable = $this->timetable->get();

        $this->assertInternalType('array', $timetable);

        $this->assertArraySubset(['a-service' => ['2016-02-03' => ['09:00:00' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-04' => ['09:30:00' => 1]]], $timetable);
        $this->assertArraySubset(['a-service' => ['2016-02-05' => ['10:30:00' => 2]]], $timetable);
    }
}
