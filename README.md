# Concierge

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-scrutinizer]][link-scrutinizer]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

Concierge is a simple reservation library for your Laravel 5 app.

## Install

Via Composer

``` bash
$ composer require timegridio/concierge
```

## Usage

``` php
$concierge = new Timegridio\Concierge();
$appointment = $this->concierge->makeReservation($user, $business, $contact, $service, $date);
```

> **ADVICE:** This package is under development and API may change. Join development!

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
[ico-scrutinizer]: https://img.shields.io/scrutinizer/coverage/g/timegridio/concierge.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/scrutinizer/g/timegridio/concierge.svg?style=flat-square
[ico-downloads]: https://img.shields.io/packagist/dt/timegridio/concierge.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/timegridio/concierge
[link-travis]: https://travis-ci.org/timegridio/concierge
[link-scrutinizer]: https://scrutinizer-ci.com/g/timegridio/concierge/code-structure
[link-code-quality]: https://scrutinizer-ci.com/g/timegridio/concierge
[link-downloads]: https://packagist.org/packages/timegridio/concierge
[link-author]: https://github.com/alariva
[link-contributors]: ../../contributors