<?php
	namespace App\Controllers;

	use App\Models\Subject;
	use Respect\Validation\Validator as v;
	/**
	 * 
	 */
	class AdminMateriaController extends BaseController
	{
		function getAdminAddMateriaForm($request)
		{
			return $this->renderHTML('adminMateriaAdd.twig', [
				'username' => $_SESSION['userName']
			]);
		}

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
	}