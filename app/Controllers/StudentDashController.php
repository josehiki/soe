<?php
	namespace App\Controllers;

	use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
    use App\Models\Tarea;
    use Laminas\Diactoros\Response\RedirectResponse;
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
	}