<?php
/**
 * run in this directory:
 * php -S localhost:3000 
 */
require_once __DIR__ . '/../lib/router.php';
require_once __DIR__ . '/../lib/template.php';
require_once __DIR__ . '/../controller.php';
require_once __DIR__ . '/../controller/static_page.php';
require_once __DIR__ . '/../controller/hype_report.php';
require_once __DIR__ . '/../controller/resources.php';
require_once __DIR__ . '/../controller/blog.php';
require_once __DIR__ . '/../controller/signup.php';
require_once __DIR__ . '/../controller/errors.php';
require_once __DIR__ . '/../controller/contribute.php';


// bind the handlers to a regex route
$routes = [
    '/' => new Index(),
    '/about' => new AboutController(),
    '/code-jam' => new CodeJamController(),
    '/ai-learning-club' => new AILearningClubController(),
    '/blog(?:/([\w\-\_]+)?)?/?' => new BlogController(),
    '/sign-up' => new Signup(),
    '/contribute' => new Contribute(),
    '/resources' => new Resources(),
    '/resource/(\d+)' => new ResourceView(),
    '/hype-report' => new HypeReport(),
];

$t = new Template(__DIR__ .'/../templates');
$r = new Router($routes, $t, new FourOhFourHandler(), new FiveOhOhHandler());
$r->run();
