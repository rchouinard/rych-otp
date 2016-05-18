<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp;

class HotpProvider extends AbstractOtpProvider implements OtpProviderInterface
{
    public function generate($counter = 0)
    {
        return hotp_generate($this->secret->getValue(), $counter, (int) $this->options["digits"]);
    }

    public function validate($otp, $counter = 0)
    {
        return hotp_validate($this->secret->getValue(), $counter, $otp, $this->options["window"]);
    }
}
