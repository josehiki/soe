<?php 
	namespace App\Controllers;

	use App\Models\Subject;
	use App\Models\Secuencia;
	use App\Models\Rel_Sec_Sub;
	use Respect\Validation\Validator as v;
	use Laminas\Diactoros\Response\RedirectResponse;
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
				'Ingenieria en informática', 
				'Licenciatura en Administración industrial', 
				'Ingeniería en Transporte', 
				'Ingeniería Industrial'
			];
			$flag = false;

			$validator = v::key('clave', v::notEmpty()->noWhitespace()->length(5,5)->uppercase()); 

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

        	$dbSecuencias = Secuencia::where('claveSecuencia', $actualSecuencia->claveSecuencia)->first(); //obtener el registro de la secuencia que se esta trabajando
			$dbSubject = Subject::where('subjectName', $postData['materia'])
                	->first(); //comprobacion que existe la materia ingresada por el usuario
                	
        	if($dbSubject)//existe la materia?
        	{
				// Comprobar que no existe ya una relacion de la secuencia con esa materia
				$flag = Rel_Sec_Sub::where('idSecuencia', $dbSecuencias->idSecuencia)
				->where('idSubject', $dbSubject->idSubject)->first();

				if(!$flag)//si no encuentra una relacion previa existente
				{
					$newRel = new Rel_Sec_Sub(); //nueva instancia de la clase de relacion
					$newRel->idSecuencia = $dbSecuencias->idSecuencia;
					$newRel->idSubject = $dbSubject->idSubject;
					$newRel->save();

					//busca todos los registros de la secuencia actual
					$auxRel = Rel_Sec_Sub::where('idSecuencia', $dbSecuencias->idSecuencia)->get();
					
					$auxPrintRel = []; //auxiliar para el arreglo a imprimir 
					foreach ($auxRel as $unit) {
						$auxMat = Subject::where('idSubject', $unit->idSubject)->first();
						array_push($auxPrintRel, $auxMat->subjectName);
					}

					$responseMessage = 'Materia agregada';
					return $this->renderHTML('adminSecuenciaAdd2.twig', [
						'username' => $_SESSION['userName'],
						'responseMessage' => $responseMessage, 
						'actualSecuencia' => $actualSecuencia, 
						'relSecSub' => $auxPrintRel, 
						'subjects' => $dbAuxSubjects
					]);
				}else{
					$auxRel = Rel_Sec_Sub::where('idSecuencia', $dbSecuencias->idSecuencia)->get();
					
					$auxPrintRel = []; //auxiliar para el arreglo a imprimir 
					foreach ($auxRel as $unit) {
						$auxMat = Subject::where('idSubject', $unit->idSubject)->first();
						array_push($auxPrintRel, $auxMat->subjectName);
					}

					$responseMessage = 'No se puede agregar la misma materia dos veces';
					return $this->renderHTML('adminSecuenciaAdd2.twig', [
						'username' => $_SESSION['userName'],
						'responseMessage' => $responseMessage, 
						'actualSecuencia' => $actualSecuencia, 
						'relSecSub' => $auxPrintRel, 
						'subjects' => $dbAuxSubjects
					]);	
				}
        	}else
        	{
        		$auxRel = Rel_Sec_Sub::where('idSecuencia', $dbSecuencias->idSecuencia)->get();
					
					$auxPrintRel = []; //auxiliar para el arreglo a imprimir 
					foreach ($auxRel as $unit) {
						$auxMat = Subject::where('idSubject', $unit->idSubject)->first();
						array_push($auxPrintRel, $auxMat->subjectName);
					}
        		$responseMessage = 'La materia que ingreso no existe';

        		return $this->renderHTML('adminSecuenciaAdd2.twig', [
					'username' => $_SESSION['userName'],
						'responseMessage' => $responseMessage, 
						'actualSecuencia' => $actualSecuencia, 
						'relSecSub' => $auxPrintRel, 
						'subjects' => $dbAuxSubjects
				]);
        	}
		}

		function addSecuenciaCancel($request)
		{
			$postData = $request->getParsedBody();

			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //obtiene la informacion de la secuencia de la clave enviada 
			$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtiene las relaciones de la secuencia enviada

			if($dbRel_Sec_Sub)
			{
				foreach ($dbRel_Sec_Sub as $rel) {
					$auxRel = $rel;
					$auxRel->delete();
				}
				$dbSecuencia->delete();
				return new RedirectResponse('/soe/dashboard/secuencia/add');
			}else{
				return new RedirectResponse('/soe/dashboard/secuencia/add');
			}
		}


		function getAdminSecuenciaList($request)
		{
			$dbSecuencias = Secuencia::all(); //obtener todas las secuencias registradas

			return $this->renderHTML('adminSecuenciaList.twig', [
				'username' => $_SESSION['userName'],
				'secuencias' => $dbSecuencias
			]);
		}

		function getSecuenciaDetalle($request)
		{
			$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			$postData = $request->getAttribute('clave');
			
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData)->first(); //obtener la informacion de la secuencia solicitada
			$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
			
			if(!$dbRel_Sec_Sub->isEmpty()){
				$listSubject = null;//Lista de ids de las materias
				foreach ($dbRel_Sec_Sub as $rel) {
					$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
					$listSubject[] = $auxSubject->idSubject;
				}
				
				$listSubjectNames = Subject::find($listSubject);
				
				return $this->renderHTML('adminSecuenciaList.twig', [
					'username' => $_SESSION['userName'],
					'secuencias' => $dbSecuencias, 
					'listSubjects' => $listSubjectNames, 
					'actualSecuencia' => $dbSecuencia 

				]);				
			}else
			{
				return $this->renderHTML('adminSecuenciaList.twig', [
					'username' => $_SESSION['userName'],
					'secuencias' => $dbSecuencias, 
					'listSubjectsEmpty' => 'No hay Agregadas', 
					'actualSecuencia' => $dbSecuencia 

				]);
			}
		}

		function deleteSecuencia($request){
			$dbSecuencias = Secuencia::all(); 
			$postData = $request->getAttribute('clave');

			return $this->renderHTML('adminSecuenciaList.twig', [
				'username' => $_SESSION['userName'],
				'secuencias' => $dbSecuencias, 
				'deletedSubject' => $postData
			]);
		}

		function getDeleteSecuencia($request)
		{
			$postData = $request->getParsedBody();

			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['clave'])->first(); //obtiene la informacion de la secuencia de la clave enviada 
			$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtiene las relaciones de la secuencia enviada

			if($dbSecuencia)
			{
				if($dbRel_Sec_Sub)
				{
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxRel = $rel;
						$auxRel->delete();
					}
					$dbSecuencia->delete();
					$dbSecuencias = Secuencia::all();
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias, 
						'responseMessage' => 'La secuencia ha sido eliminada correctamente'
					]);
				}else{
					$dbSecuencia->delete();
					$dbSecuencias = Secuencia::all();
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias, 
						'responseMessage' => 'La secuencia ha sido eliminada'
					]);
				}

			}else
			{
				$dbSecuencias = Secuencia::all();
				return $this->renderHTML('adminSecuenciaList.twig', [
					'username' => $_SESSION['userName'],
					'secuencias' => $dbSecuencias, 
					'responseMessage' => 'Ha ocurrido un error'
				]);
			}
		}

		function getEditableSecuencia($request)
		{
			$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			$dbSubjects = Subject::all(); //permite cargar las sugerencias de materia

			$postData = $request->getAttribute('clave');
			
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData)->first(); //obtener la informacion de la secuencia solicitada
			$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
			
			$listSubject = null;//Lista de ids de las materias
			foreach ($dbRel_Sec_Sub as $rel) {
				$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
				$listSubject[] = $auxSubject->idSubject;
			}
			
			$listSubjectNames = Subject::find($listSubject);
			// echo $listSubjectNames;

			return $this->renderHTML('adminSecuenciaList.twig', [
				'username' => $_SESSION['userName'],
				'secuencias' => $dbSecuencias,
				'editableSecuencia' => $dbSecuencia,
				'subjecNames' => $listSubjectNames,
				'subjects' => $dbSubjects
			]);
		}

		function deletedMateriafromSecuencia($request)
		{
			$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			$dbSubjects = Subject::all(); //permite cargar las sugerencias de materia

			
			$postData = $request->getParsedBody();
			
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['claveSecuencia'])->first(); //obtener la informacion de la secuencia que se esta recibiendo
			$dbSubject = Subject::where('subjectName', $postData['materia'])->first(); //obtener la informacion de la materia que se esta recibiendo

			

			if($dbSecuencia && $dbSubject)
			{
					$auxRelacion = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->where('idSubject', $dbSubject->idSubject)->first();
				
				if($auxRelacion)
				{
					$auxRelacion->delete();

					// REECARGAR LA PAGINA DE EDITAR LA MATERIA
					$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				
					$listSubject = null;//Lista de ids de las materias
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listSubjectNames = Subject::find($listSubject);
					// echo $listSubjectNames;
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias,
						'editableSecuencia' => $dbSecuencia,
						'subjecNames' => $listSubjectNames,
						'subjects' => $dbSubjects, 
						'responseMessageEdit' => 'Materia removida con exito'
					]);	
				}else
				{
					// REECARGAR LA PAGINA DE EDITAR LA MATERIA
					$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				
					$listSubject = null;//Lista de ids de las materias
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listSubjectNames = Subject::find($listSubject);
					// echo $listSubjectNames;
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias,
						'editableSecuencia' => $dbSecuencia,
						'subjecNames' => $listSubjectNames,
						'subjects' => $dbSubjects, 
						'responseMessageEdit' => 'No se ha encontrado la materia'
					]);	
				}
			}else{
				return new RedirectResponse('/soe/dashboard/secuencia/list');
			}
		}


		function renameSecuencia($request)
		{
			$postData = $request->getParsedBody();
			$dbSubjects = Subject::all(); //permite cargar las sugerencias de materia
			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['actualClave'])->first(); //obtener la informacion de la secuencia Actual

			$carreras = [
				'Licenciatura en Ciencias de la informática', 
				'Ingenieria en informática', 
				'Licenciatura en Administración industrial', 
				'Ingeniería en Transporte', 
				'Ingeniería Industrial'
			];


			$flag = false;

			$validator = v::key('clave', v::notEmpty()->noWhitespace()->length(5,5)->uppercase()); 

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
					$auxSecuencias = Secuencia::where('claveSecuencia', $postData['clave'])
					->first();
					if (!$auxSecuencias || $postData['actualClave'] == $postData['actualClave']) {

						$dbSecuencia->claveSecuencia = $postData['clave'];
						$dbSecuencia->carreraSecuencia = $postData['carrera'];
						$dbSecuencia->save();

						
						$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
						// REECARGAR LA PAGINA DE EDITAR LA MATERIA
						$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
					
						$listSubject = null;//Lista de ids de las materias
						foreach ($dbRel_Sec_Sub as $rel) {
							$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
							$listSubject[] = $auxSubject->idSubject;
						}
						
						$listSubjectNames = Subject::find($listSubject);
						// echo $listSubjectNames;
						return $this->renderHTML('adminSecuenciaList.twig', [
							'username' => $_SESSION['userName'],
							'secuencias' => $dbSecuencias,
							'editableSecuencia' => $dbSecuencia,
							'subjecNames' => $listSubjectNames,
							'subjects' => $dbSubjects, 
							'responseMessageEdit' => 'Secuencia actualizada'
						]);	
					
					}else
					{
						$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
						// REECARGAR LA PAGINA DE EDITAR LA MATERIA
						$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
					
						$listSubject = null;//Lista de ids de las materias
						foreach ($dbRel_Sec_Sub as $rel) {
							$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
							$listSubject[] = $auxSubject->idSubject;
						}
						
						$listSubjectNames = Subject::find($listSubject);
						// echo $listSubjectNames;
						return $this->renderHTML('adminSecuenciaList.twig', [
							'username' => $_SESSION['userName'],
							'secuencias' => $dbSecuencias,
							'editableSecuencia' => $dbSecuencia,
							'subjecNames' => $listSubjectNames,
							'subjects' => $dbSubjects, 
							'responseMessageEdit' => 'Ya existe esa clave'
						]);	
					} 
				}else
				{
					$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
					// REECARGAR LA PAGINA DE EDITAR LA MATERIA
					$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				
					$listSubject = null;//Lista de ids de las materias
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listSubjectNames = Subject::find($listSubject);
					// echo $listSubjectNames;
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias,
						'editableSecuencia' => $dbSecuencia,
						'subjecNames' => $listSubjectNames,
						'subjects' => $dbSubjects, 
						'responseMessageEdit' => 'Revise su información'
					]);	
				}
			} catch (\Exception $e) {
				$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
				// REECARGAR LA PAGINA DE EDITAR LA MATERIA
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
			
				$listSubject = null;//Lista de ids de las materias
				foreach ($dbRel_Sec_Sub as $rel) {
					$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
					$listSubject[] = $auxSubject->idSubject;
				}
				
				$listSubjectNames = Subject::find($listSubject);
				// echo $listSubjectNames;
				return $this->renderHTML('adminSecuenciaList.twig', [
					'username' => $_SESSION['userName'],
					'secuencias' => $dbSecuencias,
					'editableSecuencia' => $dbSecuencia,
					'subjecNames' => $listSubjectNames,
					'subjects' => $dbSubjects, 
					'responseMessageEdit' => 'Formato incorrecto en la clave'
				]);	
			}
		}

		function removeMateriaFromEditedSecuencia($request)
		{
			$dbSubjects = Subject::all(); //permite cargar las sugerencias de materia
			$postData = $request->getParsedBody();

			$dbSecuencia = Secuencia::where('claveSecuencia', $postData['actualClave'])->first(); //obtener la informacion de la secuencia Actual
			$dbSubject = Subject::where('subjectName', $postData['subject'])
                	->first(); //comprobacion que existe la materia ingresada por el usuario
                	
        	if($dbSubject)//existe la materia?
        	{
				// Comprobar que no existe ya una relacion de la secuencia con esa materia
				$flag = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)
				->where('idSubject', $dbSubject->idSubject)->first();

				if(!$flag)//si no encuentra una relacion previa existente
				{
					$newRel = new Rel_Sec_Sub(); //nueva instancia de la clase de relacion
					$newRel->idSecuencia = $dbSecuencia->idSecuencia;
					$newRel->idSubject = $dbSubject->idSubject;
					$newRel->save();

					$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
				
					// REECARGAR LA PAGINA DE EDITAR LA MATERIA
					$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				
					$listSubject = null;//Lista de ids de las materias
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listSubjectNames = Subject::find($listSubject);
					// echo $listSubjectNames;
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias,
						'editableSecuencia' => $dbSecuencia,
						'subjecNames' => $listSubjectNames,
						'subjects' => $dbSubjects, 
						'responseMessageEdit' => 'Materia agregada correctamente'
					]);	
				}else //si si encuentra una relacion previa
				{
					$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
					// REECARGAR LA PAGINA DE EDITAR LA MATERIA
					$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
				
					$listSubject = null;//Lista de ids de las materias
					foreach ($dbRel_Sec_Sub as $rel) {
						$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
						$listSubject[] = $auxSubject->idSubject;
					}
					
					$listSubjectNames = Subject::find($listSubject);
					// echo $listSubjectNames;
					return $this->renderHTML('adminSecuenciaList.twig', [
						'username' => $_SESSION['userName'],
						'secuencias' => $dbSecuencias,
						'editableSecuencia' => $dbSecuencia,
						'subjecNames' => $listSubjectNames,
						'subjects' => $dbSubjects, 
						'responseMessageEdit' => 'No se puede agregar la misma materia'
					]);	
				}
        	}else //no existe la materia
        	{
        		$dbSecuencias = Secuencia::all(); //permite volver a cargar las demas secuencias
			
				// REECARGAR LA PAGINA DE EDITAR LA MATERIA
				$dbRel_Sec_Sub = Rel_Sec_Sub::where('idSecuencia', $dbSecuencia->idSecuencia)->get(); //obtener los id de las materias relacionadas a esa secuencia
			
				$listSubject = null;//Lista de ids de las materias
				foreach ($dbRel_Sec_Sub as $rel) {
					$auxSubject = Subject::where('idSubject', $rel->idSubject)->first();
					$listSubject[] = $auxSubject->idSubject;
				}
				
				$listSubjectNames = Subject::find($listSubject);
				// echo $listSubjectNames;
				return $this->renderHTML('adminSecuenciaList.twig', [
					'username' => $_SESSION['userName'],
					'secuencias' => $dbSecuencias,
					'editableSecuencia' => $dbSecuencia,
					'subjecNames' => $listSubjectNames,
					'subjects' => $dbSubjects, 
					'responseMessageEdit' => 'No existe esa materia'
				]);	
        	}
		}
	}
