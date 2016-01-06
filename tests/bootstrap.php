<?php
/**
 * This file is part of Rych\OATH-OTP
 *
 * (c) Ryan Chouinard <rchouinard@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

error_reporting(E_ALL);

if (function_exists("date_default_timezone_set") && function_exists("date_default_timezone_get")) {
    date_default_timezone_set(@date_default_timezone_get());
}

require __DIR__ . "/../vendor/autoload.php";
