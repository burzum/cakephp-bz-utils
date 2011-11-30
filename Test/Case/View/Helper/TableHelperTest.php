<?php
App::uses('Router', 'Routing');
App::uses('View', 'View');
App::uses('TableHelper', 'BzUtils.View/Helper');

/**
 * Short description for class.
 *
 * @package		BzUtils
 * @subpackage	BzUtils.Test.Case.View.Helper
 */
class BzUtilsHelperTest extends CakeTestCase {
/**
 * Test data
 *
 * @var array
 */
	public $testData = array(
		array('User' => array(
			'name' => 'Florian',
			'email' => 'foobar@sample.com',
			'phone' => '35252525',
			'created' => '2011-03-04 12:41:12')),
		array('User' => array(
			'name' => 'CakeDC',
			'email' => 'another@sample.com',
			'phone' => '35252525',
			'created' => '2011-02-21 12:42:12')),
		array('User' => array(
			'name' => 'CakePHP',
			'email' => 'sample@sample.com',
			'phone' => '35252525',
			'created' => '2011-12-01 12:41:12')));

/**
 * Setup
 *
 * @return void
 */
	public function setUp() {
		ClassRegistry::flush();
		Router::reload();
		$null = null;
		$this->View = new View(null);
		$this->View->Helpers->load('Time');
		$this->View->Helpers->load('Html');
		$this->View->Helpers->load('Paginator');
		$this->Table = new TableHelper($this->View);
	}

/**
 * Testing the rendering of a table
 *
 * @return void
 */
	public function testTableHelper() {
		ob_start();

		$this->Table
			->simpleCell('Name')
			->simpleCell('Phone')
			->simpleCell('Email')
			->simpleCell('Created')
			->renderRow(array(
				'cellType' => 'th',
				'wrapper' => 'thead',
				'wrapperOptions' => array(
					'class' => 'test-wrapper',
					'id' => 'head')))
			->cell('User.name')
			->cell('User.phone')
			->cell('User.email', function($value, $View) {
				return $View->Html->link($value, 'mailto:' . $value);
			})
			->cell('User.created', function($value, $View) {
				return $View->Time->format('Y-m-d', $value);
			})
			->renderRows($this->testData, array(
				'wrapper' => 'tbody',
				'wrapperOptions' => array(
					'class' => 'test-wrapper',
					'id' => 'body')))
			->simpleCell('Footer')
			->simpleCell('-')
			->simpleCell('&nbsp')
			->simpleCell('Footer')
			->renderRow(array(
				'wrapper' => 'tfoot'))
			->display(array(
				'class' => 'test-table'));

			$result = ob_get_contents();
			ob_end_clean();

			$expected = '<table class="test-table"><thead class="test-wrapper" id="head"><tr class="even"><th>Name</th><th>Phone</th><th>Email</th><th>Created</th></tr>
</thead>
<tbody class="test-wrapper" id="body"><tr class="even"><td>Florian</td><td>35252525</td><td><a href="mailto:foobar@sample.com">foobar@sample.com</a></td><td>2011-03-04</td></tr>
<tr><td>CakeDC</td><td>35252525</td><td><a href="mailto:another@sample.com">another@sample.com</a></td><td>2011-02-21</td></tr>
<tr class="even"><td>CakePHP</td><td>35252525</td><td><a href="mailto:sample@sample.com">sample@sample.com</a></td><td>2011-12-01</td></tr>
</tbody>
<tfoot><tr class="even"><td>Footer</td><td>-</td><td>&nbsp</td><td>Footer</td></tr>
</tfoot>
</table>';
			$this->assertEquals($result, $expected);
	}

}