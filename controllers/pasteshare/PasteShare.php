<?php
/**
 * This file is the main application controller for pasteshare
 * @license MIT
 */
namespace pasteshare;

/**
 * Main application controller for pasteshare
 *
 * @author Anthony Vitacco <anthony@littlegno.me>
 */
class PasteShare
{
    /** @var object An instance of pasteshare\Application */
    private $app;
    
    /**
     * This function stores the application instance globally
     *
     * @param object $app An instance of pasteshare\Application
     */
    public function __construct(Application $app)
    {
        $this->app = $app;
    }
    
    /**
     * Main index page for pasteshare
     *
     * @return object An instance of Symfony's response class
     */
    public function newPastePage()
    {
        $options = $this->app["options"];
        $options["languages"] = $this->getLanguagesFromCodeMirror();
        return $this->app->render("pages/main.twig", $options);
    }
    
    /**
     * Save function for pastes
     *
     * @param object $request An instance of Symfony's request class
     * @return object An instance of Symfony's response class
     */
    public function savePaste(\Symfony\Component\HttpFoundation\Request $request)
    {
        try {
            $dm = $this->app["deps"]["mongoDm"];
            $timezone = new \DateTimeZone($this->app["deps"]["siteConfig"]->application->timezone);
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
            
            $pasteUrl = $this->app->path("view", ["uniqid" => $paste->uniqid]);
            
            return $this->app->json(["status" => 200, "redirect" => $pasteUrl]);
        } catch (\RuntimeException $e) {
            return $this->app->json(["status" => 403, "error" => "Database unavailable"]);
        }
    }
    
    /**
     * View function for pastes
     *
     * @param object $request An instance of Symfony's request class
     * @return object An instance of Symfony's response class
     */
    public function viewPaste(\Symfony\Component\HttpFoundation\Request $request)
    {
        try {
            $dm = $this->app["deps"]["mongoDm"];
            $paste = $dm->getRepository("\pasteshare\Paste")->findOneBy(["uniqid" => $request->get("uniqid")]);
        } catch (\RuntimeException $e) {
        }
        
        $options = $this->app["options"];
        $options["paste"] = $paste->toArray();
        return $this->app->render("pages/main.twig", $options);
    }
    
    /**
     *
     */
    private function getLanguagesFromCodeMirror()
    {
        $meta = file_get_contents(
            $this->app["deps"]["siteConfig"]->paths->app_root . "/bower_components/codemirror/mode/meta.js"
        );
        
        preg_match("/CodeMirror.modeInfo = (\[.*?\]);/s", $meta, $json);
        $json = $json[1];
        
        preg_match_all("/{.*}/", $json, $languages);
        
        $return = [];
        foreach ($languages[0] as $language) {
            preg_match("/name:\s\"(.+?)\"/", $language, $name);
            preg_match("/mode:\s\"(.+?)\"/", $language, $mode);
            $return[] = [
                "name" => $name[1],
                "mode" => $mode[1]
            ];
        }
        return $return;
    }
}
