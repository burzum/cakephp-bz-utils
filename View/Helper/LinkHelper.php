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

App::uses('AppHelper', 'View/Helper');
App::uses('TemplateUrl', 'BzUtils.Utility');

class LinkHelper extends AppHelper {

/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array(
		'Html'
	);

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		parent::__construct($View, $settings);
		TemplateUrl::loadPresetsFromConfig();
	}

/**
 * Convenience method to add a title
 *
 * @param string $title
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @return string
 */
	public function titleLink($title, $data, $identifier, $options) {
		$options['linkTitle'] = $title;
		return $this->link($data, $identifier, $options);
	}

/**
 * Creates a link based on a template and data
 *
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @throws RuntimeException
 * @return string
 */
	public function link($data, $identifier, $options = array()) {
		$preset = $this->_getPreset($identifier);
		if (isset($preset['titleField'])) {
			$title = Hash::get($data, $preset['titleField']);
		} elseif (isset($options['linkTitle'])) {
			$title = $options['linkTitle'];
			unset($options['linkTitle']);
		} else {
			throw new RuntimeException(__d('bz_utils', 'Missing title!'));
		}
		if (isset($options['alias'])) {
			$preset['alias'] = $options['alias'];
			unset($options['alias']);
		}
		$url = $this->buildUrl($data, $preset);
		return $this->Html->link($title, $url, $options);
	}

/**
 * Gets a preset configuration array
 *
 * @param string
 * @return array
 */
	protected function _getPreset($identifier) {
		return TemplateUrl::getTemplate($identifier);
	}

/**
 * Builds an URL array based on a preset
 *
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @return array
 */
	public function buildUrl($data, $identifier, $options = array()) {
		return TemplateUrl::url($data, $identifier, $options);
	}

/**
 * Convenience method to get a string $url with full base path
 *
 * @param array $data
 * @param string $identifier
 * @param boolean
 * @return string
 */
	public function stringUrl($data, $identifier, $fullBase = false) {
		return TemplateUrl::stringUrl($data, $identifier, $fullBase);
	}
}