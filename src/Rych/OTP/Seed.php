<?php
/**
 * OATH-OTP Library
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
 * @license MIT License - http://www.opensource.org/licenses/mit-license.php
 */

namespace Rych\OTP;

use Rych\OTP\Seed\EncoderInterface;
use Rych\OTP\Seed\Encoder\Base32 as Base32Encoder;
use Rych\OTP\Seed\Encoder\Hex as HexEncoder;
use Rych\OTP\Seed\Encoder\Raw as RawEncoder;

/**
 * OTP Seed/Key
 *
 * @package Rych\OTP
 * @author Ryan Chouinard <rchouinard@gmail.com>
 * @copyright Copyright (c) 2012, Ryan Chouinard
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
            if (HexEncoder::isValid($seed)) {
                $encoder = new HexEncoder;
            } else if (Base32Encoder::isValid($seed)) {
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
     * @return Seed Returns a preconfigured instance of Seed.
     */
    public static function generate($bytes = 20)
    {
        $bytes = (int) $bytes;
        $output = self::genRandomBytes($bytes);

        return new Seed($output);
    }

    /**
     * Generate a random byte string of the requested length.
     *
     * @param integer $count The number of bytes to generate.
     * @return string Returns a string of random bytes.
     */
    private static function genRandomBytes($length)
    {
        $length = (int) $length;
        $output = '';

        // Windows platforms
        if ((PHP_OS & "\xDF\xDF\xDF") === 'WIN') {
            // Try MCrypt first
            if (function_exists('mcrypt_create_iv')) {
                $output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);

            // On Windows, PHP versions < 5.3.4 have a potential blocking
            // condition with openssl_random_pseudo_bytes().
            } else if (function_exists('openssl_random_pseudo_bytes') && version_compare(PHP_VERSION, '5.3.4', '>=')) {
                $output = openssl_random_pseudo_bytes($length);
            }
        // Non-Windows platforms
        } else {
            // Try OpenSSL first
            if (function_exists('openssl_random_pseudo_bytes')) {
                $output = openssl_random_pseudo_bytes($length);
            // Attempt to read straight from /dev/urandom
            } else if (is_readable('/dev/urandom') && $fp = @fopen('/dev/urandom', 'rb')) {
                if (function_exists('stream_set_read_buffer')) {
                    stream_set_read_buffer($fp, 0);
                }
                $output = fread($fp, $length);
                fclose($fp);
            // Try mcrypt_create_iv() - basically reads from /dev/urandom, but
            // slower and without being limited by open_basedir.
            } else if (function_exists('mcrypt_create_iv')) {
                $output = mcrypt_create_iv($length, MCRYPT_DEV_URANDOM);
            }
        }

        // https://github.com/GeorgeArgyros/Secure-random-bytes-in-PHP
        // We don't read from /dev/urandom here, as we try that above.
        // If that had worked, we wouldn't be here now.
        if (strlen($output) != $length) {
            $output = '';
            $bitsPerRound = 2; // bits of entropy collected in each clock drift round
            $msecPerRound = 400; // expected running time of each round in microseconds
            $hashLength = 20; // SHA-1 Hash length
            $total = $length; // total bytes of entropy to collect

            do {
                $bytes = ($total > $hashLength) ? $hashLength : $total;
                $total -= $bytes;

                $entropy = rand() . uniqid(mt_rand(), true);
                $entropy .= implode('', @fstat(@fopen(__FILE__, 'r')));
                $entropy .= memory_get_usage();

                for ($i = 0; $i < 3; ++$i) {
                    $counter1 = microtime(true);
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < 50; ++$j) {
                        $var = sha1($var);
                    }
                    $counter2 = microtime(true);
                    $entropy .= $counter1 . $counter2;
                }

                $rounds = (int) ($msecPerRound * 50 / (int) (($counter2 - $counter1) * 1000000));
                $iterations = $bytes * (int) (ceil(8 / $bitsPerRound));

                for ($i = 0; $i < $iterations; ++$i) {
                    $counter1 = microtime();
                    $var = sha1(mt_rand());
                    for ($j = 0; $j < $rounds; ++$j) {
                        $var = sha1($var);
                    }
                    $counter2 = microtime();
                    $entropy .= $counter1 . $counter2;
                }

                $output .= sha1($entropy, true);
            } while ($length > strlen($output));
        }

        return substr($output, 0, $length);
    }

}
