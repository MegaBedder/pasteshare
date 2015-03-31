<?php
/**
 * This file is the main entry point for the pasteshare application
 * @license MIT
 */

/**
 * Bootstrap this application
 */
$app = include(__dir__ . "/../bootstrap.php");

/**
 * Load resources, routes, and extensions for this application
 */
$paths = [
    __dir__ . "/../resources/*.php",
    __dir__ . "/../routing/*.php",
    __dir__ . "/../views/extensions/*.php"
];
foreach ($paths as $path) {
    foreach (glob($path) as $file) {
        require($file);
    }
}

/**
 * Run the application
 */
$app->run();
