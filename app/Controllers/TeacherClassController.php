<?php
    namespace App\Controllers;
    use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
    use App\Models\Tarea;
    use Laminas\Diactoros\Response\RedirectResponse;

    
    class TeacherClassController extends BaseController
    {
        function getClaseDetail($request) //carga las tareas de una materia
        {
            $postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            $dbSecuencia = $this->getSecuenciaClave($postData);
            $dbTareas = Tarea::where('clase_id', $postData)
            ->orderBy('fechaLimite', 'asc')
            ->get();
            
            return $this->renderHTML('teacherClass.twig', [
                'username' => $_SESSION['userName'],
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'idClase' => $postData,
                'listTareas' => $dbTareas
			]);
        }//getClaseDetail

        function getMateriaName($idClase) // recupera el nombre de la materia a partir del id de clase
        {
            $clase = User_Rel::find($idClase);
            $dbRel_Sec_Sub = Rel_Sec_Sub::find($clase->rel_id);
            $dbMateria = Subject::find($dbRel_Sec_Sub->idSubject);
            return $dbMateria;
        }
        function getSecuenciaClave($idClase)// recupera la secuencia a partir del id de clase
        {
            $clase = User_Rel::find($idClase);
            $dbRel_Sec_Sub = Rel_Sec_Sub::find($clase->rel_id);
            $dbSecuencia = Secuencia::find($dbRel_Sec_Sub->idSecuencia);
            return $dbSecuencia;
        }

        function getNuevaTareaForm($request) //imprimir el form de alta tarea
        {
            $postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            return $this->renderHTML('teacherTareaAdd.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $postData,
                'materia' => $dbMateria
			]);
        }//getNuevaTareaForm

        function addTarea($request) //alta tarea
        {
            $postData = $request->getParsedBody();
            
            $newTarea = new Tarea();
            $newTarea->clase_id = $postData['idClase'];
            $newTarea->nombre = $postData['nombreTarea'];
            $newTarea->descripcion = $postData['descripcionTarea'];
            $newTarea->valor = $postData['valor'];
            $newTarea->fecha = date("Y-m-d");
            $newTarea->fechaLimite = $postData['fechaLimite'];
            $newTarea->save();
            return new RedirectResponse('/soe/profesor/'.$postData['idClase']);
        }//addTarea

        function getTareaDetail($request)
        {
            $idClase = $request->getAttribute('idClase');;
            $idTarea = $request->getAttribute('idTarea');
            $dbMateria = $this->getMateriaName($idClase);
            $dbSecuencia = $this->getSecuenciaClave($idClase);
            $dbTarea = Tarea::find($idTarea);
            return $this->renderHTML('teacherTareaDetail.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'tarea' => $dbTarea
			]);
        }//getTareaDetail

        function deleteTarea($request)
        {
            $idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbTarea = Tarea::find($idTarea);
            if($dbTarea)//la tarea existe
            {
                $dbTarea->delete();
                return new RedirectResponse('/soe/profesor/'.$idClase);
            }else{
                return new RedirectResponse('/soe/profesor/'.$idClase);
            }
        }

    }