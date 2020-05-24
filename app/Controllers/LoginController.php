<?php
	namespace App\Controllers;

	use Laminas\Diactoros\Response\RedirectResponse;
	use App\Models\User;

	// $dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/../..');
	// $dotenv->load();
	/**
	 * Controla el acceso al sistema y el logout, ademas imprime
	 el login.
	 */
	class LoginController extends BaseController
	{
		// imprime el html para mostrat la pantalla de login
		function getLogin($request)
		{
			session_destroy();
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
				if(password_verify($postData['password'], $user->userPassword) && $user->active)
				{
					$_SESSION['userId'] = $user->idUser;
					$_SESSION['userType'] = $user->userType;
					$_SESSION['userName'] = $user->userName;
					
					try
					{
						if ($user->userType == 'admin') {
							return new RedirectResponse('/soe/dashboard');

						}elseif ($user->userType == 'student') {
							return new RedirectResponse('/soe/student');

						}elseif($user->userType == 'teacher') {
							return new RedirectResponse('/soe/profesor');
						}
					}catch (\Exception $e)
					{
						echo $e;
					}
					
					
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

		function logout($request)
		{
			session_destroy();
			return new RedirectResponse('/soe');
		}
	}