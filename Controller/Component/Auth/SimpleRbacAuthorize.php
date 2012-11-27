<?php
/**
 * Copyright 2011, Florian Krämer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011, Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('BaseAuthorize', 'Controller/Component/Auth');

/**
 * Very simple rbac authorize
 *
 * @package		BzUtils
 * @subpackage	BzUtils.Controller.Component.Auth
 */
class SimpleRbacAuthorize extends BaseAuthorize {

/**
 * Authorize a user based on his roles
 *
 * @param array $user The user to authorize
 * @param CakeRequest $request The request needing authorization.
 * @return boolean
 */
	public function authorize($user, CakeRequest $request) {
		$userModel = $this->settings['userModel'];
		extract($this->getConrollerNameAndAction($request));

		$actionMap = $this->getActionMap();
		if (isset($actionMap[$name][$action])) {
			if (in_array('*', $actionMap[$name])) {
				return true;
			}

			if (in_array('*', $actionMap[$name][$action])) {
				return true;
			}

			if (is_string($user['role'])) {
				$user['role'] = array($user['role']);
			}

			foreach ($user['role'] as $role) {
				if (in_array($role, $actionMap[$name][$action])) {
					return true;
				}
			}
		}

		return false;
	}

/**
 * Gets the controller and action, prefixes the controller with the plugin if there is one
 *
 * @param CakeRequest $request
 * @return array
 */
	public function getConrollerNameAndAction(CakeRequest $request) {
		$name = $this->_Controller->name;
		$action = $this->_Controller->action;

		if (!empty($request->params['plugin'])) {
			$name = Inflector::camelize($request->params['plugin']) . '.' . $name;
		}

		return compact('name', 'action');
	}

/**
 * Can be overriden if inherited with a method to fetch this from anywhere, a database for exaple
 *
 * @return array
 */
	public function getActionMap() {
		return (array) Configure::read('SimpleRbac.actionMap');
	}

}
