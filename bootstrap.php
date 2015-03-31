<?php
/**
 * This file contains the bootstrap code for the pasteshare application
 * @license MIT
 */

require(__dir__ . "/vendor/autoload.php");

/**
 * Spin up the pastecode application
 */
$app = new \pasteshare\Application();

/**
 * Include the dependency container and push it into the application instance
 */
require(__dir__ . "/resources/dependencyContainer.php");
$app["deps"] = $deps;

/**
 * Set the default timezone so we don't get warnings about it, regardless of
 * what is contained in the php.ini file
 */
date_default_timezone_set($app["deps"]["siteConfig"]->application->timezone);

/**
 * Set up some basic options for the application
 */
$app->register(new Silex\Provider\ServiceControllerServiceProvider());
$app["options"] = [
    "output" => $app["deps"]["siteConfig"]->frontend->output,
    "layout" => $app["deps"]["siteConfig"]->frontend->layout,
    "cacheDir" => $app["deps"]["siteConfig"]->frontend->cacheDir,
    "mode" => $app["deps"]["siteConfig"]->application->mode,
    "debug" => $app["deps"]["siteConfig"]->application->debug,
];

/**
 * Instantiate and configure the twig service provider
 */
$app->register(new Silex\Provider\TwigServiceProvider(), [
    "twig.path" => __dir__ . "/views/",
    "twig.options" => [
        "mode" => $app["options"]["mode"],
        "debug" => $app["options"]["debug"],
        "cache" => $app["options"]["cacheDir"],
    ]
]);

/**
 * Instantiate and register the Silex URL Generator
 */
$app->register(new Silex\Provider\UrlGeneratorServiceProvider());

/**
 * Use Whoops if we're in debug mode
 */
if ($app["options"]["debug"]) {
    $app->register(new Whoops\Provider\Silex\WhoopsServiceProvider());
}

/**
 * Finally just return the application
 */
return $app;
