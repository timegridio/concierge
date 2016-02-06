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
    public function it_has_a_human_friendly_date_for_today()
    {
        $appointment = $this->createAppointmentPresenter([
            'start_at' => Carbon::parse('today'),
            ]);

        $date = $appointment->date();

        $this->assertInternalType('string', $date);
        $this->assertEquals('Concierge::appointments.text.today', $date);
    }

    /**
     * @test
     */
    public function it_has_a_human_friendly_date_for_tomorrow()
    {
        $appointment = $this->createAppointmentPresenter([
            'start_at' => Carbon::parse('tomorrow'),
            ]);

        $date = $appointment->date();

        $this->assertInternalType('string', $date);
        $this->assertEquals('Concierge::appointments.text.tomorrow', $date);
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
    public function it_maps_statuses_to_a_bootstrap_css_class()
    {
        $statuses = [
            Appointment::STATUS_ANNULATED => 'danger',
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

    /////////////
    // Helpers //
    /////////////

    protected function createAppointmentPresenter($overrides = [])
    {
        return new AppointmentPresenter($this->createAppointment($overrides));
    }
}
