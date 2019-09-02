<?php
/**
 * run in this directory:
 * php -S localhost:3000 
 */
require_once __DIR__ . '/../lib/router.php';
require_once __DIR__ . '/../lib/template.php';
require_once __DIR__ . '/../controller.php';


// bind the handlers to a regex route
$routes = [
    '/' => new Index(),
    '/about' => new AboutController(),
    '/blog(?:/([\w\-\_]+)?)?/?' => new BlogController(),
    '/sign-up' => new Signup(),
    '/resources' => new Resources(),
    '/resource/(\d+)' => new ResourceView(),
];

$t = new Template(__DIR__ .'/../templates');
$r = new Router($routes, $t, new FourOhFourHandler(), new FiveOhOhHandler());
$r->run();
