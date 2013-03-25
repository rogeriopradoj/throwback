<?php
/**
 * Copyright 2013 Eric D. Hough (http://ehough.com)
 *
 * This file is part of throwback (https://github.com/ehough/throwback)
 *
 * This Source Code Form is subject to the terms of the Mozilla Public
 * License, v. 2.0. If a copy of the MPL was not distributed with this
 * file, You can obtain one at http://mozilla.org/MPL/2.0/.
 */

function simulate_composer()
{

}

echo 'PHP ' . PHP_VERSION . ' is in use.';

if (version_compare(PHP_VERSION, '5.3.0') < 0) {

    if (is_file('src/test/php/throwback/composer_available_command.php')) {

        echo 'Now running src/test/php/throwback/composer_available_command.php';

        require 'src/test/php/throwback/composer_available_command.php';

    } else {

        echo 'src/test/php/throwback/composer_available_command.php does not exist. Running composer install --dev instead.';

        system('composer install --dev');
    }

} else {

    echo 'Now running simulated composer installation';

    simulate_composer();
}