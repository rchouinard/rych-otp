<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rych\Otp\Test;

use Rych\Otp\Utility;

class utility_functions_Test extends \PHPUnit_Framework_TestCase
{

    /**
     * @return  array
     */
    public function compareVectorProvider()
    {
        return [
            ["foobar", "foo",    false, false],
            ["foobar", "foobar", false, true],
            ["foobar", "barbaz", false, false],
            ["foobar", "foo",    true,  false],
            ["foobar", "foobar", true,  true],
            ["foobar", "barbaz", true,  false],
        ];
    }

    /**
     * @return  array
     */
    public function counterVectorProvider()
    {
        return [
            [strtotime("1970-01-01 00:00:59 UTC"), 1],
            [strtotime("2005-03-18 01:58:29 UTC"), 37037036],
            [strtotime("2005-03-18 01:58:31 UTC"), 37037037],
            [strtotime("2009-02-13 23:31:30 UTC"), 41152263],
            [strtotime("2033-05-18 03:33:20 UTC"), 66666666],
        ];
    }

    /**
     * @test
     * @dataProvider compareVectorProvider
     */
    public function secure_compare($known, $user, $use_builtin, $is_valid)
    {
        $this->assertSame($is_valid, Utility\secure_compare($known, $user, $use_builtin));
    }

    /**
     * @test
     * @dataProvider counterVectorProvider
     */
    public function timestamp_to_counter($timestamp, $counter)
    {
        $this->assertSame($counter, Utility\timestamp_to_counter($timestamp, 30));
    }

}
