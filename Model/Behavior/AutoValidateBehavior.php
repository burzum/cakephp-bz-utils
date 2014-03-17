<?php
App::uses('ModelBehavior', 'Model');

/**
 * AutoValidateBehavior
 *
 * @author Florian Krämer
 * @copyright 2013 Florian Krämer
 * @license MIT
 */
class AutoValidateBehavior extends ModelBehavior {

/**
 * beforeValidate
 *
 * @param Model $Model
 * @return boolean
 */
	public function beforeValidate(Model $Model) {
		$this->generateValidationRules($Model);
		return true;
	}

/**
 * generateValidationRules
 *
 * @param Model $Model
 * @param array
 * @return void
 */
	public function generateValidationRules(Model $Model) {
		$schema = $Model->schema();
		foreach ($schema as $field => $meta) {
			if ($field === $Model->primaryKey) {
				continue;
			}

			if ($meta['null'] === false) {
				$Model->validate[$field]['notEmpty'] = array(
					'rule' => 'notEmpty',
					'empty' => false,
					'message' => __('This field can not be empty.'),
				);
			}

			if ($meta['type'] === 'boolean') {
				$Model->validate[$field]['boolean'] = array(
					'rule' => 'boolean',
					'empty' => false,
					'message' => __('This field can not be empty.'),
				);
			}

			if ($meta['type'] === 'string') {
				$Model->validate[$field]['length'] = array(
					'rule' => array('length', $meta['length']),
					'empty' => false,
					'message' => __('This field can not be longer than %d.', $meta['length']),
				);
			}
		}
	}

}