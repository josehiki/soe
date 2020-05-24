<?php 
	namespace App\Controllers;

	use Respect\Validation\Validator as v;
	use App\Models\User;
	use App\Models\Secuencia;
	use App\Models\Subject;
	use App\Models\Rel_Sec_Sub;
	use App\Models\User_Rel;
	use Laminas\Diactoros\Response\RedirectResponse;
	/**
	 * Controlador para la gestion de profesores
	 */
	class AdminProfesorController extends BaseController
	{
		
		function getProfesorAddForm($request) //Imprime el formulario de nuevo profesor
		{
			return $this->renderHTML('adminProfesorAdd.twig', [
				'username' => $_SESSION['userName']
			]);
		}

		function getProfesorList($request) // Imprime la lista de profesores registrados
		{
			$listProfesores = User::where('userType', 'teacher')->get();
			return $this->renderHTML('adminProfesorList.twig', [
				'username' => $_SESSION['userName'],
				'listProfesores' => $listProfesores
			]);
		}

		function addProfesor($request) // Agrega la informacion general de un profesor
		{
			$postData = $request->getParsedBody();

			$validator = v::key('nombreProfesor', v::notEmpty()->stringType())
						->key('emailProfesor', v::notEmpty()->email())
						->key('contraProfesor', v::notEmpty()->length(6,null))
						->key('recontraProfesor', v::notEmpty()->length(6,null))
						->key('telefonoProfesor', v::length(10,10)->number()); //validador de los paramtros recibidos
			
			try{
				$validator->assert($postData); //validacion de postData
				
				if ($postData['contraProfesor'] == $postData['recontraProfesor']) {
					$auxUser = User::where('email', $postData['emailProfesor'])->first();
					if(!$auxUser){
						$newUser = new User ();
						$newUser->userType = 'teacher';
						$newUser->userName = $postData['nombreProfesor'];
						$newUser->email = $postData['emailProfesor'];
						$newUser->userPassword = password_hash($postData['contraProfesor'], PASSWORD_DEFAULT);
						$newUser->phone = $postData['telefonoProfesor'];
						$newUser->office = $postData['cubiculoProfesor'];
						if(isset($postData['academiaProfesor']))
						{
							$newUser->academy = $postData['academiaProfesor'];
						}	
						$newUser->save();
						$listSecuencias = Secuencia::all();
						return $this->renderHTML('adminProfesorAddHorario.twig', [
							'username' => $_SESSION['userName'],
							'actualProfesor' => $newUser, 
							'listSecuencias' => $listSecuencias
						]);
					}else{
						return $this->renderHTML('adminProfesorAdd.twig', [
							'username' => $_SESSION['userName'],
							'responseMessage' => 'El correo registrado ya existe'
						]);
					}
				}else
				{
					return $this->renderHTML('adminProfesorAdd.twig', [
						'username' => $_SESSION['userName'],
						'responseMessage' => 'Las contraseñas deben coincidir'
					]);
				}

			}catch(\Exception $e)
			{
				return $this->renderHTML('adminProfesorAdd.twig', [
					'username' => $_SESSION['userName'],
					'responseMessage' => 'Algo salio mal, por favor revisa tu información '
				]);
			}

		} //addProfesor

		function getMateriasfromSecuencia($request)// imprime las materias segun la secuencia seleccionada
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first(); //obtener el usuario con el que se esta trabajando

			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //obtener la informacion de la secuencia solicitada

			if($dbSecuencia) //La secuencia existe			
			{ 
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				if(!$dbRel_Sec_Sub->isEmpty()) //La secuencia tiene materias
				{
					$listSubject = null;
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSubjectNames = Subject::find($listSubject);
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'listMaterias' => $listSubjectNames,
						'secuencia' => $postData['clave']
					]);							
				}else // la secuencia no tiene materias
				{
					$listSecuencias = Secuencia::all();
					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'secuencia' => $postData['clave'],
						'messageList' => 'Esta secuencia no tiene materias registradas'
					]);
				}
			}else // La secuencia no existe
			{
				$listSecuencias = Secuencia::all();
				$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
				return $this->renderHTML('adminProfesorAddHorario.twig', [
					'username' => $_SESSION['userName'],
					'actualProfesor' => $dbUser, 
					'listRelNames' => $listRelNames,
					'listSecuencias' => $listSecuencias,
					'responseMessage' => 'No existe esa secuencia'
				]);
			}
				
		} // getMateriasfromSecuencia

		function addMateriaSecuenciatoProfesor($request) // agrega la clase al profesor
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first(); //Informacion del profesor
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //informacion de la secuencia
			$dbSubject = Subject::where('subjectName', $postData['materia'])->first();
			

			if($dbSecuencia && $dbSubject) //si existen tanto la secuencia como la materia
			{
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)
				->where('idSubject', $dbSubject->idSubject)->first(); //busca a relacion de materia-secuencia
				
				$dbUser_Rel = User_Rel::where('rel_id', $dbRel_Sec_Sub->id)
				->first(); // busca la relacion 
				
				if (!$dbUser_Rel) //si clase no tiene asignado un profesor
				{
					$newUser_Rel = new User_Rel();
					$newUser_Rel->user_id = $dbUser->idUser;
					$newUser_Rel->rel_id = $dbRel_Sec_Sub->id;
					$newUser_Rel->nombreRel = $dbSecuencia->claveSecuencia.' - '.$dbSubject->subjectName;
					$newUser_Rel->save();

					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'responseMessage' => 'Clase agregada correctamente'
					]);
				}else //si nadie ha sido asignado a esa clase
				{
					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'responseMessage' => 'Ya existe un profesor asignado a esa clase'
					]);
				}
				
			}else // si no existe la materia o la secuencia
			{
				$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
				$listSecuencias = Secuencia::all();
				return $this->renderHTML('adminProfesorAddHorario.twig', [
					'username' => $_SESSION['userName'],
					'actualProfesor' => $dbUser, 
					'listSecuencias' => $listSecuencias,
					'listRelNames' => $listRelNames,
					'responseMessage' => 'Ha ocurrido un error'
				]);
			}
		} /* addMateriaSecuenciatoProfesor */

		function addProfesorCanceled($request) //cancela el alta de un profesor
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first();

			$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
			if(!$dbUser_Rel->isEmpty())
			{
				foreach ($dbUser_Rel as $rel) {
					$auxUser_Rel = User_Rel::find($rel->_id);
					$auxUser_Rel->delete();
				}
			}
			$dbUser->delete();
			return new RedirectResponse('/soe/dashboard/profesor/a');

		} //addProfesorCanceled

		function getProfesorDeleteConfirmation($request) // carga solicitud de confirmacion para eliminar un profesor
		{
			$postData = $request->getAttribute('email');

			$deleteProfesor = User::where('email', $postData)->first();
			$listProfesores = User::where('userType', 'teacher')->get();
			return $this->renderHTML('adminProfesorList.twig', [
				'username' => $_SESSION['userName'],
				'listProfesores' => $listProfesores,
				'deleteProfesor' => $deleteProfesor
			]);
		} //getProfesorDeleteConfirmation

		function deleteProfesor($request)
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first();

			if($dbUser) // existe el usuario
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				if(!$dbUser_Rel->isEmpty())
				{
					foreach ($dbUser_Rel as $rel) {
						$auxUser_Rel = User_Rel::find($rel->_id);
						$auxUser_Rel->delete();
					}
				}
				$dbUser->delete();
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'responseMessage' => 'Profesor eliminado con exito'
				]);
			}else // no existe el usuario
			{
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'responseMessage' => 'El profesor no existe'
				]);
			}
		} //deleteProfesor

		function getProfesorDetail($request)
		{
			$postData = $request->getAttribute('email');
			$dbUser = User::where('email', $postData)->first();
			
			if($dbUser) // existe el usuario
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
				if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
				{
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'detailProfesor' => $dbUser,
						'listClases' => $dbUser_Rel
					]);
				}else //no tiene
				{
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'detailProfesor' => $dbUser
					]);
				}
			}else //el usuario no existe
			{
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'responseMessage' => 'El profesor no existe'
				]);
			}			
		} //getProfesorDetail

		function getProfesorEditForm($request)
		{
			$postData = $request->getAttribute('email');
			$dbUser = User::where('email', $postData)->first();
			
			if($dbUser) // existe el usuario
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
				if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser,
						'listClases' => $dbUser_Rel, 
						'listSecuencias' => $listSecuencias
					]);
				}else //no tiene
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias
					]);
				}
			}else //el usuario no existe
			{
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'responseMessage' => 'El profesor no existe'
				]);
			}
		} //getProfesorEditForm

		function getMatformSecEdit($request)
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first(); //obtener el usuario con el que se esta trabajando

			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //obtener la informacion de la secuencia solicitada

			if($dbSecuencia) //La secuencia existe			
			{ 
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				if(!$dbRel_Sec_Sub->isEmpty()) //La secuencia tiene materias
				{
					$listSubject = null;
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					$listSubjectNames = Subject::find($listSubject);
					$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
					if(!$dbUser_Rel->isEmpty()) //si el profesor tiene clases
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser,
							'listClases' => $dbUser_Rel, 
							'listSecuencias' => $listSecuencias,
							'secuencia' => $postData['clave'],
							'listMaterias' => $listSubjectNames,
						]);
					}else //no tiene
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser, 
							'listSecuencias' => $listSecuencias,
							'secuencia' => $postData['clave'],
							'listMaterias' => $listSubjectNames,
						]);
					}
					
				}else // la secuencia no tiene materias
				{
					$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
					if(!$dbUser_Rel->isEmpty()) //si el profesor tiene clases
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser,
							'listClases' => $dbUser_Rel, 
							'listSecuencias' => $listSecuencias,
							'secuencia' => $postData['clave'],
							'messageList' => 'Esta secuencia no tiene materias registradas'
						]);
					}else //no tiene
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser, 
							'listSecuencias' => $listSecuencias,
							'secuencia' => $postData['clave'],
							'messageList' => 'Esta secuencia no tiene materias registradas'
						]);
					}
				}
			}else // La secuencia no existe
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
				if(!$dbUser_Rel->isEmpty()) //si el profesor tiene clases
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser,
						'listClases' => $dbUser_Rel, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'No existe esa secuencia'
					]);
				}else //no tiene
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'No existe esa secuencia'
					]);
				}
			}
			
		} // getMatformSecEdit

		function editProfesor($request)
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first();
			$validator = v::key('nombreProfesor', v::notEmpty()->stringType())
						->key('emailProfesor', v::notEmpty()->email())
						->key('telefonoProfesor', v::length(10,10)->number()); //validador de los paramtros recibidos
			try
			{
				$validator->assert($postData); //validacion de postData
				$auxUser = User::where('email', $postData['emailProfesor'])->first();
				
				if(!$auxUser || $dbUser->email == $postData['emailProfesor']){ //el email no esta registrado
					if($postData['contraProfesor'] != '') // ingresaron una nueva contraseña
					{
						if(v::length(6,null)->validate($postData['contraProfesor'])) //la contraseña tiene mas de seir caracteres
						{ 
							$newUser = User::find($dbUser->idUser);
							$newUser->userName = $postData['nombreProfesor'];
							$newUser->email = $postData['emailProfesor'];
							$newUser->userPassword = password_hash($postData['contraProfesor'], PASSWORD_DEFAULT);
							$newUser->phone = $postData['telefonoProfesor'];
							$newUser->office = $postData['cubiculoProfesor'];
							if(isset($postData['academiaProfesor']))
							{
								$newUser->academy = $postData['academiaProfesor'];
							}	
							$newUser->save();
							$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
							$dbUser = User::where('email',$newUser->email)->first();
							if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
							{
								$listSecuencias = Secuencia::all();
								$listProfesores = User::where('userType', 'teacher')->get();
								return $this->renderHTML('adminProfesorList.twig', [
									'username' => $_SESSION['userName'],
									'listProfesores' => $listProfesores,
									'editableProfesor' => $dbUser,
									'listClases' => $dbUser_Rel, 
									'listSecuencias' => $listSecuencias,
									'responseMessageEdit' => 'Profesor actualizado'
								]);
							}else //no tiene
							{
								$listSecuencias = Secuencia::all();
								$listProfesores = User::where('userType', 'teacher')->get();
								return $this->renderHTML('adminProfesorList.twig', [
									'username' => $_SESSION['userName'],
									'listProfesores' => $listProfesores,
									'editableProfesor' => $dbUser, 
									'listSecuencias' => $listSecuencias,
									'responseMessageEdit' => 'Profesor actualizado'
								]);
							}
						}else // la contraseña no cumple la validacion 
						{
							$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
							if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
							{
								$listSecuencias = Secuencia::all();
								$listProfesores = User::where('userType', 'teacher')->get();
								return $this->renderHTML('adminProfesorList.twig', [
									'username' => $_SESSION['userName'],
									'listProfesores' => $listProfesores,
									'editableProfesor' => $dbUser,
									'listClases' => $dbUser_Rel, 
									'listSecuencias' => $listSecuencias,
									'responseMessageEdit' => 'Formato incorrecto en la contraseña'
								]);
							}else //no tiene
							{
								$listSecuencias = Secuencia::all();
								$listProfesores = User::where('userType', 'teacher')->get();
								return $this->renderHTML('adminProfesorList.twig', [
									'username' => $_SESSION['userName'],
									'listProfesores' => $listProfesores,
									'editableProfesor' => $dbUser, 
									'listSecuencias' => $listSecuencias,
									'responseMessageEdit' => 'Formato incorrecto en la contraseña'
								]);
							}
						}
					}else{ // no ingresaron un cambio de contraseña
						$newUser = User::find($dbUser->idUser);
						$newUser->userName = $postData['nombreProfesor'];
						$newUser->email = $postData['emailProfesor'];
						$newUser->phone = $postData['telefonoProfesor'];
						$newUser->office = $postData['cubiculoProfesor'];
						if(isset($postData['academiaProfesor']))
						{
							$newUser->academy = $postData['academiaProfesor'];
						}	
						$newUser->save();
						$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
						$dbUser = User::where('email', $newUser->email)->first();
						if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
						{
							$listSecuencias = Secuencia::all();
							$listProfesores = User::where('userType', 'teacher')->get();
							return $this->renderHTML('adminProfesorList.twig', [
								'username' => $_SESSION['userName'],
								'listProfesores' => $listProfesores,
								'editableProfesor' => $dbUser,
								'listClases' => $dbUser_Rel, 
								'listSecuencias' => $listSecuencias,
								'responseMessageEdit' => 'Profesor actualizado'
							]);
						}else //no tiene
						{
							$listSecuencias = Secuencia::all();
							$listProfesores = User::where('userType', 'teacher')->get();
							return $this->renderHTML('adminProfesorList.twig', [
								'username' => $_SESSION['userName'],
								'listProfesores' => $listProfesores,
								'editableProfesor' => $dbUser, 
								'listSecuencias' => $listSecuencias,
								'responseMessageEdit' => 'Profesor actualizado'
							]);
						}
					}
				}else{ //el email ya esta registrado
					$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
					if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser,
							'listClases' => $dbUser_Rel, 
							'listSecuencias' => $listSecuencias,
							'responseMessageEdit' => 'El correo registrado ya existe'
						]);
					}else //no tiene
					{
						$listSecuencias = Secuencia::all();
						$listProfesores = User::where('userType', 'teacher')->get();
						return $this->renderHTML('adminProfesorList.twig', [
							'username' => $_SESSION['userName'],
							'listProfesores' => $listProfesores,
							'editableProfesor' => $dbUser, 
							'listSecuencias' => $listSecuencias,
							'responseMessageEdit' => 'El correo registrado ya existe'
						]);
					}
				}
			}catch(\Exception $e) // no pasa la validacion
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				
				if(!$dbUser_Rel->isEmpty()) //el profesor tiene clases
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser,
						'listClases' => $dbUser_Rel, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'Algo salio mal, por favor revisa tu información'
					]);
				}else //no tiene
				{
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'Algo salio mal, por favor revisa tu información'
					]);
				}
			}
		} //editProfesor

		function removeClasefromProfesor($request)
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData)->first();
			$auxdbUser_Rel = User_Rel::where('nombreRel', $postData['nombreRel'])->first();
			
			if($auxdbUser_Rel) //Existe la clase
			{
				$auxdbUser_Rel->delete();
				
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				$listSecuencias = Secuencia::all();
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'editableProfesor' => $dbUser,
					'listClases' => $dbUser_Rel, 
					'listSecuencias' => $listSecuencias,
					'responseMessageEdit' => 'Profesor eliminado de la clase'
				]);
			}else // no existe la clase
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				$listSecuencias = Secuencia::all();
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'editableProfesor' => $dbUser,
					'listClases' => $dbUser_Rel, 
					'listSecuencias' => $listSecuencias,
					'responseMessageEdit' => 'Error no se encuentra la clase'
				]);
			}
			
		}// removeClasefromProfesor

		function addClasetoProfesorinEdit($request) // agrega clases al profesor desde el menu editar
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first(); //Informacion del profesor
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //informacion de la secuencia
			$dbSubject = Subject::where('subjectName', $postData['materia'])->first();

			if($dbSecuencia && $dbSubject) //si existen tanto la secuencia como la materia
			{
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)
				->where('idSubject', $dbSubject->idSubject)->first(); //busca a relacion de materia-secuencia
				
				$dbUser_Rel = User_Rel::where('rel_id', $dbRel_Sec_Sub->id)
				->first(); // busca la relacion entre clase-profesor
				
				if (!$dbUser_Rel) //si la clase no tiene asignado un profesor
				{
					$newUser_Rel = new User_Rel();
					$newUser_Rel->user_id = $dbUser->idUser;
					$newUser_Rel->rel_id = $dbRel_Sec_Sub->id;
					$newUser_Rel->nombreRel = $dbSecuencia->claveSecuencia.' - '.$dbSubject->subjectName;
					$newUser_Rel->save();

					$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser,
						'listClases' => $dbUser_Rel, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'Clase agregada correctamente'
					]);

				}else //si la clase ya tiene un profesor asignado
				{
					$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSecuencias = Secuencia::all();
					$listProfesores = User::where('userType', 'teacher')->get();
					return $this->renderHTML('adminProfesorList.twig', [
						'username' => $_SESSION['userName'],
						'listProfesores' => $listProfesores,
						'editableProfesor' => $dbUser,
						'listClases' => $dbUser_Rel, 
						'listSecuencias' => $listSecuencias,
						'responseMessageEdit' => 'La clase ya tiene un profesor asignado'
					]);
				}
				
			}else // si no existe la materia o la secuencia
			{
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)->get();
				$listSecuencias = Secuencia::all();
				$listProfesores = User::where('userType', 'teacher')->get();
				return $this->renderHTML('adminProfesorList.twig', [
					'username' => $_SESSION['userName'],
					'listProfesores' => $listProfesores,
					'editableProfesor' => $dbUser,
					'listClases' => $dbUser_Rel, 
					'listSecuencias' => $listSecuencias,
					'responseMessageEdit' => 'Error no se encuentra la materia'
				]);	
			}			
		}//addClasetoProfesorinEdit

	}