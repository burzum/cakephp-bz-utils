<?php
App::uses('Controller', 'Controller');
App::uses('SimpleRbacAuthorize', 'BzUtils.Controller/Component/Auth');
App::uses('CakeRequest', 'Network');
App::uses('CakeResponse', 'Network');

class SimpleRbacTestController extends Controller {

	public $name = 'SimpleRbacTest';
}

class SimpleRbacAuthorizeTest extends CakeTestCase {

/**
 * setup
 *
 * @return void
 */
	public function setUp() {
		parent::setUp();
		$this->controller = $this->getMock('SimpleRbacTestController', array('isAuthorized'), array(), '', false);
		$this->components = $this->getMock('ComponentCollection');

		$this->components->expects($this->any())
			->method('getController')
			->will($this->returnValue($this->controller));

		$this->auth = new SimpleRbacAuthorize($this->components);

		$actionMap = array(
			'Rbac.Roles' => array(
				'index' => array('*'),
				'add' => array('admin'),
				'edit' => array()));
		Configure::write('SimpleRbac.actionMap', $actionMap);
	}

/**
 * test failure
 *
 * @return void
 */
	public function testAuthorizeFailure() {
		$this->controller->name = 'Roles';
		$this->controller->action = 'edit';

		$user = array(
			'User' => array(
				'role' => 'admin',
				'id' => '4316da10-4014-4640-8df2-05c2c0a80b96'));

		$request = new CakeRequest('/rbac/roles/edit', false);
		$request->params['plugin'] = 'rbac';

		$this->assertFalse($this->auth->authorize($user, $request));
	}

/**
 * test isAuthorized working.
 *
 * @return void
 */
	public function testAuthorizeSuccess() {
		$user = array(
			'User' => array(
				'role' => 'admin'));

		$this->controller->name = 'Roles';
		$this->controller->action = 'add';
		$request = new CakeRequest('/rbac/roles/add', false);
		$request->params['plugin'] = 'rbac';
		$this->assertTrue($this->auth->authorize($user, $request));

		$this->controller->name = 'Roles';
		$this->controller->action = 'index';
		$request = new CakeRequest('/rbac/roles/index', false);
		$request->params['plugin'] = 'rbac';
		$this->assertTrue($this->auth->authorize($user, $request));
	}

/**
 * test isAuthorized with custom roleField.
 *
 * @return void
 */
	public function testAuthorizeCustomRoleField() {
		$user = array(
			'User' => array(
				'anotherRoleField' => 'admin'));

		$this->controller->name = 'Roles';
		$this->controller->action = 'add';
		$request = new CakeRequest('/rbac/roles/add', false);
		$request->params['plugin'] = 'rbac';
		$this->assertFalse($this->auth->authorize($user, $request));

		$this->auth->settings['roleField'] = 'anotherRoleField';
		$this->assertTrue($this->auth->authorize($user, $request));
	}

}
