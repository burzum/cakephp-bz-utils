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
 * Default settings
 * @var array
 */
	protected $_defaultSettings = array(
		'roleField' => 'role',
	);

/**
 * Constructor
 *
 * @param ComponentCollection $collection The controller for this request.
 * @param string $settings An array of settings. This class does not use any settings.
 */
	public function __construct(ComponentCollection $collection, $settings = array()) {
		parent::__construct($collection, $settings);
		$this->settings = Hash::merge($this->settings, $this->_defaultSettings, $settings);
	}

/**
 * Authorize a user based on his roles
 *
 * @param array $user The user to authorize
 * @param CakeRequest $request The request needing authorization.
 * @return boolean true if the action is configured for all access '*' or there is
 * a role field and the role is defined in the action mapping array
 */
	public function authorize($user, CakeRequest $request) {
		$userModel = $this->settings['userModel'];
		$roleField = $this->settings['roleField'];
		extract($this->getControllerNameAndAction($request));

		$actionMap = $this->getActionMap();

		if (isset($actionMap[$name][$action])) {
			if (in_array('*', $actionMap[$name][$action])) {
				return true;
			}
			if (empty($user[$userModel][$roleField])) {
				return false;
			}
			if (is_string($user[$userModel][$roleField])) {
				$user[$userModel][$roleField] = array($user[$userModel][$roleField]);
			}
			foreach ($user[$userModel][$roleField] as $role) {
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
	public function getControllerNameAndAction(CakeRequest $request) {
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
		return Configure::read('SimpleRbac.actionMap');
	}

}
