<?php
	namespace App\Controllers;

	use App\Models\User;

	/**
	 * 
	 */
	class AdminDashController extends BaseController
	{
		
		function getAdminDashboard($request)
		{

			return $this->renderHTML('adminDashboard.twig', [
				'username' => $_SESSION['userName']
			]);
		}

		function getAdminMateriaDashboard($request)
		{
			return $this->renderHTML('adminMateriaDash.twig', [
				'username' => $_SESSION['userName']
			]);
		}

		function getAdminSecuenciaDashboard($request)
		{
			return $this->renderHTML('adminSecuenciaDash.twig', [
				'username' => $_SESSION['userName']
			]);
		}
	}