<?php 
	namespace App\Controllers;

	use App\Models\Subject;
	use App\Models\Secuencia;
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

			$validator = v::key('clave', v::notEmpty()->noWhitespace()->length(1,15)->uppercase()); 

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
						echo "guardado";
					
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
	}
