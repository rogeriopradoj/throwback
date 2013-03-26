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

function _throwbackSystem($command)
{
    $result = system($command);

    if ($result === false) {

        echo "$command failed\n";
        exit(1);
    }
}

function clone_git_repos()
{
    global $deps;

    if (! is_dir('vendor')) {

        $result = mkdir('vendor', 0755);

        if ($result === false) {

            echo "Could not create vendor directory\n";

            exit(1);
        }
    };

    _throwbackSystem('echo "Host github.com\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config');

    _throwbackSystem('git clone git://github.com/ehough/pulsar.git vendor/ehough/pulsar');

    foreach ($deps as $dependency) {

        $home = 'vendor/' . $dependency[0];

        $result = mkdir($home, 0755, true);

        if ($result === false) {

            echo "Could not create $home\n";

            exit(1);
        }

        _throwbackSystem('git clone ' . $dependency[1] . " $home");
    }
}

function build_autoload()
{
    global $deps, $selfInfo;

    $content = <<<EOT
<?php

require 'vendor/ehough/pulsar/src/main/php/ehough/pulsar/UniversalClassLoader.php';

class throwbackLoader extends ehough_pulsar_UniversalClassLoader
{
    public function add(\$prefix, \$dir)
    {
        \$this->registerPrefixFallback(\$dir);
        \$this->registerNamespaceFallback(\$dir);
    }
}

\$loader = new throwbackLoader();

EOT;

    foreach ($deps as $dependency) {

        $content .= '$loader->registerPrefixFallback(\'' . getcwd() . '/vendor/' . $dependency[0] . '/' . $dependency[2] . "');\n";
        $content .= '$loader->registerNamespaceFallback(\'' . getcwd() . '/vendor/' . $dependency[0] . '/' . $dependency[2] . "');\n";
    }

    $content .= "\$loader->registerPrefix('" . $selfInfo[0] . "', '" . $selfInfo[1] . "');\n";

    $content .= '$loader->register();';

    $content .= 'return $loader;';

    file_put_contents(getcwd() . '/vendor/autoload.php', $content);
}

function simulate_composer()
{
    if (is_file('src/test/php/throwback/simulated_composer.php')) {

        echo "Including src/test/php/throwback/simulated_composer.php\n";

        require 'src/test/php/throwback/simulated_composer.php';

        clone_git_repos();
        build_autoload();

    } else {

        echo "src/test/php/throwback/simulated_composer.php not found\n";
    }
}

echo 'PHP ' . PHP_VERSION . " is in use.\n";

if (version_compare(PHP_VERSION, '5.3.0') >= 0) {

    if (is_file('src/test/php/throwback/composer_available_command.php')) {

        echo "Now running src/test/php/throwback/composer_available_command.php\n";

        require 'src/test/php/throwback/composer_available_command.php';

    } else {

        echo "src/test/php/throwback/composer_available_command.php does not exist. Running composer install --dev instead.\n";

        _throwbackSystem('composer install --dev');
    }

} else {

    echo "Now running simulated composer installation\n";

    simulate_composer();
}

require 'src/test/php/throwback/final_script.php';