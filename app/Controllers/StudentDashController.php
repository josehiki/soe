<?php
	namespace App\Controllers;

	/**
	 * 
	 */
	class StudentDashController extends BaseController
	{
		
		function getStudentDashboard()
		{
			return $this->renderHTML('studentDashboard.twig');
		}
	}