<?php 
	
	class Controller{
		
		protected function model($model){
			require_once '../app/model/' . $model . '.php';
			return new $model;
		}
		
		protected function view($view, $data = []){
			require_once '../app/view/' . $view . '.php';
		}
	}
