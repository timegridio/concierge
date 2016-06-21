<?php

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Pagination\Paginator;
use Timegridio\Concierge\Addressbook;
use Timegridio\Concierge\Models\Contact;

class AddressbookTest extends TestCaseDB
{
    use DatabaseTransactions;
    use CreateUser, CreateContact, CreateBusiness, CreateService, CreateVacancy, ArrangeFixture;

    /**
     * @test
     */
    public function it_lists_all_contacts()
    {
        $this->arrangeFixture();

        $contacts = $this->business->addressbook()->listing(10);

        $this->assertInstanceOf(Paginator::class, $contacts);
    }

    /**
     * @test
     */
    public function it_finds_a_contact()
    {
        $this->arrangeFixture();

        $contact = $this->createContact();

        $this->business->contacts()->save($contact);

        $foundContact = $this->business->addressbook()->find($contact);

        $this->assertInstanceOf(Contact::class, $foundContact);
    }

    /**
     * @test
     */
    public function it_registers_a_new_contact()
    {
        $this->arrangeFixture();

        $contact = $this->makeContact();

        $registeredContact = $this->business->addressbook()->register($contact->toArray());

        $this->assertInstanceOf(Contact::class, $registeredContact);
        $this->assertArraySubset($contact->toArray(), $registeredContact->toArray());
    }

    /**
     * @test
     */
    public function it_updates_a_contact()
    {
        $this->arrangeFixture();

        $contact = $this->createContact();

        $this->business->contacts()->save($contact);

        $newData = $contact->toArray();

        $newData['firstname'] = 'Coolname';
        $newData['lastname'] = 'Updated';

        $updatedContact = $this->business->addressbook()->update($contact, $newData);

        $this->assertInstanceOf(Contact::class, $updatedContact);
        $this->assertEquals($contact->firstname, $updatedContact->firstname);
        $this->assertEquals($contact->lastname, $updatedContact->lastname);
    }

    /**
     * @test
     */
    public function it_reuses_an_existing_contact()
    {
        $this->arrangeFixture();

        $contact = $this->createContact();

        $contact->email = 'reuseme@example.org';

        $this->business->contacts()->save($contact);

        $reuseContact = $this->business->addressbook()->getSubscribed('reuseme@example.org');

        $this->assertInstanceOf(Contact::class, $reuseContact);
        $this->assertTrue($reuseContact->businesses->contains($this->business));
    }

    /**
     * @test
     */
    public function it_gets_an_existing_subscribed_contact()
    {
        $this->arrangeFixture();

        $contact = $this->makeContact($this->issuer, [
            'email' => 'reuseme@example.org',
            ]);
        $this->business->contacts()->save($contact);

        $reuseContact = $this->business->addressbook()->getExisting('reuseme@example.org');

        $this->assertInstanceOf(Contact::class, $reuseContact);
        $this->assertTrue($reuseContact->businesses->contains($this->business));
    }

    /**
     * @test
     */
    public function it_gets_an_existing_subscribed_and_registered_contact()
    {
        $this->arrangeFixture();

        $contact = $this->makeContact($this->issuer);
        $this->business->contacts()->save($contact);

        $reuseContact = $this->business->addressbook()->getRegisteredUserId($this->issuer->id);

        $this->assertInstanceOf(Contact::class, $reuseContact);
        $this->assertTrue($reuseContact->businesses->contains($this->business));
    }

    /**
     * @test
     */
    public function it_removes_a_contact()
    {
        $this->arrangeFixture();

        $contact = $this->makeContact($this->issuer);
        $this->business->contacts()->save($contact);

        $return = $this->business->addressbook()->remove($contact);

        $this->assertEquals(1, $return);
        $this->assertFalse($contact->businesses->contains($this->business));
    }

    /**
     * @test
     */
    public function it_links_a_contact_to_user_id()
    {
        $this->arrangeFixture();

        $contact = $this->createContact();
        $this->business->contacts()->save($contact);

        $this->business->addressbook()->linkToUserId($contact, $this->issuer->id);

        $this->assertEquals($contact->user->id, $this->issuer->id);
    }

    /**
     * @test
     */
    public function it_copies_from_another_contact()
    {
        $this->arrangeFixture();

        $contact = $this->createContact();

        $copyContact = $this->business->addressbook()->copyFrom($contact, $this->issuer->id);

        $this->assertEquals($copyContact->user->id, $this->issuer->id);
        $this->assertTrue($copyContact->businesses->contains($this->business));
    }
}
