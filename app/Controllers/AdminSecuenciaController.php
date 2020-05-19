<?php 
	namespace App\Controllers;

	use App\Models\Subject;
	use App\Models\Secuencia;
	use App\Models\Rel_Sec_Sub;
	use Respect\Validation\Validator as v;
	/**
	 * 
	 */
	class AdminSecuenciaController extends BaseController
	{
		
		function getAdminAddSecuenciaForm($request)
		{
			
			return $this->renderHTML('adminSecuenciaAdd.twig', [
				'username' => $_SESSION['userName']
			]);
		}

		function getAdminAddSecuencia($request){
			$postData = $request->getParsedBody();
			$carreras = [
				'Licenciatura en Ciencias de la informática', 
				'Ingenieria en informática'
			];
			$flag = false;

			$validator = v::key('clave', v::notEmpty()->noWhitespace()->length(1,5)->uppercase()); 

			try {
				$validator->assert($postData); //validacion para el formato de la clave
				//verificar que no se haya insertado algo en el select de carrera
				foreach ($carreras as $carr) {
					if($postData['carrera'] == $carr)
					{
						$flag = true;
						break;
					}
				}
				if($flag) //accion si paso todas las validaciones 
				{
					// comprobacion de que no existe otra secuencia con la misma clave
					$dbSecuencias = Secuencia::where('claveSecuencia', $postData['clave'])
					->first();
					if (!$dbSecuencias) {

					 	$newSecuencia = new Secuencia();
						$newSecuencia->claveSecuencia = $postData['clave'];
						$newSecuencia->carreraSecuencia = $postData['carrera'];
						$newSecuencia->save();

						$dbSubjects = Subject::all();
						
						return $this->renderHTML('adminSecuenciaAdd2.twig', [
							'username' => $_SESSION['userName'],
							'subjects' => $dbSubjects,
							'actualSecuencia' => $newSecuencia	
						]);
					
					}else
					{
						$responseMessage = 'Ya existe esa secuencia';
				
						return $this->renderHTML('adminSecuenciaAdd.twig', [
							'username' => $_SESSION['userName'],
							'responseMessage' => $responseMessage
						]);
					} 
				}else
				{
					// error si no se pudo verificar la carrera 
					$responseMessage = 'Por favor revise su informacion';
				
					return $this->renderHTML('adminSecuenciaAdd.twig', [
						'username' => $_SESSION['userName'],
						'responseMessage' => $responseMessage
					]);	
				}
			} catch (\Exception $e) {

				// error si no se pudo validad la clave de la secuencia
				$responseMessage = 'Por favor revise su informacion';
				
				return $this->renderHTML('adminSecuenciaAdd.twig', [
					'username' => $_SESSION['userName'],
					'responseMessage' => $responseMessage
				]);
			}	
		}

		// Agrega materias a la secuencia previa, Parte del alta de secuencias
		function addMateriatoSecuencia($request)
		{
			$postData = $request->getParsedBody();
			$dbAuxSubjects = Subject::all();

			$actualSecuencia = new Secuencia(); //objeto secuencia
			$actualSecuencia->claveSecuencia = $postData['clave'];
			$actualSecuencia->carreraSecuencia = $postData['carrera'];

			$dbSubject = Subject::where('subjectName', $postData['materia'])
                	->first(); //comprobacion que existe la materia ingresada por el usuario
                	
        	if($dbSubject)//existe la materia?
        	{
            	$dbSecuencias = Secuencia::where('claveSecuencia', $actualSecuencia->claveSecuencia)->first(); //obtener el registro de la secuencia que se esta trabajando
				
				$newRel = new Rel_Sec_Sub(); //nueva instancia de la clase de relacion
				$newRel->idSecuencia = $dbSecuencias->idSecuencia;
				$newRel->idSubject = $dbSubject->idSubject;
				$newRel->save();

				$auxPrintRel = []; //auxiliar para el arreglo a imprimir 
				$auxRel = Rel_Sec_Sub::where('idSecuencia', $dbSecuencias->idSecuencia)->get(); //busca todos los registros de la secuencia actual

				// foreach ($auxRel as $unit) {
				// 	echo $unit->idSubject;
				// }
				$otroNombre = null;
				foreach ($auxRel as $unit) {
					$otroNombre = Subject::where('idSubject', $unit->idSubject)->first();
					array_push($auxPrintRel, $otroNombre->subjectName);
				}
				return $this->renderHTML('adminSecuenciaAdd2.twig', [
					'username' => $_SESSION['userName'],
					'responseMessage' => $responseMessage, 
					'actualSecuencia' => $actualSecuencia, 
					'relSecSub' => $auxPrintRel, 
					'subjects' => $dbAuxSubjects
				]);
        	}else
        	{
        		$responseMessage = 'La materia que ingreso no existe';

        		return $this->renderHTML('adminSecuenciaAdd2.twig', [
					'username' => $_SESSION['userName'],
					'responseMessage' => $responseMessage, 
					'actualSecuencia' => $actualSecuencia,
					'subjects' => $dbAuxSubjects
				]);
        	}
		}


	}
