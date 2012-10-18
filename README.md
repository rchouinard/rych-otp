OATH-OTP Implementation for PHP
===============================

This library provides an [RFC 4226](http://www.ietf.org/rfc/rfc4226.txt)
compliant HOTP implementation for PHP.

Quick Start
-----------

The main class is `OTP\HOTP`, which provides methods for generating
client seeds, generating OTPs based on a counter, and verifying those
OTPs with an optional window for error.

A full two-factor authentication implementation based on this library
would require additional integration outside of the project scope.

### Generating a Seed

Seed values MUST be unique per client device, and should only be
generated once per device.

```php
<?php

$hotp = new HOTP;

// Generate a 20-byte (160-bit) seed value
// This will result in a 32-character base32 string
//
// Default is 20, so specifying it is completely optional
$seed = $hotp->generateSeed(20);

// Fetch your user object however you like
$user = new User($userId);

// Implementation details will vary here, but the point is to store
// the seed value with the user record for future use.
$user->setOTPSeed($seed);

// We now need to get the seed to the user so they can configure their
// device. The easiest way, supported by Google Authenticator, is to
// use a QR code.
//
// See http://code.google.com/p/google-authenticator/wiki/KeyUriFormat
$appLabel = urlencode('My Cool OTP App');
$qr = new QRCode("otpauth://hotp/$appLabel?secret=$seed");
$qr->render();
```

Once the client device is configured, it should start generating OTPs
for us! It's best to then verify that the device is working as expected
by verifying one or two OTPs from the client device before flagging the
user's acount to require the device for authentication. Implementation
details for this step are left to the developer.

### Verifying an OTP

Once a user has registered a client device with our application, the
hardest part is over. Now we just need to prompt the user for an OTP
once they've logged in through normal means.

```php
<?php

$hotp = new HOTP;

// Fetch your user object however you like
$user = new User($userId);

// Fetch the OTP seed value stored when we configured the device
$hotp->setSeed($user->getOTPSeed());

// We also need the sequential counter value we're currently on
// This value is incremented by one on each successful verification,
// as we'll see below.
$otpCounter = $user->getOTPCounter();

// Now we check the provided OTP using the stored counter value
// The last argument, 4, specifies a window to account for errors
//
// If the user clicks refresh on their device a few times to generate
// a new OTP without actually validating, the counters will be out
// of sync. This argument means that the user can do so up to 4 times
// before they're locked out.
//
// verifyOTP() returns the counter which generated the OTP on success,
// so we can re-sync our value.
if ($counter = $hotp->verifyOTP($_GET['otp'], $otpCounter, 4)) {
    $user->setOTPCounter($counter + 1);
    $user->save();
    
    // User has now completed OTP authentication.
}
```
