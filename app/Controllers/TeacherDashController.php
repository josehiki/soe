<?php
	namespace App\Controllers;

	use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
	/**
	 * 
	 */
	class TeacherDashController extends BaseController
	{
		function getTeacherDashboard($request){
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
			return $this->renderHTML('teacherDashboard.twig', [
				'username' => $_SESSION['userName'],
				'listSubject' => $listClases
			]);
		}
	}