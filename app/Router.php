<?php

namespace App;

use Exception;
use Throwable;

class Router
{
	public $routes;

	protected $method;

	protected $controller;

	protected $action;

	protected $params = [];

	public function run()
	{
		try {
			$this->routes = explode('/', $_SERVER['REQUEST_URI']);
			$this->parseController();
			$this->parseAction();
			$this->parseParams();

			if (method_exists($this->controller, $this->action)) {

				return call_user_func_array(array($this->controller, $this->action), $this->params);
			}
		} catch (Throwable  $e) {
			return error_response($e->getMessage());
		}
		return error_response('Invalid Method', 405);
	}

	/**
	 * @throws Exception
	 */
	private function parseController()
	{
		if (empty($this->routes[2])) {
			$this->controller = 'Books';
		} else {
			$this->controller = $this->routes[2];
		}
		$this->controller = 'App\\' . ucfirst($this->controller) . 'Controller';
		if (!class_exists($this->controller)) {
			throw new Exception('Undefined class: ' . $this->controller);
		}
		$this->controller = new $this->controller();
	}

	private function parseAction()
	{
		if (empty($this->routes[3])) {
			$this->action = 'index';
		} else {
			if (strpos($this->routes[3], '?') !== false) {
				$params = explode('?', $this->routes[3]);
				$this->action = $params[0];
				$this->routes[4] = $params[1];
			} else {
				$this->action = $this->routes[3];
			}
		}
		$this->action = 'action' . ucfirst($this->action);
	}

	private function parseParams()
	{
		$_POST = file_get_contents('php://input');
		$_POST = json_decode($_POST, true);
		if (!empty($_POST) && !$this->isJson()) {
			error_response('Invalid income parameters. Allowed json only.');
		}
		if (!empty($this->routes[3]) && is_numeric($this->routes[3])) {
			$this->action = 'actionView';
			$this->params = [$this->routes[3]];
		}
		if (!empty($this->routes[4])) {
			if (is_numeric($this->routes[4])) {
				$this->params = [$this->routes[4]];
			} else{
				$this->params = [$_GET];
			}
		}
	}

	function isJson()
	{
		return (json_last_error() == JSON_ERROR_NONE);
	}
}