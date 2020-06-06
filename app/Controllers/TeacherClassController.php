<?php
    namespace App\Controllers;
    
    use App\Models\User;
	use App\Models\User_Rel;
	use App\Models\Rel_Sec_Sub;
	use App\Models\Subject;
	use App\Models\Secuencia;
    use App\Models\Tarea;
    use App\Models\Entrega;
    use Laminas\Diactoros\Response\RedirectResponse;

    
    class TeacherClassController extends BaseController
    {
        function getClaseDetail($request) //carga las tareas de una materia
        {
            $postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            $dbSecuencia = $this->getSecuenciaClave($postData);
            $dbTareas = Tarea::where('clase_id', $postData)
            ->orderBy('fechaLimite', 'desc')
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
            $newTarea->tipo = 'tarea';
            $newTarea->valor = $postData['valor'];
            $newTarea->fecha = date("Y-m-d");
            $newTarea->fechaLimite = $postData['fechaLimite'];
            $newTarea->save();
            return new RedirectResponse('/soe/profesor/'.$postData['idClase']);
        }//addTarea

        function getTareaDetail($request)
        {
            $idClase = $request->getAttribute('idClase');
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
        }//deleteTarea

        function editTareaForm($request){
            $idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbTarea = Tarea::find($idTarea);
            return $this->renderHTML('teacherTareaEdit.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'tarea' => $dbTarea
			]); 
        }//editTareaForm

        function editTarea($request)
        {
            $postData = $request->getParsedBody();
            
            $editedTarea = Tarea::find($postData['idTarea']);
            $editedTarea->nombre = $postData['nombreTarea'];
            $editedTarea->descripcion = $postData['descripcionTarea'];
            $editedTarea->valor = $postData['valor'];
            $editedTarea->fechaLimite = $postData['fechaLimite'];
            $editedTarea->save();
            return new RedirectResponse('/soe/profesor/tarea/'.$postData['idClase'].'/d/'.$postData['idTarea']);
        }//editTarea

        function getNuevoAnuncioForm($request)
        {
            $postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            return $this->renderHTML('teacherAnuncioAdd.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $postData,
                'materia' => $dbMateria
			]);
        }//getNuevoAnuncioForm

        function addAnuncio($request)
        {
            $postData = $request->getParsedBody();
            
            $newTarea = new Tarea();
            $newTarea->clase_id = $postData['idClase'];
            $newTarea->nombre = $postData['nombreAnuncio'];
            $newTarea->descripcion = $postData['descripcionAnuncio'];
            $newTarea->tipo = 'anuncio';
            $newTarea->fecha = date("Y-m-d");
            $newTarea->fechaLimite = date("Y-m-d");
            $newTarea->save();
            return new RedirectResponse('/soe/profesor/'.$postData['idClase']);
        }//addAnuncio

        function getAnucioDetail($request)
        {
            $idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbMateria = $this->getMateriaName($idClase);
            $dbSecuencia = $this->getSecuenciaClave($idClase);
            $dbTarea = Tarea::find($idTarea);
            return $this->renderHTML('teacherAnuncioDetail.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'tarea' => $dbTarea
			]);
        }

        function editAnuncioForm($request)
        {
            $idClase = $request->getAttribute('idClase');
            $idTarea = $request->getAttribute('idTarea');
            $dbTarea = Tarea::find($idTarea);
            return $this->renderHTML('teacherAnuncioEdit.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'tarea' => $dbTarea
			]); 
        }// editAnuncioForm

        function editAnuncio($request)
        {
            $postData = $request->getParsedBody();
            
            $editedTarea = Tarea::find($postData['idTarea']);
            $editedTarea->nombre = $postData['nombreTarea'];
            $editedTarea->descripcion = $postData['descripcionTarea'];
            $editedTarea->save();
            return new RedirectResponse('/soe/profesor/anuncio/'.$postData['idClase'].'/d/'.$postData['idTarea']);
        }//editAnuncio

        function getStudentsList($request)
        {
            $postData = $request->getAttribute('idClase');
            $dbMateria = $this->getMateriaName($postData);
            $dbSecuencia = $this->getSecuenciaClave($postData);

            $auxlistStudent = User_Rel::where('rel_id', $postData)->get(); //lista de alumnos que pertenecen a la clase
            
            $listStudent; //lista de alumnos - salida
            $alumno; //auxiliar 
            foreach ($auxlistStudent as $student) //minentras la lista de alumnos
            {
                if ($student->user_id != $_SESSION['userId']) { // si el alumno no es el profesor de la clase 
                    $auxAlumno = User::find($student->user_id); //Busca info del alumno 
                    $alumno=[
                        'nombreAlumno' => $auxAlumno->userName,
                        'boleta' => $auxAlumno->boleta
                    ]; //guarda informacion en el auxiliar 
                    $listStudent[] = $alumno; // agrega el auxiliar a la lista 
                }
            }
            return $this->renderHTML('teacherStudentsList.twig', [
                'username' => $_SESSION['userName'],
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'idClase' => $postData,
                'listAlumnos' => $listStudent
            ]);

        }// getStudentsList

        function getEntregasList($request)
        {
            $idClase = $request->getAttribute('idClase'); 
            $idTarea = $request->getAttribute('idTarea');

            $dbMateria = $this->getMateriaName($idClase); // Recupera el nombre de la materia
            $dbSecuencia = $this->getSecuenciaClave($idClase); // Recupera la clave de la secuecia 

            $auxlistStudent = User_Rel::where('rel_id', $idClase)->get(); //lista de alumnos que pertenecen a la clase
            $listStudent; //lista de alumnos - salida
            $alumno; //auxiliar 
            foreach($auxlistStudent as $student) //mientras la lista de alumnos
            {
                if ($student->user_id != $_SESSION['userId']) // si el usuario no es el profesor de la clase 
                { 
                    $auxAlumno = User::find($student->user_id); //Busca info del alumno 
                    $auxEntrega = Entrega::where('idTarea', $idTarea)
                    ->where('idUsuario', $student->user_id)->first(); // Busca una entrega de la tarea por parte del usuario (alumno)
                    $auxTarea = Tarea::find($idTarea); //aux de tarea 
                    if($auxEntrega)// realizo una entrega 
                    {  
                        if($auxEntrega->fecha > $auxTarea->fecha) // realizo la tarea de forma tardia
                        {
                            $alumno=[
                                'nombreAlumno' => $auxAlumno->userName,
                                'entrego' => true,
                                'tardio' => true,
                                'fecha' => $auxEntrega->fecha,
                                'url' => $auxEntrega->urlEntrega
                            ]; //guarda informacion en el auxiliar 
                        }else{
                            $alumno=[
                                'nombreAlumno' => $auxAlumno->userName,
                                'entrego' => true,
                                'fecha' => $auxEntrega->fecha,
                                'url' => $auxEntrega->urlEntrega
                            ]; //guarda informacion en el auxiliar     
                        }
                    }else{ // No entrego
                        $alumno=[
                            'nombreAlumno' => $auxAlumno->userName,
                            'entrego' => false
                        ];
                    }
                    $listStudent[] = $alumno; // agrega el auxiliar a la lista de alumnos
                }
            }            
            return $this->renderHTML('teacherEntregasList.twig', [
                'username' => $_SESSION['userName'],
                'idClase' => $idClase,
                'idTarea' => $idTarea,
                'nombreMateria' => $dbMateria->subjectName,
                'secuencia' => $dbSecuencia->claveSecuencia,
                'listaEntregas' => $listStudent
            ]);
        } // getEntregasList
    }