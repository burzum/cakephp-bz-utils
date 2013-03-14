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
		$roleField = $this->settings['roleField'];

		if (is_string($user[$roleField])) {
			$user[$roleField] = array($user[$roleField]);
		}

		if ($this->authorizeByPrefix($user[$roleField], $request)) {
			return true;
		}

		if ($this->authorizeByControllerAndAction($user, $request)) {
			return true;
		}

		return false;
	}


/**
 * Checks if a role is granted access to a controller and action
 *
 * @param array $user
 * @param CakeRequest $request
 * @return boolean
 */
	public function authorizeByControllerAndAction($user, CakeRequest $request) {
		$roleField = $this->settings['roleField'];
		extract($this->getConrollerNameAndAction($request));
		$actionMap = $this->getActionMap();
		if (isset($actionMap[$name])) {
			if (in_array('*', $actionMap[$name])) {
				return true;
			}
		}

		if (isset($actionMap[$name][$action])) {
			if (in_array('*', $actionMap[$name][$action])) {
				return true;
			}

			foreach ($user[$roleField] as $role) {
				if (in_array($role, $actionMap[$name][$action])) {
					return true;
				}
			}
		}

		return false;
	}

/**
 * Checks if a role is granted access to a prefix route like /admin
 *
 * @param array $roles
 * @param CakeRequest $request
 * @return boolean
 */
	public function authorizeByPrefix($roles, CakeRequest $request) {
		$prefixeMap = $this->getPrefixMap();
		if (isset($request->params['prefix']) && isset($prefixeMap[$request->params['prefix']])) {
			foreach ($roles as $role) {
				if (in_array($role, $prefixeMap[$request->params['prefix']])) {
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

/**
 * Can be overriden if inherited with a method to fetch this from anywhere, a database for exaple
 *
 * @return array
 */
	public function getPrefixMap() {
		return (array) Configure::read('SimpleRbac.prefixMap');
	}

}
