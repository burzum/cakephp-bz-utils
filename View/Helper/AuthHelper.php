<?php
App::uses('AppHelper', 'View/Helper');
App::uses('CakeSession', 'Model/Datasource');
/**
 * AuthHelper
 *
 * @author Florian Krämer
 * @copyright 2013 - 2014 Florian Krämer
 * @license MIT
 */
class AuthHelper extends AppHelper {

/**
 * Default settings
 *
 * @var array
 */
	public $defaults = array(
		'session' => false,
		'viewVar' => 'userData',
		'roleField' => 'role'
	);

/**
 * Constructor
 *
 * @param View $View
 * @param array $settings
 * @return AuthHelper
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		$settings = Hash::merge($this->defaults, $settings);

		if (is_string($settings['session'])) {
			$this->userData = CakeSession::read($settings['session']);
		} else {
			$this->userData = $this->_View->viewVars[$settings['viewVar']];
		}

		$this->settings = $settings;
	}

/**
 * Checks if a user is logged in
 *
 * @return boolean
 */
	public function isLoggedin() {
		return (!empty($this->userData));
	}

/**
 * This check can be used to tell if a record that belongs to some user is the
 * current logged in user
 *
 * @param string|integer $userId
 * @param string $field Name of the field in the user record to check against, id by default
 * @return boolean
 */
	public function isMe($userId, $field = 'id') {
		return ($userId === $this->user($field));
	}

/**
 * Method equal to the AuthComponent::user()
 *
 * @param string $key
 * @return mixed
 */
	public function user($key) {
		return Hash::get($this->userData, $key);
	}

/**
 * Convinence method to compare user data
 *
 * @param string $key
 * @param mixed $value
 * @return boolean
 */
	public function equals($key, $value) {
		return ($this->user($key) === $value);
	}

/**
 * Role check
 *
 * @param string
 * @return boolean
 */
	public function hasRole($role) {
		$roles = $this->user($this->settings['roleField']);
		if (is_string($roles)) {
			return ($role === $roles);
		}
		if (is_array($roles)) {
			return (in_array($role, $role));
		}
		return false;
	}

}
