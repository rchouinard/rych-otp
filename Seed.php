<?php
/**
 * OATH-OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

use Rych\Random\Random;
use Rych\Random\Encoder\EncoderInterface;
use Rych\Random\Encoder\Base32Encoder;
use Rych\Random\Encoder\HexEncoder;
use Rych\Random\Encoder\RawEncoder;

/**
 * OTP Seed/Key
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2013, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */
class Seed
{

    const FORMAT_BASE32 = 'base32';
    const FORMAT_HEX = 'hex';
    const FORMAT_RAW = 'raw';

    /**
     * @var EncoderInterface
     */
    private $encoder;

    /**
     * @var string
     */
    private $value;

    /**
     * Class constructor.
     *
     * @param string $value The seed value.
     * @return void
     */
    public function __construct($value = null)
    {
        if ($value !== null) {
            $this->setValue($value);
        }
        $this->setFormat(self::FORMAT_HEX);
    }

    /**
     * Get the seed value in the optionally specified format.
     *
     * @param string $format The output format.
     * @return string Returns the seed value optionally encoded as $format.
     */
    public function getValue($format = null)
    {
        return $this->encode($this->value, $format);
    }

    /**
     * Set the seed value, optionally specifying an input format.
     *
     * @param string $value The seed value.
     * @param string $format The input format.
     * @return self
     */
    public function setValue($value, $format = null)
    {
        $this->value = $this->decode($value, $format);
    }

    /**
     * Get the current format setting.
     *
     * @return string Returns the current default format.
     */
    public function getFormat()
    {
        switch (true)
        {
            case ($this->encoder instanceof Base32Encoder):
                $format = self::FORMAT_BASE32;
                break;
            case ($this->encoder instanceof HexEncoder):
                $format = self::FORMAT_HEX;
                break;
            case ($this->encoder instanceof RawEncoder):
            default:
                $format = self::FORMAT_RAW;
                break;
        }

        return $format;
    }

    /**
     * Set the format setting.
     *
     * @param string $format The format.
     * @return self
     */
    public function setFormat($format)
    {
        switch ($format) {
            case self::FORMAT_BASE32:
                $this->encoder = new Base32Encoder;
                break;
            case self::FORMAT_HEX:
                $this->encoder = new HexEncoder;
                break;
            case self::FORMAT_RAW:
            default:
                $this->encoder = new RawEncoder;
                break;
        }

        return $this;
    }

    /**
     * Output the seed value as a string.
     *
     * @return string
     */
    public function __toString()
    {
        $value = $this->value;
        if ($this->encoder instanceof EncoderInterface) {
            $value = $this->encoder->encode($value);
        }

        return (string) $value;
    }

    /**
     * Attempt to decode a seed value with one of the Encoder classes.
     *
     * @param string $seed The encoded seed value.
     * @return string Returns the decoded seed value.
     */
    private function decode($seed, $format = null)
    {
        $encoder = new RawEncoder;

        // Auto-detect
        if ($format === null) {
            if (preg_match('/^[0-9a-f]+$/i', $seed)) {
                $encoder = new HexEncoder;
            } else if (preg_match('/^[2-7a-z]+$/i', $seed)) {
                $encoder = new Base32Encoder;
            }
        // User-specified
        } else {
            if ($format == self::FORMAT_HEX) {
                $encoder = new HexEncoder;
            } else if ($format == self::FORMAT_BASE32) {
                $encoder = new Base32Encoder;
            }
        }

        $output = $encoder->decode($seed);

        return $output;
    }

    /**
     * Attempt to encode a seed value with one of the Encoder classes.
     *
     * @param string $seed The seed value.
     * @return string Returns the encoded seed value.
     */
    private function encode($seed, $format = null)
    {
        $encoder = $this->encoder;

        if ($format == self::FORMAT_HEX) {
            $encoder = new HexEncoder;
        } else if ($format == self::FORMAT_BASE32) {
            $encoder = new Base32Encoder;
        } else if ($format == self::FORMAT_RAW) {
            $encoder = new RawEncoder;
        }

        $output = $encoder->encode($seed);

        return $output;
    }

    /**
     * Generate a new Seed instance with a new random seed value.
     *
     * @param integer $bytes Number of bytes to use. Default of 20 produces an
     *     160-bit seed value as recommended by RFC 4226 Section 4 R6.
     * @param \Rych\Random\Random $random Optional pre-configured instance of
     *     the random generator class.
     * @return Seed Returns a preconfigured instance of Seed.
     */
    public static function generate($bytes = 20, Random $random = null)
    {
        if (!$random) {
            $random = new Random;
        }
        $output = $random->getRandomBytes((int) $bytes);

        return new Seed($output);
    }

}
