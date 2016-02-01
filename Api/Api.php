<?php

	require_once "models/User.php";
	require_once "models/Anesthesia.php";
	require_once "models/Record.php";
	require_once "HttpStatusCode.php";
	
	class Api
	{
		private $modelClass, $result;
		
		function __construct($modelClass)
		{
			$this->modelClass = $modelClass;
			
			if($this->authenticate())
			{
				$this->result = $this->processRequest();
			}
			else
			{
				http_response_code(HttpStatusCode::UNAUTHORIZED);
				$this->result = HttpStatusCode::getMessage(HttpStatusCode::UNAUTHORIZED);
			}
		}
		
		protected function authenticate()
		{
			$username = isset($_GET["username"]) ? $_GET["username"] : null;
			$accessToken = isset($_GET["accessToken"]) ? $_GET["accessToken"] : null;
			
			if(isset($username))
			{
				$user = User::getByUsername($username);
				
				if(isset($user) && $user->accessToken == $accessToken)
					return true;
			}
			
			return false;
		}
		
		public function getResult()
		{
			return json_encode($this->result);
		}
		
		protected function processRequest()
		{
			$request = $_SERVER["REQUEST_METHOD"];
			$id = isset($_GET["id"]) ? $_GET["id"] : null;
			
			$value = isset($_POST["value"]) ? $_POST["value"] : null;
			
			if($request == "GET")
				return (empty($id)) ? $this->get() : $this->getById($id);
			elseif($request == "POST")
				return $this->post($value);
			elseif($request == "DELETE")
				return $this->delete($id);
			
			http_response_code(HttpStatusCode::NOT_IMPLEMENTED);
			return HttpStatusCode::getMessage(HttpStatusCode::NOT_IMPLEMENTED);
		}
		
		protected function get()
		{
			$class = $this->modelClass;
			return $class::get();
		}
		
		protected function getById($id)
		{
			$class = $this->modelClass;
			$model = $class::getById($id);
			
			if($model != null)
			{
				return $model;
			}
			else
			{
				http_response_code(HttpStatusCode::NOT_FOUND);
				return HttpStatusCode::getMessage(HttpStatusCode::NOT_FOUND);
			}
		}
		
		protected function post($value)
		{
			if(isset($value))
			{
				
				$class = $this->modelClass;
				$model = new $class();
				$model->initFromJson($value);
				
				if($model->save())
				{
					http_response_code(HttpStatusCode::ACCEPTED);
					return HttpStatusCode::getMessage(HttpStatusCode::ACCEPTED);
				}
			}
			
			http_response_code(HttpStatusCode::NOT_ACCEPTABLE);
			return HttpStatusCode::getMessage(HttpStatusCode::NOT_ACCEPTABLE);
		}
		
		protected function delete($id)
		{
			$class = $this->modelClass;
			$model = $class::getById($id);
			
			if(isset($model) && $model->delete())
			{
				http_response_code(HttpStatusCode::ACCEPTED);
				return HttpStatusCode::getMessage(HttpStatusCode::ACCEPTED);
			}
				
			http_response_code(HttpStatusCode::NOT_ACCEPTABLE);
			return HttpStatusCode::getMessage(HttpStatusCode::NOT_ACCEPTABLE);
		}
	}
?>