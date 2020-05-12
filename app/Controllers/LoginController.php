<?php
	namespace App\Controllers;

	/**
	 * 
	 */
	class LoginController extends BaseController
	{
		function getLogin($request)
		{
			return $this->renderHTML('index.twig');
		}
	}