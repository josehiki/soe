<?php
	namespace App\Controllers;

	use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
    use App\Models\Tarea;
    use App\Models\Entrega;
    use App\Models\Extra;
    use Laminas\Diactoros\Response\RedirectResponse;
    use Aws\S3\S3Client;
    use Aws\S3\AwsException;


	/**
	 * 
	 */
	class StudentDashController extends BaseController
	{
		
		function getStudentDashboard()
		{
			$dbUser_Rel = User_Rel::where('user_id', $_SESSION['userId'])->get(); //recupera las clases en las que esta el usuario
			$listClases; //lista de las relaciones secuencia-materia de las clases 
			$auxList;
			foreach ($dbUser_Rel as $clase) 
			{
				$dbRel_Sec_Sub = Rel_Sec_Sub::find($clase->rel_id);
				$auxSecuencia = Secuencia::find($dbRel_Sec_Sub->idSecuencia);
				$auxSubject = Subject::find($dbRel_Sec_Sub->idSubject);
				$auxList = [
					'idClase' => $clase->rel_id,
					'materia' => $auxSubject->subjectName,
					'secuencia' => $auxSecuencia->claveSecuencia
				];
				$listClases[] = $auxList;
			}		
			return $this->renderHTML('studentDashboard.twig', [
				'username' => $_SESSION['userName'],
				'listSubject' => $listClases
			]);
		}
		function getMateriaName($idClase) // recupera el nombre de la materia a partir del id de clase
        {
            $dbRel_Sec_Sub = Rel_Sec_Sub::find($idClase);
            $dbMateria = Subject::find($dbRel_Sec_Sub->idSubject);
            return $dbMateria;
        }
        function getSecuenciaClave($idClase)// recupera la secuencia a partir del id de clase
        {
            $dbRel_Sec_Sub = Rel_Sec_Sub::find($idClase);
            $dbSecuencia = Secuencia::find($dbRel_Sec_Sub->idSecuencia);
            return $dbSecuencia;
        }

		function getClaseDetail($request)
		{
			$postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            $dbSecuencia = $this->getSecuenciaClave($postData);
            $dbTareas = Tarea::where('clase_id', $postData)
            ->orderBy('fechaLimite', 'desc')
            ->get();
            
            return $this->renderHTML('studentClass.twig', [
                'username' => $_SESSION['userName'],
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'idClase' => $postData,
                'listTareas' => $dbTareas
			]);
		}//getClaseDetail

		function getTareaDetail($request)
		{
			$idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbMateria = $this->getMateriaName($idClase);
            $dbSecuencia = $this->getSecuenciaClave($idClase);
			$dbTarea = Tarea::find($idTarea);
			$consultaEntrega = Entrega::where('idTarea', $idTarea)
			->where('idUsuario', $_SESSION['userId'])->first();
			$tareaEntregada = false;
			if($consultaEntrega){
				$tareaEntregada = true;
			}
            return $this->renderHTML('studentTareaDetail.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
				'tarea' => $dbTarea,
				'tareaEntregada'=>$tareaEntregada
			]);
		}//getTareaDetail

		function getAnuncioDetail($request)
		{
			$idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbMateria = $this->getMateriaName($idClase);
            $dbSecuencia = $this->getSecuenciaClave($idClase);
            $dbTarea = Tarea::find($idTarea);
            return $this->renderHTML('studentAnuncioDetail.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'tarea' => $dbTarea
			]);
		}//getAnuncioDetail


		function uploadTarea($request)
		{
			$postData = $request->getParsedBody();


            $S3Options = [
            	'version'	=> 'latest',
            	'region'	=> 'us-east-2',
            	'credentials' =>
            	[
            		'key'	=> 'AKIAIU7EHNZIVLQF7S2A',
            		'secret' => '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'
            	]
            ]; //Configuracion objeto S3
            $s3 = new S3Client($S3Options);

			if(isset($_FILES['tareaAlumno'])) //hay un archivo para subir 
			{
				$flag = true;
				$responseMessage = '';
				if($_FILES["tareaAlumno"]["size"] > (1024*1024*25))
				{
					$flag = false;
					$responseMessage = 'El tamaño máximo permitido es 50MB';
				}
				if($flag)//paso las validaciones 
				{
					//Carga de archivo en el bucket
					$uploadedObject = $s3->putObject([
						'Bucket'	=> 'soe-bucket',
						'Key'		=> $_FILES['tareaAlumno']['name'],
						'SourceFile'=> $_FILES['tareaAlumno']['tmp_name']
					]);
					$newEntrega = new Entrega();
					$newEntrega->idTarea = $postData['idTarea'];
					$newEntrega->idUsuario = $_SESSION['userId'];
					$newEntrega->urlEntrega = $uploadedObject['ObjectURL'];
					$newEntrega->fecha = date("Y-m-d");;
					$newEntrega->save();
					return new RedirectResponse('/soe/alumno/'.$postData['idClase'].'/d/'.$postData['idTarea']);
				}
				else //fallo la validacion del archivo 
				{
					return new RedirectResponse('/soe/alumno/'.$postData['idClase'].'/d/'.$postData['idTarea']);
				}
			}else //error al subir el archivo 
			{
				return new RedirectResponse('/soe/alumno/'.$postData['idClase'].'/d/'.$postData['idTarea']);
			}
            
		} //uploadTarea

		function calendario($request)
		{
			$idClase = $request->getAttribute('idClase');
			$userMaterias = User_Rel::where('user_id', $_SESSION['userId'])->get(); // lista de materias del alumno
			$userExtras = Extra::where('user_id', $_SESSION['userId'])->get();//lista de actividades extracurriculares
			$listaTareas; // lista de las tareas de todas las materias del usuario
			$auxTarea;
			$colorExtra = '#5c6bc0';
			$color = array("#5BA8D4", "#5FDEB4", "#65C75F", "#B3B149", "#D6AC25");
			foreach ($userMaterias as $materia) //recorrer la lista de materias
			{	
				$auxListTareas = Tarea::where('clase_id', $materia->rel_id)->get();
				$auxNameMateria = $this->getMateriaName($materia->rel_id);
				$auxNameSecuencia = $this->getSecuenciaClave($materia->rel_id);
				$colorMateria = $color[rand(0,4)];
				foreach ($auxListTareas as $tarea) {
					$auxTarea = [
						'title' => $auxNameMateria->subjectName.' - '.$tarea->nombre,
						'description' => $tarea->descripcion,
						'start' => $tarea->fechaLimite,
						'color' => $colorMateria,
						'extendedProps' => [
							"tipo" => $tarea->tipo,
							"secuencia" => $auxNameSecuencia->claveSecuencia
						],
						'textColor' => 'white'
						
					];
					$listaTareas[] = $auxTarea;
				}
			}
			foreach ($userExtras as $extra) {
				$auxTarea = [
					'title' => $extra->titulo,
					'description' => $extra->descripcion,
					'start' => $extra->fechaLimite,
					'color' => $colorExtra,
					'extendedProps' => [
						"tipo" => 'Actividad Extracurricular',
						"secuencia" => ''
					],
					'textColor' => 'white'
					
				];
				$listaTareas[] = $auxTarea;
			}
			// print_r($listaTareas);
			return $this->renderHTML('studentCalendar.twig', [
				'username' => $_SESSION['userName'],
				'idClase' => $idClase,
				'listaTareas' => $listaTareas
			]);
			
		}//calendario

		function addActividadExtra($request)
		{
			$postData = $request->getParsedBody();
			print_r ($postData);
			
			$newActividadExtra = new Extra();
			$newActividadExtra->user_id = $_SESSION['userId'];
			$newActividadExtra->titulo = $postData['nombreActividad'];
			$newActividadExtra->descripcion = $postData['descripcionActividad'];
			$newActividadExtra->fechaLimite = $postData['fechaActividad'];
			$newActividadExtra->save();
			return new RedirectResponse('/soe/alumno/calendario/'.$postData['idClase']);
		}//addActividadExtra
		
	}