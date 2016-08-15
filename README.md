# Concierge - Laravel 5.x

## ABOUT THIS BRANCH

This is a development Work In Progress branch to get a new (and decoupled) booking Library.

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Test Coverage][ico-codeclimate-coverage]][link-codeclimate-coverage]
[![Code Climate][ico-codeclimate-quality]][link-codeclimate-quality]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Concierge is a simple reservation library for your Laravel 5 app.

## Usage

``` php

    $concierge = new Concierge();

    $reservation = [
        'business' => $business,
        'contact'  => $contact,
        'service'  => $service,
        'date'     => '2016-03-26',
        'time'     => '10:30',
        'timezone' => $business->timezone,
        'comments' => 'Hello, Dr.!',
    ];

    $appointment = $concierge->business($business)->takeReservation($reservation);
```

> **ADVICE:** This package is under development and API may change. Join development!

See the [Concierge Unit Tests](https://github.com/timegridio/concierge/blob/master/tests/integration/concierge/ConciergeUnitTest.php) for more and current examples.

## Install

### Step 1

Via Composer

``` bash
$ composer require timegridio/concierge=dev-master
```

> **ADVICE:** Note that this library is currently under development and API may change.

### Step 2

Add the following item to **config/app.php**

**Providers array:**

    'Timegridio\Concierge\TimegridioConciergeServiceProvider'

or

    Timegridio\Concierge\TimegridioConciergeServiceProvider::class

### Step 3

#### Migration

Publish the migration as well as the configuration of notifynder with the following command:

    php artisan vendor:publish --provider="Timegridio\Concierge\TimegridioConciergeServiceProvider"

Don't forget to migrate.

### Applications using this lib

  * [Timegrid](https://github.com/timegridio/timegrid): A marketplace for service providers that use online booking.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

``` bash
$ composer test
```

## Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) and [CONDUCT](CONDUCT.md) for details.

## Security

If you discover any security related issues, please email alariva@timegrid.io instead of using the issue tracker.

## Credits

- [Ariel Vallese][link-author]
- [All Contributors][link-contributors]

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.

[ico-version]: https://img.shields.io/packagist/v/timegridio/concierge.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/timegridio/concierge/master.svg?style=flat-square
[ico-codeclimate-coverage]: https://codeclimate.com/github/timegridio/concierge/badges/coverage.svg?style=flat-square
[ico-codeclimate-quality]: https://codeclimate.com/github/timegridio/concierge/badges/gpa.svg?style=flat-square
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/timegridio/concierge.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/timegridio/concierge.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/timegridio/concierge.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/timegridio/concierge
[link-travis]: https://travis-ci.org/timegridio/concierge
[link-codeclimate-coverage]: https://codeclimate.com/github/timegridio/concierge/coverage
[link-codeclimate-quality]: https://codeclimate.com/github/timegridio/concierge
[link-scrutinizer]: https://scrutinizer-ci.com/g/timegridio/concierge/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/timegridio/concierge
[link-downloads]: https://packagist.org/packages/timegridio/concierge
[link-author]: https://github.com/alariva
[link-contributors]: ../../contributors