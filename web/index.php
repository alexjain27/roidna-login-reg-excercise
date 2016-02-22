<?php
use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
require('../vendor/autoload.php');

Stormpath\Client::$apiKeyFileLocation = '../.stormpath/apiKey.properties';
$client = Stormpath\Client::getInstance();
$application = Stormpath\Resource\Application::get('https://api.stormpath.com/v1/applications/10f7iFRfH4Pzo74z7MF1LF');

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
$app->post('/api-register', function(Request $request) use($app) {
  $app['monolog']->addDebug('logging output.');
  //return $request->get('givenName');
  $application = Stormpath\Resource\Application::get('https://api.stormpath.com/v1/applications/10f7iFRfH4Pzo74z7MF1LF');
  $account = \Stormpath\Resource\Account::instantiate([
  'givenName' => $request->get('givenName'),
  'surname' => $request->get('surname'),
  'username' => $request->get('username'),
  'email' => $request->get('email'),
  'password' => $request->get('password')
	]);

  $account = $application->createAccount($account);
  return $app['twig']->render('register.twig');
});
$app->get('/login', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('login.twig',array('error_message' => ''));
});
$app->post('/api-login', function(Request $request) use($app) {
  $app['monolog']->addDebug('logging output.');
  //return $request->get('givenName');
  $application = Stormpath\Resource\Application::get('https://api.stormpath.com/v1/applications/10f7iFRfH4Pzo74z7MF1LF');
	try {
	  $result = $application->authenticate($request->get('username'), $request->get('password'));
	  $account = $result->account;
	} catch (\Stormpath\Resource\ResourceError $e) {
	  // Login attempt failed.
	  return $app['twig']->render('login.twig',array('error_message' => 'login failed'));
	}
  	return $app['twig']->render('authenticated.twig');
});

$app->get('/forgot_password', function() use($app) {
  $app['monolog']->addDebug('logging output.');
  return $app['twig']->render('forgot_password.twig');
});
$app->post('/api-forgot_password', function(Request $request) use($app) {
  $app['monolog']->addDebug('logging output.');
  //return $request->get('givenName');
  $application = Stormpath\Resource\Application::get('https://api.stormpath.com/v1/applications/10f7iFRfH4Pzo74z7MF1LF');
	$result = $application->sendPasswordResetEmail($request->get('email'), [], true);

	// The token is the last part of the HREF.
	$token = explode('/', $result->href);
	$token = end($token);

	// The account can be retrieved by using the account href on the result.
	$account = $result->account;
  return $app['twig']->render('authenticated.twig');
});
$app->run();
