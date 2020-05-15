<?php
	namespace App\Controllers;

	use App\Models\Subject;
	use Respect\Validation\Validator as v;
	use Laminas\Diactoros\Response\RedirectResponse;
	/**
	 * 
	 */
	class AdminMateriaController extends BaseController
	{
		// imprime el panel de control de materia
		function getAdminAddMateriaForm($request)
		{
			return $this->renderHTML('adminMateriaAdd.twig', [
				'username' => $_SESSION['userName']
			]);
		}


		// Da de alta una materia
		function adminAddMateria($request)
		{
			$responseMessage = null;

			if($request->getMethod() == 'POST')
			{
				$postData = $request->getParsedBody();

				$projectValidator = v::key('subject', v::stringType()->notEmpty());
                
                try
                {					
                	$projectValidator->assert($postData);

                	$dbSubject = Subject::where('subjectName', $postData['subject'])
                	->first();
                	
                	
                	if(!$dbSubject)
                	{
	                	$subject = new Subject();
	                	$subject->subjectName = $postData['subject'];
						$subject->save();
						$responseMessage = 'Materia registrada exitosamente';
                	}else
                	{
                		$responseMessage = 'Ya existe esa materia';
                	}

                }catch(\Exception $e)
                {
                	$responseMessage = 'Porfavor rellene todos los campos';

                }
                return $this->renderHTML('adminMateriaAdd.twig', [
                	'responseMessage' => $responseMessage, 
                	'username' => $_SESSION['userName']
                ]);
			}
		}

		// Imprime lista de materias
		function getAdminMaterias($request)
		{
			$subjects = Subject::all();


			return $this->renderHTML('adminMateriaList.twig', [
				'username' => $_SESSION['userName'],
				'subjects' => $subjects
			]);
		}

		function getAdminMateriasDeleteConfirmation($request){
			$postData = $request->getAttribute('id');

			$subjects = Subject::all();
			$subject = Subject::find($postData);

			return $this->renderHTML('adminMateriaList.twig',[
				'selectedSubject' => $subject,
				'username' => $_SESSION['userName'],
				'subjects' => $subjects
			]);

		}
		function getAdminMateriasDelete($request){
			$postData = $request->getAttribute('id');

			$subject = Subject::find($postData);
			$subject->delete();
			
			// $subjects = Subject::all();

			// return $this->renderHTML('adminMateriaList.twig',[
			// 	'username' => $_SESSION['userName'],
			// 	'subjects' => $subjects,
			// 	'responseMessage' => 'Materia eliminada'
			// ]);

			return new RedirectResponse('/soe/dashboard/materia/list');
		}
	}