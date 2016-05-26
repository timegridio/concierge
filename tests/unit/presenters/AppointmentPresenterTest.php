<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Presenters\AppointmentPresenter;

class AppointmentPresenterTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateAppointment;

    protected $appointmentPresenter;

    public function setUp()
    {
        parent::setUp();

        $this->appointmentPresenter = $this->createAppointmentPresenter();
    }

    /**
     * @test
     */
    public function it_has_a_presenter()
    {
        $appointment = $this->createAppointmentPresenter();

        $this->assertEquals(AppointmentPresenter::class, $appointment->getPresenterClass());
    }

    /**
     * @test
     */
    public function it_has_a_human_friendly_code()
    {
        $appointment = $this->createAppointmentPresenter();

        $code = $appointment->code();
        $codeLength = $appointment->business->pref('appointment_code_length');

        $this->assertInternalType('string', $code);
        $this->assertEquals($codeLength, strlen($code));
    }

    /**
     * @test
     */
    public function it_has_a_normal_date_for_any_other_date()
    {
        $carbon = Carbon::parse('today')->addDays(7);

        $appointment = $this->createAppointmentPresenter([
            'start_at' => $carbon,
            ]);

        $date = $appointment->date();

        $this->assertInternalType('string', $date);
        $this->assertEquals($carbon->toDateString(), $date);
    }

    /**
     * @test
     */
    public function it_has_a_strict_arrival_time()
    {
        $carbon = Carbon::parse('today')->addDays(7);

        $appointment = $this->createAppointmentPresenter([
            'start_at' => $carbon,
            'finish_at' => $carbon->addHours(1),
            ]);

        $timeFormat = 'H:i';

        $appointment->business->pref('time_format', $timeFormat);
        $appointment->business->pref('appointment_flexible_arrival', false);

        $arriveAt = $appointment->arriveAt();

        $this->assertInternalType('array', $arriveAt);
        $this->assertInternalType('string', $arriveAt['at']);
        $this->assertNotEmpty($arriveAt['at']);
        $this->assertEquals(Carbon::parse($arriveAt['at'])->format($timeFormat), $carbon->format($timeFormat));
    }

    /**
     * @test
     */
    public function it_has_a_flexible_arrival_time()
    {
        $carbon = Carbon::parse('today')->addDays(7);

        $appointment = $this->createAppointmentPresenter([
            'start_at' => $carbon,
            'finish_at' => $carbon->addHours(1),
            ]);

        $timeFormat = 'H:i';

        $appointment->business->pref('time_format', $timeFormat);
        $appointment->business->pref('appointment_flexible_arrival', true);

        $arriveAt = $appointment->arriveAt();

        $this->assertInternalType('array', $arriveAt);
        $this->assertNotEmpty($arriveAt['from']);
        $this->assertNotEmpty($arriveAt['to']);
    }

    /**
     * @test
     */
    public function it_has_a_timezone_attribute()
    {
        $carbon = Carbon::parse('today')->addDays(7);

        $appointment = $this->createAppointmentPresenter([
            'start_at' => $carbon,
            'finish_at' => $carbon->addHours(1),
            ]);

        $appointment->setTimezone('Europe/Madrid');

        $this->assertInternalType('string', $appointment->timezone());
    }

    /**
     * @test
     */
    public function it_maps_statuses_to_a_bootstrap_css_class()
    {
        $statuses = [
            Appointment::STATUS_CANCELED => 'danger',
            Appointment::STATUS_RESERVED  => 'warning',
            Appointment::STATUS_CONFIRMED => 'success',
            Appointment::STATUS_SERVED    => 'default',
        ];

        foreach ($statuses as $key => $status) {
            $appointment = $this->createAppointmentPresenter([
                'status' => $key,
                ]);

            $this->assertEquals($appointment->statusToCssClass(), $status);
        }
    }

    /**
     * @test
     */
    public function it_provides_the_business_phone_number()
    {
        $appointment = $this->createAppointmentPresenter();

        $this->assertEquals($appointment->business->phone, $appointment->phone());
    }

    /**
     * @test
     */
    public function it_provides_the_business_location()
    {
        $appointment = $this->createAppointmentPresenter();

        $this->assertEquals($appointment->business->postal_address, $appointment->location());
    }

    /**
     * @test
     */
    public function it_provides_a_duration_string_in_minutes()
    {
        $appointment = $this->createAppointmentPresenter();

        $this->assertInternalType('string', $appointment->duration());
    }

    /////////////
    // Helpers //
    /////////////

    protected function createAppointmentPresenter($overrides = [])
    {
        return new AppointmentPresenter($this->createAppointment($overrides));
    }
}
