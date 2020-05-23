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
	 * Controlador para la gestion de alumnos
	 */
    class AdminAlumnoController extends BaseController
    {
        function getAlumnoAddForm($request) //Imprime el formulario de nuevo alumno
		{
			return $this->renderHTML('adminAlumnoAdd.twig', [
				'username' => $_SESSION['userName']
			]);
        }
        function getAlumnoList($request) // Imprime la lista de alumnos registrados
		{
			$listAlumnos = User::where('userType', 'student')->get();
			return $this->renderHTML('adminAlumnoList.twig', [
				'username' => $_SESSION['userName'],
				'listAlumnos' => $listAlumnos
			]);
        }
        
        function addAlumno($request) // Agrega la informacion general de un alumno
        {
            $postData = $request->getParsedBody();
            $carreras = [
				'Licenciatura en Ciencias de la informática', 
				'Ingenieria en informática', 
				'Licenciatura en Administración industrial', 
				'Ingeniería en Transporte', 
				'Ingeniería Industrial'
            ];
            
            $validator = v::key('nombreAlumno', v::notEmpty()->stringType())
            ->key('emailAlumno', v::notEmpty()->email())
            ->key('contraAlumno', v::notEmpty()->length(6,null))
            ->key('recontraAlumno', v::notEmpty()->length(6,null))
            ->key('telefonoAlumno', v::notEmpty()->length(10,10)->number())
            ->key('boletaAlumno', v::notEmpty()->length(10,10)->number())
            ->key('carreraAlumno', v::notEmpty()->stringType()); //validador de los paramtros recibidos

            try {
                $validator->assert($postData); //validacion de postData
                if ($postData['contraAlumno'] == $postData['recontraAlumno']) // las contraseñas son iguales
                {
					$auxUser = User::where('email', $postData['emailAlumno'])->first();
                    if(!$auxUser)//existe ese email registrado
                    {
					    $auxUser = User::where('boleta', $postData['boletaAlumno'])->first();
                        if (!$auxUser) //si no existe la boleta registrada
                        {
                            $newUser = new User ();
                            $newUser->userType = 'student';
                            $newUser->userName = $postData['nombreAlumno'];
                            $newUser->email = $postData['emailAlumno'];
                            $newUser->userPassword = password_hash($postData['contraAlumno'], PASSWORD_DEFAULT);
                            $newUser->phone = $postData['telefonoAlumno'];
                            $newUser->boleta = $postData['boletaAlumno'];
                            $flag = false;
                            foreach ($carreras as $carr) // comprueba que la carrera ingresada exista realmente
                            {
                                if($postData['carreraAlumno'] == $carr)
                                {
                                    $flag = true;
                                    break;
                                }
                            }
                            if($flag)
                            {
                                $newUser->carrera = $postData['carreraAlumno'];
                            }	
                            $newUser->save();

                            $listSecuencias = Secuencia::all();
                            return $this->renderHTML('adminAlumnoAddHorario.twig', [
                                'username' => $_SESSION['userName'],
                                'actualAlumno' => $newUser, 
                                'listSecuencias' => $listSecuencias
                            ]);
                        }else //ya existe la boleta registrada
                        {
                            return $this->renderHTML('adminAlumnoAdd.twig', [
                                'username' => $_SESSION['userName'],
                                'responseMessage' => 'La boleta registrada ya existe'
                            ]);
                        }
						
                    }else // ya existe el correo 
                    {
						return $this->renderHTML('adminAlumnoAdd.twig', [
                            'username' => $_SESSION['userName'],
                            'responseMessage' => 'El correo registrado ya existe'
                        ]);
					}
				}else // las contraseñas no son iguales
				{
					return $this->renderHTML('adminAlumnoAdd.twig', [
                        'username' => $_SESSION['userName'],
                        'responseMessage' => 'Las contraseñas deben coincidir'
                    ]);
				}

            } catch (\Exception $e) //no paso la validacion
            {
                return $this->renderHTML('adminAlumnoAdd.twig', [
					'username' => $_SESSION['userName'],
					'responseMessage' => 'Algo salio mal, por favor revisa tu información '
				]);
            }
        } // addAlumno

        function getMateriasfromSecuencia($request) // imprime las materias segun la secuencia seleccionada
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
					return $this->renderHTML('adminAlumnoAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualAlumno' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'listMaterias' => $listSubjectNames,
						'secuencia' => $postData['clave']
					]);							
				}else // la secuencia no tiene materias
				{
					$listSecuencias = Secuencia::all();
					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					return $this->renderHTML('adminAlumnoAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualAlumno' => $dbUser, 
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
				return $this->renderHTML('adminAlumnoAddHorario.twig', [
					'username' => $_SESSION['userName'],
					'actualAlumno' => $dbUser, 
					'listRelNames' => $listRelNames,
					'listSecuencias' => $listSecuencias,
					'responseMessage' => 'No existe esa secuencia'
				]);
			}
		}// getMateriasfromSecuencia
		
		function addClasetoAlumno($request) // agrega clases al alumno
		{
			$postData = $request->getParsedBody();
			$dbUser = User::where('email', $postData['email'])->first(); //Informacion del profesor
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //informacion de la secuencia
			$dbSubject = Subject::where('subjectName', $postData['materia'])->first();
			

			if($dbSecuencia && $dbSubject) //si existen tanto la secuencia como la materia
			{
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)
				->where('idSubject', $dbSubject->idSubject)->first(); //busca a relacion de materia-secuencia
				
				$dbUser_Rel = User_Rel::where('user_id', $dbUser->idUser)
				->where('rel_id', $dbRel_Sec_Sub->id)
				->first(); // busca la relacion 

				if(!$dbUser_Rel)// el alumno no tiene esa clase
				{
					$newUser_Rel = new User_Rel();
					$newUser_Rel->user_id = $dbUser->idUser;
					$newUser_Rel->rel_id = $dbRel_Sec_Sub->id;
					$newUser_Rel->nombreRel = $dbSecuencia->claveSecuencia.' - '.$dbSubject->subjectName;
					$newUser_Rel->save();

					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminAlumnoAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualAlumno' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listRelNames' => $listRelNames,
						'responseMessage' => 'Clase agregada correctamente'
					]);
				}else
				{
					$listSecuencias = Secuencia::all();
					$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
					return $this->renderHTML('adminAlumnoAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualAlumno' => $dbUser, 
						'listRelNames' => $listRelNames,
						'listSecuencias' => $listSecuencias,
						'responseMessage' => 'No puede agregarse a la misma clase'
					]);
				}
				
			}else // si no existe la materia o la secuencia
			{
				$listSecuencias = Secuencia::all();
				$listRelNames = User_Rel::where('user_id', $dbUser->idUser)->get();
				return $this->renderHTML('adminAlumnoAddHorario.twig', [
					'username' => $_SESSION['userName'],
					'actualAlumno' => $dbUser, 
					'listRelNames' => $listRelNames,
					'listSecuencias' => $listSecuencias,
					'responseMessage' => 'Ha ocurrido un error'
				]);
			}
		} // addClasetoAlumno

		function addAlumnoCanceled($request) // cancela el alta del alumno y elimina todo lo cuardado
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
			return new RedirectResponse('/soe/dashboard/alumno/a');
		}//addAlumnoCanceled

		function getAlumnoDeleteConfirmation($request)
		{
			$postData = $request->getAttribute('email');

			$deleteAlumno = User::where('email', $postData)->first();
			$listAlumnos = User::where('userType', 'student')->get();
			return $this->renderHTML('adminAlumnoList.twig', [
				'username' => $_SESSION['userName'],
				'listAlumnos' => $listAlumnos,
				'deleteAlumno' => $deleteAlumno
			]);
		}//getAlumnoDeleteConfirmation

		function deleteAlumno($request)
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
				$listAlumnos = User::where('userType', 'student')->get();
				return $this->renderHTML('adminAlumnoList.twig', [
					'username' => $_SESSION['userName'],
					'listAlumnos' => $listAlumnos,
					'responseMessage' => 'Alumno eliminado con exito'
				]);
			}else // no existe el usuario
			{
				$listAlumnos = User::where('userType', 'student')->get();
				return $this->renderHTML('adminAlumnoList.twig', [
					'username' => $_SESSION['userName'],
					'listAlumnos' => $listAlumnos,
					'responseMessage' => 'El alumno no existe'
				]);
			}
		}//deleteAlumno
    }