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
	$map->get('a.profesor.EditForm', '/soe/dashboard/profesor/e/{email}', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getProfesorEditForm',
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
	$map->post('a.profesor.delete', '/soe/dashboard/profesor/del', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'deleteProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.edit.getMatfromSec', '/soe/dashboard/profesor/e/m', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'getMatformSecEdit',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.edit.generalInformation', '/soe/dashboard/profesor/edit', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'editProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.edit.removeClase', '/soe/dashboard/profesor/edit/r', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'removeClasefromProfesor',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.profesor.edit.addClase', '/soe/dashboard/profesor/edit/aM', [
		'controller' => 'App\Controllers\AdminProfesorController',
		'action' => 'addClasetoProfesorinEdit',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	// ESTUDIANTE
	$map->get('a.alumno.Dash', '/soe/dashboard/alumno', [
		'controller' => 'App\Controllers\AdminDashController',
		'action' => 'getAdminAlumnoDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	

	
	$map->get('a.alumno.AddForm', '/soe/dashboard/alumno/a', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getAlumnoAddForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->get('a.alumno.List', '/soe/dashboard/alumno/l', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getAlumnoList',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->get('a.alumno.DeleteConfirmation', '/soe/dashboard/alumno/d/{email}', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getAlumnoDeleteConfirmation',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->get('a.alumno.Detail', '/soe/dashboard/alumno/detail/{email}', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getAlumnoDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	
	$map->get('a.alumno.EditForm', '/soe/dashboard/alumno/edit/{email}', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getAlumnoEditForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);	


	$map->post('a.alumno.Add', '/soe/dashboard/alumno/add', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'addAlumno',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Add.getMateriafSecuencia', '/soe/dashboard/alumno/add/sec', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getMateriasfromSecuencia',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Add.claseToAlumno', '/soe/dashboard/alumno/add/sec/c', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'addClasetoAlumno',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Add.cancel', '/soe/dashboard/alumno/add/cancel', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'addAlumnoCanceled',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Delete', '/soe/dashboard/alumno/del', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'deleteAlumno',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Edit.getMatformSec', '/soe/dashboard/alumno/edit/m', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'getMatfromSecEditForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Edit', '/soe/dashboard/alumno/edit/e', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'editAlumno',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Edit.RemoveClass', '/soe/dashboard/alumno/edit/r', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'removeClaseFromAlumno',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);
	$map->post('a.alumno.Edit.AddClass', '/soe/dashboard/alumno/edit/a', [
		'controller' => 'App\Controllers\AdminAlumnoController',
		'action' => 'addClasetoAlumnoEdit',
		'auth' => true, 
		'userType' => getenv('USER_TYPE1')
	]);


	
	// TEACHER!!!!!!!!!!!!!!
	$map->get('teacherDashboard', '/soe/profesor', [
		'controller' => 'App\Controllers\TeacherDashController',
		'action' => 'getTeacherDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);

	$map->get('t.getClaseDetail', '/soe/profesor/{idClase}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getClaseDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.tarea.getForm', '/soe/profesor/tarea/{idClase}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getNuevaTareaForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.tarea.Detail', '/soe/profesor/tarea/{idClase}/d/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getTareaDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.tarea.Delete', '/soe/profesor/tarea/{idClase}/del/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'deleteTarea',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.tarea.Edit.Form', '/soe/profesor/tarea/{idClase}/edit/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'editTareaForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.anuncio.getForm', '/soe/profesor/anuncio/{idClase}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getNuevoAnuncioForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.anuncio.Detail', '/soe/profesor/anuncio/{idClase}/d/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getAnucioDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.anuncio.Edit.Form', '/soe/profesor/anuncio/{idClase}/edit/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'editAnuncioForm',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.studentsList', '/soe/profesor/list/{idClase}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getStudentsList',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->get('t.tarea.entregasList', '/soe/profesor/tarea/{idClase}/list/{idTarea}', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'getEntregasList',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	


	$map->post('t.tarea.add', '/soe/profesor/tarea/n', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'addTarea',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->post('t.tarea.Edit', '/soe/profesor/tarea/e', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'editTarea',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->post('t.anuncio.add', '/soe/profesor/anuncio/n', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'addAnuncio',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);
	$map->post('t.anuncio.Edit', '/soe/profesor/anuncio/e', [
		'controller' => 'App\Controllers\TeacherClassController',
		'action' => 'editAnuncio',
		'auth' => true, 
		'userType' => getenv('USER_TYPE3')
	]);


	// STUDENT!!!!!!!!!!!!!!	
	$map->get('studentDashboard', '/soe/alumno', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'getStudentDashboard',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);

	$map->get('a.getClaseDetail', '/soe/alumno/{idClase}', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'getClaseDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);
	$map->get('a.tarea.Detail', '/soe/alumno/{idClase}/d/{idTarea}', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'getTareaDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);
	$map->get('a.anuncio.Detail', '/soe/alumno/{idClase}/a/{idTarea}', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'getAnuncioDetail',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);
	$map->get('a.calendario', '/soe/alumno/calendario/{idClase}', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'calendario',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);


	$map->post('a.tarea.upload', '/soe/alumno/tarea', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'uploadTarea',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);
	$map->post('a.extra.add', '/soe/alumno/actividadExtra', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'addActividadExtra',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
	]);
	$map->post('a.extra.del', '/soe/alumno/actividadExtra/del', [
		'controller' => 'App\Controllers\StudentDashController',
		'action' => 'deleteActividadExtra',
		'auth' => true, 
		'userType' => getenv('USER_TYPE2')
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