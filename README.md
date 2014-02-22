OATH-OTP Implementation for PHP
===============================

[![Build Status](https://secure.travis-ci.org/rchouinard/rych-otp.png?branch=develop)](https://travis-ci.org/rchouinard/rych-otp)
[![Coverage Status](https://coveralls.io/repos/rchouinard/rych-otp/badge.png?branch=develop)](https://coveralls.io/r/rchouinard/rych-otp?branch=develop)
[![Dependency Status](https://www.versioneye.com/php/rych:otp/dev-develop/badge.png)](https://www.versioneye.com/php/rych:otp/dev-develop)

This library provides HMAC and time-based one-time password functionality as
defined by [RFC 4226](http://www.ietf.org/rfc/rfc4226.txt) and
[RFC 6238](http://www.ietf.org/rfc/rfc6238.txt) for PHP 5.3+.

Build status: [![Build Status](https://travis-ci.org/rchouinard/rych-otp.png?branch=master)](https://travis-ci.org/rchouinard/rych-otp)

Quick Start
-----------

Enabling one-time passwords in an application is fairly easy. A user will
register and verify their authenticator device with the application, and
subsequent logins should require the entry of a one-time password displayed on
the device as well as the usual username and password.

A shared secret is generated and stored with the application and configured
on the user's device during the registration phase. All OTP operations will
then use the same shared secret for that user. A user should only have one
shared secret, and a shared secret should belong to only one user.

The library makes generating and sharing secret keys easy.

```php
<?php

use Rych\OTP\Seed;

// Generates a 20-byte (160-bit) secret key
$otpSeed = Seed::generate();

// Display secret key details
printf("Secret (HEX): %s\n", $otpSeed->getValue(Seed::FORMAT_HEX));
printf("Secret (BASE32): %s\n", $otpSeed->getValue(Seed::FORMAT_BASE32));
```

When a user attempts to login, they should be prompted to provide the OTP
displayed on their device. The library can then validate the provided OTP
using the user's shared secret key.

```php
<?php

use Rych\OTP\HOTP;

$otpSeed = $userObject->getOTPSeed();
$otpCounter = $userObject->getOTPCounter();
$providedOTP = $requestObject->getPost('otp');

$otplib = new HOTP($otpSeed);
if ($otplib->validate($providedOTP, $otpCounter)) {
    // Advance the application's stored counter
    // This bit is important for HOTP but not done for TOTP
    $userObject->incrementOTPCounter($otplib->getLastValidCounterOffset() + 1);

    // Now the user is authenticated
}
```

Time-based OTPs are handled the same way, except you don't have a counter value
to track or increment.


Installation via [Composer](http://getcomposer.org/)
------------

 * Install Composer to your project root:
    ```bash
    curl -sS https://getcomposer.org/installer | php
    ```

 * Add a `composer.json` file to your project:
    ```json
    {
      "require" {
        "rych/otp": "1.0.*"
      }
    }
    ```

 * Run the Composer installer:
    ```bash
    php composer.phar install
    ```
