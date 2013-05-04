.. _introduction.installation:

************
Installation
************

Installation of the library is done through `Composer`_. Composer is a tool for
dependency management in PHP. It allows you to declare the dependent libraries
your project needs and it will install them in your project for you.

* Install Composer in your project root

.. code-block:: bash

    curl -sS https://getcomposer.org/installer | php

* Create a composer.json in your project root with the following content:

.. code-block:: json

    {
      "require": {
        "rych/otp": "1.0.*@dev"
      }
    }

* Run the Composer installer

.. code-block:: bash

    php composer.phar install

Composer will automatically download the library into the vendor/ directory.
Your application just needs to load Composer's autoloader and begin using the
library.

.. code-block:: php

    <?php
    require 'vendor/autoload.php';

    $otp = new \Rych\OTP\HOTP('secret');

.. _`Composer`: http://getcomposer.org/
