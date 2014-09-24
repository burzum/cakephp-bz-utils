<?php
/**
 * Copyright 2011 - 2014, Florian Krämer
 *
 * Licensed under The MIT License
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright 2011 - 2014, Florian Krämer
 * @license MIT License (http://www.opensource.org/licenses/mit-license.php)
 */

App::uses('Router', 'Routing');
App::uses('View', 'View');
App::uses('LinkHelper', 'BzUtils.View/Helper');

/**
 * Short description for class.
 *
 * @package		BzUtils
 * @subpackage	BzUtils.Test.Case.View.Helper
 */
class LinkHelperTest extends CakeTestCase {

/**
 * Setup
 *
 * @return void
 */
	public function setUp() {
		$null = null;
		$this->View = new View(null);
		$this->View->Helpers->load('Html');
		$this->Link = new LinkHelper($this->View);
		Configure::write('App.linkMap', array(
			'blogSlug' => array(
				'preset' => array(
					'controller' => 'blog_posts',
					'action' => 'view'
				),
				'alias' => 'BlogPost',
				'fieldMap' => array(
					'id' => '{alias}.id',
					'slug' => '{alias}.slug',
					'categorySlug' => 'Category.slug',
				),
				'titleField' => 'BlogPost.title'
			)
		));
		Router::reload();
		Router::connect('/article/:categorySlug/:slug-:id', array(
			'controller' => 'blog_posts',
			'action' => 'view',
		));
	}

/**
 * Testing the rendering of a table
 *
 * @return void
 */
	public function testTableHelper() {
		$data = array(
			'BlogPost' => array(
				'id' => 123,
				'title' => 'A fancy posting!',
				'slug' => 'a-fancy-posting'
			),
			'Category' => array(
				'slug' => 'cakephp-rocks'
			)
		);
		$expected = array(
			'controller' => 'blog_posts',
			'action' => 'view',
			'id' => 123,
			'slug' => 'a-fancy-posting',
			'categorySlug' => 'cakephp-rocks'
		);
		$result = $this->Link->buildUrl($data, 'blogSlug');
		$this->assertEquals($result, $expected);
		$result = $this->Link->Link($data, 'blogSlug');
		$expected = '<a href="/article/cakephp-rocks/a-fancy-posting-123">A fancy posting!</a>';
		$this->assertEquals($result, $expected);
	}

}