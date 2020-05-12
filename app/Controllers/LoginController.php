<?php
	namespace App\Controllers;

	use Laminas\Diactoros\Response\RedirectResponse;
	use App\Models\User;
	/**
	 * Controla el acceso al sistema y el logout, ademas imprime
	 el login.
	 */
	class LoginController extends BaseController
	{
		// imprime el html para mostrat la pantalla de login
		function getLogin($request)
		{
			return $this->renderHTML('index.twig');
		}

		//validacion del ususario
		function postLogin($request)
		{
			$postData = $request->getParsedBody();
			$responseMessage = null;

			$user = User::where('email', $postData['email'])->first();

			if ($user) 
			{
				if(password_verify($postData['password'], $user->userPassword))
				{
					$_SESSION['userId'] = $user->idUser;
					return new RedirectResponse('/soe/dashboard');
				}else
				{
					$responseMessage = 'Usuario o Contraseña incorrectos';
				}
			}else
			{
				$responseMessage = 'Usuario o Contraseña incorrectos';
			}

			return $this->renderHTML('index.twig', [
				'responseMessage' => $responseMessage
			]);
		}
	}