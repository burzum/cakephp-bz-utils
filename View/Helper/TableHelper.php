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

/**
 * TableHelper
 *
 * See the unit test case testTableHelper() for an example of how to use this helper.
 * 
 * The benefits of this helper are no more repeating the foreach looping for the data,
 * the even/odd row coloring, it produces consistant error free html. The example in the 
 * unit test might look like more lines of code but it is less code than writing all of
 * what the code does by hand.
 *
 * @package		BzUtils
 * @subpackage	BzUtils.View.Helper
 */
class TableHelper extends AppHelper {
/**
 * Helpers
 *
 * @var array
 */
	public $helpers = array('Html');

/**
 * Html output
 *
 * @var string
 */
	public $html = '';

/**
 * Array of cell callback options
 *
 * @var array
 */
	public $cells = array();

/**
 * Method changing
 *
 * @var boolean
 */
	public $chaining = true;

/**
 * Default Constructor
 *
 * @param View $View The View this helper is being attached to.
 * @param array $settings Configuration settings for the helper.
 */
	public function __construct(View $View, $settings = array()) {
		$defaults = array(
			'chaining' => true);
		$settings = array_merge($defaults, $settings);
		$this->chaining = $settings['chaining'];

		parent::__construct($View, $settings);
	}

/**
 * Resets properties
 *
 * Resets these properties:
 * - html
 * - cells 
 *
 * @return void
 */
	public function reset() {
		$this->html = '';
		$this->cells = array();
	}

/**
 * Resets the cells property
 *
 * @return object $this
 */
	public function resetCells() {
		$this->cells = array();
		return $this;
	}

/**
 * Adds a cell description to the cells array
 * 
 * @param
 * @param 
 * @param 
 * @return
 */
	public function cell($string, $callback = null, $options = array()) {
		$arrayPath = '';
		$cellType = 'td';
		$value = '';

		if (is_array($callback)) {
			$options = $callback;
			$callback = null;
		}

		if (isset($options['cellType'])) {
			if (!in_array($options['cellType'], array('td', 'th'))) {
				throw InvalidArgumentException(__d('BzUtils', 'Invalid cell type %s. Use td or th.'));
			}
			$cellType = $options['cellType'];
			unset($options['cellType']);
		}

		if (isset($options['arrayPath']) && $options['arrayPath'] == false) {
			$value = $string;
		} else {
			$arrayPath = $string;
		}

		$this->cells[] = array(
			'value' => $value,
			'arrayPath' => $string,
			'callback' => $callback,
			'options' => $options,
			'cellType' => $cellType);

		if ($this->chaining) {
			return $this;
		}
	}

/**
 * Convenience method to create a cell with string content
 *
 * @param string $string
 * @param array $options
 * @return void
 */
	public function simpleCell($string, $options = array()) {
		$options = array_merge(array('arrayPath' => false), $options);
		$this->cell($string, $options);
		if ($this->chaining) {
			return $this;
		}
	}

/**
 * Gets a value from a multi-level array based on a string path
 * 
 * @param Path to the value stored in the array in this syntax: FirstLevel.SecondLevel
 * @return mixed
 */
	public function arrayPath($array, $path) {
		$arrayPath = explode('.', $path);
		$count = count($arrayPath) - 1;
		$value = $array;
		foreach ($arrayPath as $path) {
			if (isset($value[$path])) {
				$value = $value[$path];
			} else {
				return false;
			}
		}
		return $value;
	}

/**
 * Render cells
 * 
 * @param string cell type, tr or td. Overrides the cells settings
 * @return string
 */
	protected function __renderCells($cellType = null) {
		$string = '';
		foreach ($this->cells as $cell) {
			if (!empty($cellType)) {
				$cell['cellType']  = $cellType;
			}
			if (empty($cell['value'])) {
				$value = h($this->arrayPath($this->rowData, $cell['arrayPath']));
			} else {
				$value = $cell['value'];
			}
			if (empty($cell['callback'])) {
				$string .= $this->{$cell['cellType']}($value, $cell['options']);
			} else {
				$string .= $this->{$cell['cellType']}($cell['callback']($value, $this->_View), $cell['options']);
			}
		}
		return $string;
	}

/**
 * Convenience method to render a single row for headers or footers
 *
 * @param array $options
 * @return 
 **/
	public function renderRow($options = array()) {
		return $this->renderRows(array('empty'), $options);
	}

/**
 * Renders the rows based on the provided callbacks and a data array
 *
 * @param array
 * @param array
 * @return string
 */
	public function renderRows($data, $options = array()) {
		$defaultOptions = array(
			'callback' => false,
			'wrapper' => false,
			'wrapperOptions' => array(),
			'modulus' => 2,
			'evenClass' => 'even',
			'oddClass' => false,
			'wrapper' => false,
			'resetCells' => true,
			'cellType' => null);
		$options = array_merge($defaultOptions, $options);

		$this->data = $data;
		$rows = '';
		$i = 0;

		foreach ($this->data as $key => $row) {
			$this->rowKey = $key;
			$this->rowData = $row;
			$this->rowIsEven = true;
			$this->rowNumber = $i;

			if ($i % $options['modulus']) {
				$this->rowIsEven = false;
			}

			$trOptions = array();
			if ($this->rowIsEven && $options['evenClass'] !== false) {
				$trOptions['class'] = $options['evenClass'];
			}
			if (!$this->rowIsEven && $options['oddClass'] !== false) {
				$trOptions['class'] = $options['oddClass'];
			}

			if (is_callable($options['callback'])) {
				$rows .= $options['callback']($this);
			} else {
				$rows .= $this->tr($this->__renderCells($options['cellType']), $trOptions);
			}

			$i++;
		}

		if ($options['resetCells'] == true) {
			$this->resetCells();
		}

		if ($options['wrapper']) {
			$rows = $this->{$options['wrapper']}($rows, $options['wrapperOptions']);
		}

		$this->html .= $rows;

		if ($this->chaining) {
			return $this;
		}
		return $rows;
	}

/**
 * Echos the table
 *
 * @param 
 * @param 
 * @return void
 */
	public function display($content = array(), $options = array()) {
		if (is_array($content)) {
			$options = $content;
			$content = $this->html;
		}
		$this->html = $this->Html->tag('table', $content, $options);
	
		if ($this->chaining) {
			echo $this->html;
			$this->reset();
			return;
		}

		$return = $this->html;
		$this->reset();
		return $return;
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function tr($content, $options = array()) {
		return $this->Html->tag('tr', $content, $options) . "\n";
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function td($content, $options = array()) {
		return $this->Html->tag('td', $content, $options);
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function th($content, $options = array()) {
		return $this->Html->tag('th', $content, $options);
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function thead($content, $options = array()) {
		return $this->Html->tag('thead', $content, $options) . "\n";;
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function tbody($content, $options = array()) {
		return $this->Html->tag('tbody', $content, $options) . "\n";;
	}

/**
 * Convenience method
 *
 * @param string $content
 * @param array $options
 * @return string
 */
	public function tfoot($content, $options = array()) {
		return $this->Html->tag('tfoot', $content, $options) . "\n";
	}

}