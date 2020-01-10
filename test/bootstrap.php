<?php

use SwoftTest\Testing\TestApplication;

if (file_exists(dirname(__DIR__) . '/vendor/autoload.php')) {
    require dirname(__DIR__) . '/vendor/autoload.php';
    // application's vendor
} elseif (file_exists(dirname(__DIR__, 3) . '/autoload.php')) {
    /** @var \Composer\Autoload\ClassLoader $loader */
    $loader = require dirname(__DIR__, 3) . '/autoload.php';

    // need load test psr4 config map
    $componentDir  = dirname(__DIR__);
    $componentJson = $componentDir . '/composer.json';
    $composerData  = json_decode(file_get_contents($componentJson), true);

    foreach ($composerData['autoload-dev']['psr-4'] as $prefix => $dir) {
        $loader->addPsr4($prefix, $componentDir . '/' . $dir);
    }
} else {
    exit('Please run "composer install" to install the dependencies' . PHP_EOL);
}

if (defined('RUN_TEST_APP') && !RUN_TEST_APP) {
    return;
}

$application = new TestApplication();
$application->setBeanFile(__DIR__ . '/testing/bean.php');
$application->run();
