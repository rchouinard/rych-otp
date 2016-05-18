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

interface OtpProviderInterface
{
    public function __construct(Secret $secret, array $options);
    public function generate($counter);
    public function validate($otp, $counter);
}
