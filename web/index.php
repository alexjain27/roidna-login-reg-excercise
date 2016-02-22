<?php

require('../vendor/autoload.php');
Stormpath\Client::$apiKeyFileLocation = '/Users/alex/Projects/php-getting-started/.stormpath/apiKey.properties';
$client = Stormpath\Client::getInstance();

$app = new Silex\Application();
$app['debug'] = true;

// Register the monolog logging service
$app->register(new Silex\Provider\MonologServiceProvider(), array(
  'monolog.logfile' => 'php://stderr',
));

// Register view rendering
$app->register(new Silex\Provider\TwigServiceProvider(), array(
    'twig.path' => __DIR__.'/views',
));

// Our web handlers

$app->get('/', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('index.twig');
});

$app->get('/register', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('register.twig');
});

$app->run();
