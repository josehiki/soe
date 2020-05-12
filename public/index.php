<?php
	ini_set('display_errors', 1);
	ini_set('display_starup_error', 1);
	error_reporting(E_ALL);


	require_once '../vendor/autoload.php';


	session_start();

	
	use Aura\Router\RouterContainer;


	$request = Laminas\Diactoros\ServerRequestFactory::fromGlobals(
	    $_SERVER,
	    $_GET,
	    $_POST,
	    $_COOKIE,
	    $_FILES
	);

	// ROUTER!!!!
	$routerContainer = new RouterContainer();
	$map = $routerContainer->getMap();

	// Login route
	$map->get('Login', '/soe/', [
		'controller' => 'App\Controllers\LoginController',
		'action' => 'getLogin'
	]);

	$matcher = $routerContainer->getMatcher();	
	$route = $matcher->match($request);

	if(!$route)
	{
		echo 'Ruta no encontrada';
	}else{
		$handlerData = $route->handler;
		$controllerName = $handlerData['controller'];
		$actionName = $handlerData['action'];
		$needsAuth = $handlerData['auth'] ?? false;

		$sessionUserId = $_SESSION['userId'] ?? null;
		if($needsAuth && !$sessionUserId)
		{
			$response = new RedirectResponse('/soe');  
		}else
		{
			$controller = new $controllerName;
			$response = $controller->$actionName($request);


		}
		foreach ($response->getHeaders() as $name => $values) 
		{
			foreach ($values as $value) {
				header(sprintf('%s: %s', $name, $value), false);
			}
		}

		http_response_code($response->getStatusCode());
		echo $response->getBody();
	}