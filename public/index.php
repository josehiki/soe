<?php
	ini_set('display_errors', 1);
	ini_set('display_starup_error', 1);
	error_reporting(E_ALL);


	require_once '../vendor/autoload.php';


	use Aura\Router\RouterContainer;
  	use Laminas\Diactoros\Response\RedirectResponse;
	use Illuminate\Database\Capsule\Manager as Capsule;

	
	session_start();

	
	$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
	$dotenv->load();

	// CONEXION BASE DE DATOS
	$capsule = new Capsule;
	$capsule->addConnection([
	    'driver'    => 'mysql',
	    'host'      => getenv('DB_HOST'),
	    'database'  => getenv('DB_NAME'),
	    'username'  => getenv('DB_USER'),
	    'password'  => getenv('DB_PASS'),
	    'charset'   => 'utf8',
	    'collation' => 'utf8_unicode_ci',
	    'prefix'    => '',
	]);

	$capsule->setAsGlobal();

	$capsule->bootEloquent();





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

	//ROUTES MAP
	// LOGIN & LOGOUT  
	$map->get('Login', '/soe/', [
		'controller' => 'App\Controllers\LoginController',
		'action' => 'getLogin'
	]);
	$map->get('Logout', '/soe/logout', [
		'controller' => 'App\Controllers\LoginController',
		'action' => 'logout',
		'auth' => true
	]);
	$map->post('PostLogin', '/soe/postLogin', [
		'controller' => 'App\Controllers\LoginController',
		'action' => 'postLogin'
	]);


	// ADMIN!!!!!!!!!!!!!!

	// DASHBOARD
	$map->get('adminDashboard', '/soe/dashboard', [
		'controller' => 'App\Controllers\AdminDashController',
		'action' => 'getAdminDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	//	MATERIA
	$map->get('a.materia.getDashboard', '/soe/dashboard/materia', [
		'controller' => 'App\Controllers\AdminDashController',
		'action' => 'getAdminMateriaDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	$map->get('a.materia.AddForm', '/soe/dashboard/materia/add', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminAddMateriaForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.materia.List', '/soe/dashboard/materia/list', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminMaterias',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.materia.DeleteConfirmation', '/soe/dashboard/materia/list/{id}', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminMateriasDeleteConfirmation',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.materia.Delete', '/soe/dashboard/materia/del/{id}', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminMateriasDelete',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);

	$map->get('a.materia.EditForm', '/soe/dashboard/materia/edit/{id}', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminMateriasEditForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);

	$map->post('a.materia.Add', '/soe/dashboard/materia/add', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'adminAddMateria',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.materia.Edit', '/soe/dashboard/materia/edit/', [
		'controller' => 'App\Controllers\AdminMateriaController',
		'action' => 'getAdminMateriasEdit',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	// SECUENCIA
	$map->get('a.secuencia.Dash', '/soe/dashboard/secuencia', [
		'controller' => 'App\Controllers\AdminDashController',
		'action' => 'getAdminSecuenciaDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);

	$map->get('a.secuencia.AddForm', '/soe/dashboard/secuencia/add', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getAdminAddSecuenciaForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.secuencia.List', '/soe/dashboard/secuencia/list', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getAdminSecuenciaList',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.Secuencia.Detalle', '/soe/dashboard/secuencia/list/d/{clave}', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getSecuenciaDetalle',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.secuencia.DeleteConfirmation', '/soe/dashboard/secuencia/list/del/{clave}', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'deleteSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.secuencia.EditForm', '/soe/dashboard/secuencia/list/e/{clave}', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getEditableSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	$map->post('a.secuencia.Add', '/soe/dashboard/secuencia/add/sec', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getAdminAddSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.AddContinue', '/soe/dashboard/secuencia/add/sec2', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'addMateriatoSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.AddCanceled', '/soe/dashboard/secuencia/add/sec2/cancel', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'addSecuenciaCancel',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.Delete', '/soe/dashboard/secuencia/list/del', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'getDeleteSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.DeleteMateriafromSecuencia', '/soe/dashboard/secuencia/list/e/d', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'deletedMateriafromSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.Rename', '/soe/dashboard/secuencia/list/e/r', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'renameSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.secuencia.addMateriatoSecuencia', '/soe/dashboard/secuencia/list/e/a', [
		'controller' => 'App\Controllers\AdminSecuenciaController',
		'action' => 'removeMateriaFromEditedSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	// PROFESOR
	$map->get('a.profesor.Dash', '/soe/dashboard/profesor', [
		'controller' => 'App\Controllers\AdminDashController',
		'action' => 'getAdminProfesorDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);

	$map->get('a.profesor.AddForm', '/soe/dashboard/profesor/a', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getProfesorAddForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->get('a.profesor.List', '/soe/dashboard/profesor/l', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getProfesorList',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.profesor.DeleteConfirmation', '/soe/dashboard/profesor/d/{email}', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getProfesorDeleteConfirmation',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->get('a.profesor.Detail', '/soe/dashboard/profesor/detail/{email}', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getProfesorDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);

	$map->post('a.profesor.Add', '/soe/dashboard/profesor/add', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'addProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->post('a.profesor.Add.getMateriasfromSecuencia', '/soe/dashboard/profesor/add/sec', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getMateriasfromSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->post('a.profesor.Add.materiaSecuencia', '/soe/dashboard/profesor/add/mS', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'addMateriaSecuenciatoProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.Add.canceled', '/soe/dashboard/profesor/add/canceled', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'addProfesorCanceled',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.delete', '/soe/dashboard/profesor/add/del', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'deleteProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);




	// STUDENT!!!!!!!!!!!!!!	
	$map->get('studentDashboard', '/soe/student', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'getStudentDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);

	// TEACHER!!!!!!!!!!!!!!
	$map->get('teacherDashboard', '/soe/teacher', [
		'controller' => 'App\Controllers\TeacherDashController',
		'action' => 'getTeacherDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);



	

	$matcher = $routerContainer->getMatcher();	
	$route = $matcher->match($request);

	foreach ($route->attributes as $key => $val) {
	    $request = $request->withAttribute($key, $val);
	}

	if(!$route)
	{
		echo 'Ruta no encontrada';
	}else{
		$handlerData = $route->handler;

		$controllerName = $handlerData['controller'];
		$actionName = $handlerData['action'];
		$needsAuth = $handlerData['auth'] ?? false;
		$needsUserType = $handlerData['userType'] ?? false;

		$sessionUserId = $_SESSION['userId'] ?? null;
		$sessionUserType = $_SESSION['userType'] ?? null;

		if($needsAuth && !$sessionUserId)
		{
			$response = new RedirectResponse('/soe');  
		}else
		{
			if(!$needsUserType){
				$controller = new $controllerName;
				$response = $controller->$actionName($request);	
			}else
			{
				if ($needsUserType == $sessionUserType) 
				{
					$controller = new $controllerName;
					$response = $controller->$actionName($request);
				}else
				{
					unset($_SESSION['userId']);
					$response = new RedirectResponse('/soe');
				}	
			}	
			
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