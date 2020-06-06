<?php
	namespace App\Controllers;

	use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
    use App\Models\Tarea;
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
            ->orderBy('fechaLimite', 'asc')
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
            return $this->renderHTML('studentTareaDetail.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'tarea' => $dbTarea
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
			$idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');


            $S3Options = [
            	'version'	=> 'latest',
            	'region'	=> 'us-east-2',
            	'credentials' =>
            	[
            		'key'	=> 'AKIAIU7EHNZIVLQF7S2A',
            		'secret' => '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'
            	]
            ];
            $s3 = new S3Client($S3Options);

			// if(isset($_FILES['tareaAlumno']))
			// {
			// 	$uploadedObject = $s3->putObject([
			// 		'Bucket'	=> 'soe-bucket',
			// 		'Key'		=> $_FILES['tareaAlumno']['name'],
			// 		'SourceFile'=> $_FILES['tareaAlumno']['tmp_name']
			// 	]);
			// 	print_r($uploadedObject);
			// }else
			// {
			// 	echo "Error al cargar el archivo";
			// }
            try{
			$result = $s3->getObject([
			        'Bucket' => 'soe-bucket',
			        'Key'    => 'Morgado_Jose_T25.pdf'
			    ]);

			    // Display the object in the browser.
			    header("Content-Type: {$result['ContentType']}");
			    echo $result['Body'];
			} catch (S3Exception $e) {
			    echo $e->getMessage() . PHP_EOL;
			}
		}
	}