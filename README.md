# OATH-OTP Implementation for PHP

[![Latest Version on Packagist][ico-version]][link-packagist]
[![Software License][ico-license]](LICENSE.md)
[![Build Status][ico-travis]][link-travis]
[![Coverage Status][ico-coveralls]][link-coveralls]
[![Quality Score][ico-code-quality]][link-code-quality]
[![Total Downloads][ico-downloads]][link-downloads]

This library provides HMAC and time-based one-time password functionality as
defined by [RFC 4226](http://www.ietf.org/rfc/rfc4226.txt) and
[RFC 6238](http://www.ietf.org/rfc/rfc6238.txt) for PHP.

## Installation

```bash
$ composer require rych/otp
```

## Usage

When a user attempts to login, they should be prompted to provide the OTP
displayed on their device. The library can then validate the provided OTP
using the user's shared secret key.

```php
<?php

use Rych\OTP\HOTP;

$otpSecret = $userObject->getOtpSecret();
$otpCounter = $userObject->getOtpCounter();
$providedOTP = $requestObject->getPost("otp");

// $otpSecret is now assumed to be the raw secret string, sans encoding

$otplib = new HOTP($otpSecret);
if ($otplib->verify($providedOTP, $otpCounter)) {
    // Advance the application's stored counter
    // This bit is important for HOTP but not done for TOTP
    $userObject->incrementOTPCounter($otplib->getLastOffset() + 1);

    // Now the user is authenticated
}
```

Time-based OTPs are handled the same way, except you don't have a counter value
to track or increment.

### Options

The OTP classes take an array of options as the second constructor argument.
In most cases the defaults are sufficient, but you may run across situations
where more control is required.

 - **digits** *(Default: 6)* - Length of OTP values returned by the class.
 - **hash_func** *(Default: sha1)* - Hash function used to caluculate OTP value.
 - **window** *(Default: 2)* - Number of counter values before and after the
   given value which should be considered valid. Used to handle counter drift.
 - **interval** *(TOTP only; Default: 30)* - Time in seconds an OTP value is valid.

## Change log

Please see [CHANGELOG](CHANGELOG.md) for more information what has changed recently.

## Testing

```bash
$ vendor/bin/phpunit
```

## Security

If you discover any security related issues, please email rchouinard@gmail.com instead of using the issue tracker.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.


[ico-version]: https://img.shields.io/packagist/v/rych/otp.svg?style=flat-square
[ico-license]: https://img.shields.io/badge/license-MIT-brightgreen.svg?style=flat-square
[ico-travis]: https://img.shields.io/travis/rchouinard/rych-otp.svg?style=flat-square
[ico-coveralls]: https://img.shields.io/coveralls/rchouinard/rych-otp.svg?style=flat-square
[ico-code-quality]: https://img.shields.io/sensiolabs/i/4441db2d-0872-4fa8-b3f7-6354863b7bdd.svg
[ico-downloads]: https://img.shields.io/packagist/dt/rych/otp.svg?style=flat-square

[link-packagist]: https://packagist.org/packages/rych/otp
[link-travis]: https://travis-ci.org/rchouinard/rych-otp
[link-coveralls]: https://coveralls.io/r/rchouinard/rych-otp
[link-code-quality]: https://insight.sensiolabs.com/projects/4441db2d-0872-4fa8-b3f7-6354863b7bdd
[link-downloads]: https://packagist.org/packages/rych/otp
[link-author]: https://github.com/rchouinard
[link-contributors]: https://github.com/rchouinard/rych-otp/graphs/contributors
