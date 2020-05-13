<?php
	namespace App\Controllers;

	/**
	 * 
	 */
	class TeacherDashController extends BaseController
	{
		function getTeacherDashboard($request){
			return $this->renderHTML('teacherDashboard.twig');
		}
	}