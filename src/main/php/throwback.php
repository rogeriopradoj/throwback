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

class __throwback
{
    public static $config;

    public static function run()
    {
        try {

            self::_run();

        } catch (Exception $e) {

            self::_log('throwback failed: ' . $e->getMessage());
            exit(1);
        }
    }

    private static function _run()
    {
        self::_log('PHP ' . PHP_VERSION . ' is in use.');

        if (! isset(self::$config)) {

            throw new Exception('Missing throwback config');
        }

        self::_installComposerDeps();
    }

    private static function _installComposerDeps()
    {
        if (version_compare(PHP_VERSION, '5.3.0') >= 0) {

            if (isset(self::$config['PHP53-before_script'])) {

                self::_runCommand(self::$config['PHP53-before_script']);

            } else {

                self::_runCommand('composer install --dev');
            }

        } else {

            self::_performSimulatedComposerInstall();
        }
    }

    private static function _performSimulatedComposerInstall()
    {
        if (! isset(self::$config['dependencies'])) {

            return;
        }

        self::_clone_git_repos(self::$config['dependencies']);
        self::_build_autoload(self::$config['dependencies']);
    }

    private static function _clone_git_repos($deps)
    {
        self::_runCommand('echo "Host github.com\n\tStrictHostKeyChecking no\n" >> ~/.ssh/config');

        self::_runCommand('git clone git://github.com/ehough/pulsar.git vendor/ehough/pulsar');

        foreach ($deps as $dep) {

            $home = 'vendor/' . $dep[0];

            if (count($dep) > 3) {

                $home .= '/' . $dep[3];
            }

            self::_mkdir($home);

            self::_runCommand("git clone " . $dep[1] . " $home");
        }
    }

    private static function _build_autoload($deps)
    {
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

            $content .= '$loader->registerPrefixFallback(\'' . getcwd() . '/vendor/' . $dependency[0] . '/' . ( count($dependency) > 3 ? ( $dependency[3] . '/' ) : '' ) . $dependency[2] . "');\n";
            $content .= '$loader->registerNamespaceFallback(\'' . getcwd() . '/vendor/' . $dependency[0] . '/' . ( count($dependency) > 3 ? ( $dependency[3] . '/' ) : '' ) . $dependency[2] . "');\n";
        }

        $content .= "\$loader->registerPrefix('" . self::$config['name'] . "', '" . self::$config['autoload'] . "');\n";

        $content .= '$loader->register();';

        $content .= 'return $loader;';

        file_put_contents(getcwd() . '/vendor/autoload.php', $content);
    }

    private static function _log($message)
    {
        echo "$message\n";
    }

    private static function _runCommand($command)
    {
        $result = system($command);

        if ($result === false) {

            throw new RuntimeException("$command failed");
        }
    }

    private static function _mkdir($path)
    {
        $result = mkdir($path, 0755, true);

        if ($result === false) {

            throw new RuntimeException("Could not mkdir $path");
        }
    }
}

/** @noinspection PhpIncludeInspection */
require 'src/test/php/throwback.php';

__throwback::run();