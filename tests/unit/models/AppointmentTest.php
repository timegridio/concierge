<?php

use Carbon\Carbon;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Timegridio\Concierge\Models\Appointment;
use Timegridio\Concierge\Models\Vacancy;

class AppointmentTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateContact, CreateBusiness, CreateAppointment, CreateVacancy;

    /**
     * @test
     */
    public function it_creates_an_appointment()
    {
        $appointment = $this->createAppointment();

        $this->assertInstanceOf(Appointment::class, $appointment);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::user
     * @test
     */
    public function it_gets_the_contact_user_of_appointment()
    {
        $user = $this->createUser();

        $contact = $this->makeContact($user);
        $contact->save();

        $business = $this->makeBusiness($user);
        $business->save();

        $appointment = $this->makeAppointment($business, $user, $contact);

        $this->assertEquals($user, $appointment->user());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::scopeOfBusiness
     * @test
     */
    public function it_scopes_for_business()
    {
        $this->assertInstanceOf(Illuminate\Database\Eloquent\Builder::class, Appointment::ofBusiness(1));
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::scopeOfBusiness
     * @test
     */
    public function it_scopes_unarchived()
    {
        $this->assertInstanceOf(Illuminate\Database\Eloquent\Builder::class, Appointment::unarchived());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::scopeOfBusiness
     * @test
     */
    public function it_scopes_unserved()
    {
        $this->assertInstanceOf(Illuminate\Database\Eloquent\Builder::class, Appointment::unserved());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::user
     * @test
     */
    public function it_gets_no_user_from_contact_of_appointment()
    {
        $issuer = $this->makeUser();
        $contact = $this->makeContact();
        $business = $this->makeBusiness($issuer);
        $appointment = $this->makeAppointment($business, $issuer, $contact);

        $this->assertNull($appointment->user());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::duplicates
     * @test
     */
    public function it_detects_a_duplicate_appointment()
    {
        $issuer = $this->createUser();

        $contact = $this->createContact();

        $business = $this->makeBusiness($issuer);
        $business->save();

        $appointment = $this->makeAppointment($business, $issuer, $contact);
        $appointment->save();

        $appointmentDuplicate = $appointment->replicate();

        $this->assertTrue($appointmentDuplicate->duplicates());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::getFinishAtAttribute
     * @test
     */
    public function it_gets_the_finish_datetime_of_appointment()
    {
        $appointment = $this->createAppointment([
            'startAt'  => Carbon::parse('2015-12-08 08:00:00 UTC'),
            'duration' => 90,
        ]);

        $startAt = $appointment->startAt;
        $finishAt = $appointment->finishAt;

        $this->assertEquals('2015-12-08 09:30:00', $finishAt);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::vacancy
     * @test
     */
    public function it_gets_the_associated_vacancy()
    {
        $appointment = $this->createAppointment([
            'startAt'     => Carbon::parse('2015-12-08 08:00:00 UTC'),
            'duration'    => 90,
            ]);

        $this->assertInstanceOf(Vacancy::class, $appointment->vacancy);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::getDateAttribute
     * @test
     */
    public function it_gets_the_date_attribute_at_000000utc()
    {
        $business = $this->createBusiness();

        $appointment = $this->createAppointment([
            'business_id' => $business->id,
            'startAt'     => Carbon::parse('2015-12-08 00:00:00 UTC'),
            'duration'    => 90,
            ]);

        $this->assertEquals($appointment->start_at->timezone($business->timezone)->toDateString(), $appointment->date);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::getDateAttribute
     * @test
     */
    public function it_gets_the_date_attribute_at_120000utc()
    {
        $business = $this->createBusiness();

        $appointment = $this->createAppointment([
            'business_id' => $business->id,
            'startAt'     => Carbon::parse('2015-12-08 12:00:00 UTC'),
            'duration'    => 90,
            ]);

        $this->assertEquals($appointment->start_at->timezone($business->timezone)->toDateString(), $appointment->date);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::getDateAttribute
     * @test
     */
    public function it_gets_the_date_attribute_at_235959utc()
    {
        $business = $this->createBusiness();

        $appointment = $this->createAppointment([
            'business_id' => $business->id,
            'startAt'     => Carbon::parse('2015-12-08 23:59:59 UTC'),
            'duration'    => 90,
            ]);

        $this->assertEquals($appointment->start_at->timezone($business->timezone)->toDateString(), $appointment->date);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::isReserved
     * @test
     */
    public function it_returns_is_reserved()
    {
        $appointment = $this->createAppointment([
            'status' => Appointment::STATUS_RESERVED,
            ]);

        $this->assertTrue($appointment->isReserved());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::isPending
     * @test
     */
    public function it_returns_is_pending()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_RESERVED,
            ]);

        $this->assertTrue($appointment->isPending());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::isPending
     * @test
     */
    public function it_returns_is_not_pending()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->subDays(1),
            'status'   => Appointment::STATUS_RESERVED,
            ]);

        $this->assertFalse($appointment->isPending());
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doConfirm
     * @test
     */
    public function it_changes_status_to_confirmed()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_RESERVED,
            ]);

        $appointment->doConfirm();

        $this->assertEquals(Appointment::STATUS_CONFIRMED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doCancel
     * @test
     */
    public function it_changes_status_to_canceled()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_CANCELED,
            ]);

        $appointment->doCancel();

        $this->assertEquals(Appointment::STATUS_CANCELED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doServe
     * @test
     */
    public function it_changes_status_to_served()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_SERVED,
            ]);

        $appointment->doCancel();

        $this->assertEquals(Appointment::STATUS_SERVED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doReserve
     * @test
     */
    public function it_sets_status_to_reserved()
    {
        $appointment = $this->makeAppointment($this->createBusiness(), $this->createUser(), $this->createContact(), [
            'status'   => null,
            'start_at' => Carbon::now()->addDays(5),
            ]);

        $appointment->doReserve();

        $this->assertEquals(Appointment::STATUS_RESERVED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doServe
     * @test
     */
    public function it_cannot_serve_if_canceled()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->subDays(1),
            'status'   => Appointment::STATUS_CANCELED,
            ]);

        $appointment->doServe();

        $this->assertEquals(Appointment::STATUS_CANCELED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doServe
     * @test
     */
    public function it_cannot_confirm_if_canceled()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->subDays(1),
            'status'   => Appointment::STATUS_CANCELED,
            ]);

        $appointment->doConfirm();

        $this->assertEquals(Appointment::STATUS_CANCELED, $appointment->status);
    }

    /**
     * @covers \Timegridio\Concierge\Models\Appointment::doServe
     * @test
     */
    public function it_cannot_cancel_if_served()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->subDays(1),
            'status'   => Appointment::STATUS_SERVED,
            ]);

        $appointment->doServe();

        $this->assertEquals(Appointment::STATUS_SERVED, $appointment->status);
    }

    /**
     * @test
     */
    public function it_is_considered_active_if_in_reserved_status()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_RESERVED,
            ]);

        $this->assertTrue($appointment->isActive());
    }

    /**
     * @test
     */
    public function it_is_considered_active_if_in_confirmed_status()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_CONFIRMED,
            ]);

        $this->assertTrue($appointment->isActive());
    }

    /**
     * @test
     */
    public function it_is_considered_due_if_in_the_past()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->subDays(1),
            'status'   => Appointment::STATUS_CONFIRMED,
            ]);

        $this->assertTrue($appointment->isDue());
        $this->assertFalse($appointment->isFuture());
    }

    /**
     * @test
     */
    public function it_is_considered_future_if_in_the_future()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_CONFIRMED,
            ]);

        $this->assertTrue($appointment->isFuture());
        $this->assertFalse($appointment->isDue());
    }

    /**
     * @test
     */
    public function it_is_considered_pending_if_in_the_future_and_active()
    {
        $appointment = $this->createAppointment([
            'start_at' => Carbon::now()->addDays(1),
            'status'   => Appointment::STATUS_CONFIRMED,
            ]);

        $this->assertTrue($appointment->isPending());
    }

    /**
     * @test
     */
    public function it_provides_a_duration_in_minutes_based_on_start_and_finish_time()
    {
        $appointment = $this->createAppointment();

        $this->assertInternalType('int', $appointment->duration());
    }
}
