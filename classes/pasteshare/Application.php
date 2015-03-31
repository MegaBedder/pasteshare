<?php
/**
 * This class extends the base Silex Application class to include some useful
 * traits.
 *
 * @author Anthony Vitacco <anthony@littlegno.me>
 * @license MIT
 */
namespace pasteshare;

class Application extends \Silex\Application
{
    use \Silex\Application\TwigTrait;
    use \Silex\Application\UrlGeneratorTrait;
    use \Silex\Application\SwiftmailerTrait;
    use \Silex\Application\MonologTrait;
}
