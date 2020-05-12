<?php
	namespace App\Controllers;

	/**
	 * 
	 */
	class AdminDashController extends BaseController
	{
		
		function getAdminDashboard($request)
		{
			return $this->renderHTML('adminDashboard.twig');
		}
	}