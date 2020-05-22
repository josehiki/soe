<?php 
	namespace App\Controllers;

	use Respect\Validation\Validator as v;
	use App\Models\User;
	use App\Models\Secuencia;
	use App\Models\Subject;
	use App\Models\Rel_Sec_Sub;
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
			return $this->renderHTML('adminProfesorList.twig', [
				'username' => $_SESSION['userName']
			]);
		}

		function addProfesor($request){
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

		}

		function getMateriasfromSecuencia($request)
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
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'listMaterias' => $listSubjectNames,
						'secuencia' => $postData['clave']
					]);							
				}else // la secuencia no tiene materias
				{
					$listSecuencias = Secuencia::all();
					return $this->renderHTML('adminProfesorAddHorario.twig', [
						'username' => $_SESSION['userName'],
						'actualProfesor' => $dbUser, 
						'listSecuencias' => $listSecuencias,
						'secuencia' => $postData['clave'],
						'messageList' => 'Esta secuencia no tiene materias registradas'
					]);
				}
			}else // La secuencia no existe
			{
				$listSecuencias = Secuencia::all();
				return $this->renderHTML('adminProfesorAddHorario.twig', [
					'username' => $_SESSION['userName'],
					'actualProfesor' => $dbUser, 
					'listSecuencias' => $listSecuencias,
					'responseMessage' => 'No existe esa secuencia'
				]);
			}
				
		} // getMateriasfromSecuencia

		function addMateriaSecuenciatoProfesor($request)
		{
			
		} //addMateriaSecuenciatoProfesor
	}