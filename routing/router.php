<?php
/**
 * This file is the main router file for pastecode
 * @license MIT
 */

/**
 * Add the pasteshare controller to the controllers collection
 */
$app["controllers.PasteShare"] = $app->share(function () use ($app) {
    return new \pasteshare\PasteShare($app);
});

/**
 * This file contains the route definitions for the pasteshare application
 */
$router = $app["controllers_factory"];

/**
 * GET index
 */
$router->get("/", "controllers.PasteShare:newPastePage")
->bind("new");

/**
 * GET a paste by uniqid
 * @param uniqid Required to be 13 characters in length
 */
$router->get("/{uniqid}", "controllers.PasteShare:viewPaste")
->bind("view")
->assert("uniqid", "\w{13}");

/**
 * POST save
 */
$router->post("/save", "controllers.PasteShare:savePaste")
->bind("save");

/**
 * Finally mount the router at the correct url
 */
$app->mount("/", $router);
