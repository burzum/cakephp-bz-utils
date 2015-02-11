<?php
App::uses('Configure', 'Core');
App::uses('Utility', 'Hash');
App::uses('Router', 'Router');

class TemplateUrl {

/**
 * Template list.
 *
 * @var array
 */
	protected static $_templates = [];

/**
 * Loads presets from the configuration.
 *
 * @param string $configKey Config key to load the templates from.
 * @return void
 */
	public static function loadPresetsFromConfig($configKey = 'App.linkMap') {
		self::$_templates = Hash::merge(self::$_templates, (array)Configure::read($configKey));
	}

/**
 * Get a template.
 *
 * @param string $identifier Identifier of the preset.
 * @throws \RuntimeException
 * @return array
 */
	public static function getTemplate($identifier) {
		if (!isset(self::$_templates[$identifier])) {
			throw new \RuntimeException(sprintf('No preset for identifier "%s" present!', $identifier));
		}
		return self::$_templates[$identifier];
	}

/**
 * Add a preset.
 *
 * @param string $identifier Identifier of the preset.
 * @param array $preset The preset configuration.
 * @return array
 */
	public function addTemplate($identifier, array $preset = array()) {
		self::$_templates[$identifier] = $preset;
	}

/**
 * Builds an URL array based on a preset.
 *
 * @param array $data
 * @param string $identifier
 * @param array $options
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @return array
 */
	public static function url($data, $identifier, $options = array()) {
		if (is_string($identifier)) {
			$preset = self::getTemplate($identifier);
		} elseif (is_array($identifier)) {
			$preset = $identifier;
		} else {
			throw new \InvalidArgumentException(__d('bz_utils', 'Must be string or array!'));
		}
		$urlVars = array();
		if (is_callable($preset['fieldMap'])) {
			$preset['preset'] = $preset['fieldMap']($data, $preset);
		} else {
			foreach ($preset['fieldMap'] as $urlVar => $field) {
				if (isset($preset['alias'])) {
					$field = str_replace('{alias}', $preset['alias'], $field);
				}
				$result = Hash::get($data, $field);
				if (!is_null($result)) {
					$urlVars[$urlVar] = $result;
				} else {
					throw new \RuntimeException(__d('bz_utils', 'Missing field %s!', $field));
				}
			}
		}
		$url = Hash::merge($preset['preset'], $urlVars);
		if (isset($options['string']) && $options['string'] === true) {
			$fullBase = (isset($options['fullBase']) && $options['fullBase'] === true);
			return Router::url($url, $fullBase);
		}
		return $url;
	}

/**
 * Convenience method to get a string $url with full base path.
 *
 * @param array $data
 * @param string $identifier
 * @param boolean
 * @return string
 */
	public static function stringUrl($data, $identifier, $fullBase = false) {
		return self::url($data, $identifier, array(
			'string' => true,
			'fullBase' => $fullBase
		));
	}
}
