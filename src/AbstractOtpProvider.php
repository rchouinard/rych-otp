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

abstract class AbstractOtpProvider implements OtpProviderInterface
{
    /**
     * @var array
     */
    protected $options = [
        "algorithm" => TOTP_SHA1,
        "digits" => 6,
        "step" => 30,
        "window" => 0,
    ];

    /**
     * @var Secret
     */
    protected $secret;

    /**
     * @param   Secret  $secret
     * @param   array   $options
     */
    public function __construct(Secret $secret, array $options = [])
    {
        $this->options = array_merge($this->options, $options);
        $this->secret = $secret;
    }
}
