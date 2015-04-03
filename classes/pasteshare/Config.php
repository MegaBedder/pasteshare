<?php
/**
 * This file contains only the Config class.
 * @license MIT
 */
namespace pasteshare;

/**
 * Generic config class.
 *
 * This class will load a json file and store the contents of that file at a
 * global scope in a variable called settings. It has a magic get function
 * which allows variables to be read out of the stored information.
 *
 * @author Anthony Vitacco <anthony@littlegno.me>
 */
class Config
{
    /** This is where the settings are stored. */
    private $settings;
    
    /**
     * Instantation function
     * This function will load the json file into memory specified by the first
     * parameter
     *
     * @param string $file The path to the config file. May be relative or full
     * @param boolean $asArray Whether to prefer an array data structure or an object (optional, default: false)
     * @throws RuntimeException When the specified file doesn't exist
     * @throws RuntimeException When the specified file can't be read
     * @throws RuntimeException When the specified file contains invalid json
     */
    public function __construct($file, $asArray = false)
    {
        // File doesn't exist
        if (!file_exists($file)) {
            throw new \RuntimeException("Specified config file does not exist: {$file}", 404);
        }
        
        // File can't be read
        if (!is_readable($file)) {
            throw new \RuntimeException("Specified config file cannot be read: {$file}", 403);
        }
        
        // Read the file and throw the entires into memory
        $this->settings = json_decode(file_get_contents($file), $asArray);
        if ($this->settings === null) {
            throw new \RuntimeException("The specified config file appears to contain invalid json: {$file}", 402);
        }
    }
    
    /**
     * Magic get function
     * This function will return requested information from the settings var
     *
     * @param string $what The requested information
     * @return mixed Returns the requested config information or false
     * @example $config->something
     * @example $config->some["array"]
     * @example $config->some->object
     * @example $config->something->far["more"]->complex
     */
    public function __get($what)
    {
        if (!empty($this->settings->$what)) {
            return $this->settings->$what;
        } else {
            return false;
        }
    }
    
    /**
     * This function allows for just getting the whole config variable as one
     * big variable return. Mostly used for getting database configs as an
     * array.
     *
     * @return mixed The whole config as one return
     */
    public function getAll()
    {
        return $this->settings;
    }
}
