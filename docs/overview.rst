.. _introduction.overview:

********
Overview
********

This library aims to make adding two-factor authentication to a PHP application
simple and easy. To achieve this goal, the library provides two types of
standard one-time password schemes: counter and time-based. These schemes are
supported by a wide variety of devices and mobile apps, including Google
Authenticator.

Shared Secrets
--------------

One-time passwords rely on a shared secret, known only to the application and
authenticator device. This library manages the generation of secure secrets
using the `\\Rych\\OTP\\Seed` class.

.. code-block:: php

    <?php
    require 'vendor/autoload.php';
    use Rych\OTP;

    /* @var \Rych\OTP\Seed $secret */
    $secret = OTP\Seed::generate();

    // Display the secret as a hex value
    echo $secret->getValue(OTP\Seed::FORMAT_HEX);

When registering a user to use OTP two-factor authentication, a new shared
secret should be generated and stored with the user's record. It will be used
again in the future when authenticating that user.

Counters
--------

The other key element to one-time passwords is the counter value. For RFC-4226
"HOTP" passwords, this is a numeric value which is stored with both the
application and the authenticator device and incremented with each successful
authentication.

Time-based, or RFC-6238, passwords use the current timestamp as the counter
value. For these types of OTPs to work, the application and authenticator device
must have accurate clocks.

Since counters are pretty straight-forward incrementing values, the library does
not provide any means of managing their values.

Authentication Flow
-------------------

Authentication with OTPs is straight-forward. The user will authenticate using
their standard means of login. The application should detect that the user has
two-factor authentication enabled and challenge the user to enter a numeric
OTP. The user should request an OTP from their device and enter the displayed
value.

The application will then pull two bits of information from the user's record:
the user's shared secret, and the user's current counter value. Using these
values and this library, the application can validate the user's OTP and
either continue with the authentication flow or log the user out.

An example of validating a user's OTP value is below:

.. code-block:: php

    <?php
    require 'vendor/autoload.php';
    use Rych\OTP;

    $secret = $userObject->getSharedSecret();
    $counter = $userObject->getCounter();
    $otp = $requestObject->getRequestVar('otp');

    $otpValidator = new OTP\HOTP($secret);
    if ($otpValidator->validate($otp, $counter)) {
        // User is authenticated! Make sure to store the new counter value...
        $userObject->setCounter($counter + $otpValidator->getLastValidCounterOffset());
    } else {
        // OTP was wrong. Either reprompt of log out with an error.
    }
