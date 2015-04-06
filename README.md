# pasteshare
[![SensioLabsInsight](https://insight.sensiolabs.com/projects/15563174-c9f0-43b4-8d36-282293f30055/mini.png)](https://insight.sensiolabs.com/projects/15563174-c9f0-43b4-8d36-282293f30055)

A PHP/Javascript pastebin alternative with full client-side AES-256 encryption support.

# Demo
A working demo is available at [http://pasteshare.avitac.co](http://pasteshare.avitac.co)

# Requirements
* PHP 5.4+
* MongoDB Server
* Bower
* NPM (to build the encryption module package)

# Installation
1. Check out source code
2. Run composer install
3. Run bower install
4. cd into `bower_components/forge`
  1. run `npm install`
  2. run `npm run minify`
5. Change settings in `configs/site.json` to be appropriate for your site/circumstances
