<?php
/**
 * Created by PhpStorm.
 * User: Nicolas
 * Date: 17.07.14
 * Time: 08:54
 */

namespace Modules\Service;

class RestRouter {

	protected $request;

	protected $controller;

	protected $controllerName;
	protected $applicationName;
	protected $action;
	protected $id = array();
	protected $idCount = 0;

	public function __construct(\Core\Request $request) {
		$this->request = $request;
	}

	public function getControllerData($routes) {
		$uri = $this->request->getVar('uri', 'uri');
		$data = array();

		if(preg_match('#^api/(\w+)[/]*(\d*)[/]*(\w*)[/]*(\d*)[/]*(\w*)$#i', $uri, $match)) {
			$this->getData($match, $routes);

			$data['controllerName'] = $this->controllerName;
			switch(strtolower($this->request->getVar('server', 'REQUEST_METHOD', 'string'))) {
				case 'get':
					if(empty($this->action)) {
						if(!empty($this->id[$this->idCount])) {
							$data['action'] = 'get';
						} else {
							$data['action'] = 'getAll';
						}
					} else {
						$data['action'] = $this->action;
					}
					break;
				case 'post':
					if(empty($this->action)) {
						if(empty($this->id[$this->idCount])) {
							$data['action'] = 'create';
						} else {
							$data['action'] = 'update';
						}
					} else {
						$data['action'] = $this->action;
					}
					break;
				case 'delete':
					if(!empty($this->id[$this->idCount])) {
						$data['action'] = 'delete';
					}
					break;
			}
			$data['id'] = $this->id;
		}

//		var_dump( $data );

		if(!isset($data['controllerName'])) {
			throw new \Exception('Keine Klasse zur Uri:' . $uri . ' gefunden');
		}

		return $data;
	}

	protected function getData($match, $routes) {
		$this->id[] = $match[2];
		$this->id[] = $match[4];

		$data1 = strtolower($match[1]);
		$data2 = strtolower($match[3]);
		$data3 = strtolower($match[5]);

		if(isset($routes[$data1 . '/' . $data2])) {
			$temp = $routes[$data1 . '/' . $data2];

			$this->controllerName = $temp['controller'];
			if(in_array($data3, $temp['action'])) {
				$this->action = $data3;
			}
			$this->idCount = 1;
		} elseif(isset($routes[$data1])) {
			$temp = $routes[$data1];

			$this->controllerName = $temp['controller'];
			if(in_array($data2, $temp['action'])) {
				$this->action = $data2;
			}
		}

	}

}