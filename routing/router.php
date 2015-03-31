<?php
/**
 * This file contains the route definitions for the pasteshare application
 */
$router = $app["controllers_factory"];

/**
 * GET index
 */
$router->get("/",
    function (Silex\Application $app)
    {
        return $app->render("pages/main.twig", $app["options"]);
    }
);

/**
 * GET a paste by uniqid
 * @param uniqid Required to be 13 characters in length
 */
$router->get("/{uniqid}",
    function (Silex\Application $app, $uniqid)
    {
        try {
            $dm = $app["deps"]["mongoDm"];
            $paste = $dm->getRepository("\pasteshare\Paste")->findOneBy(["uniqid" => $uniqid]);
        } catch (\RuntimeException $e) {
            
        }
        
        $options = $app["options"];
        $options["paste"] = $paste->toArray();
        return $app->render("pages/main.twig", $options);
    }
)
->bind("view")
->assert("uniqid", "\w{13}");

/**
 * POST save
 */
$router->post("/save",
    function (Silex\Application $app, Symfony\Component\HttpFoundation\Request $request)
    {   
        try {
            $dm = $app["deps"]["mongoDm"];
            $timezone = new \DateTimeZone($app["deps"]["siteConfig"]->application->timezone);
            $paste = new \pasteshare\Paste();
            $paste->visible = false;
            
            if ($request->get("visible") == "true") {
                $paste->visible = true;
            }
            
            if ($request->get("encrypted")) {
                $paste->encrypted = true;
                $paste->iv = $request->get("iv");
            }
            
            $paste->language = $request->get("language");
            $paste->contents = $request->get("contents");
            $paste->created = new \DateTime("now", $timezone);
            $paste->expires = new \DateTime($request->get("expires"), $timezone);
            
            $dm->persist($paste);
            $dm->flush();
            
            $pasteUrl = $app->path("view", ["uniqid" => $paste->uniqid]);
            
            return $app->json(["status" => 200, "redirect" => $pasteUrl]);
        } catch (\RuntimeException $e) {
            return $app->json(["status" => 403, "error" => "Database unavailable"]);
        }
    }
);

/**
 * Finally mount the router at the correct url
 */
$app->mount("/", $router);
