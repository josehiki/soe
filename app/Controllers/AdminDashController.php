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
	}