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
 * Cache config to use for the URLs
 *
 * @var mixed
 */
	protected static $_cacheConfig = false;

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
 * Sets the cache config.
 *
 * @var mixed
 * @return void
 */
	public static function setCache($cache) {
		self::$_cacheConfig = $cache;
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
 * @param array $data Data for the URL params.
 * @param string $identifier
 * @param array $options
 * @throws RuntimeException
 * @throws InvalidArgumentException
 * @return array
 */
	public static function url($data, $identifier, $options = array()) {
		if (self::$_cacheConfig !== false) {
			$cacheKey = md5(serialize($data) . serialize($options)) . $identifier;
			$url = Cache::read($cacheKey, self::$_cacheConfig);
			if (!empty($url)) {
				return $url;
			}
		}

		if (is_string($identifier)) {
			$preset = self::getTemplate($identifier);
		} elseif (is_array($identifier)) {
			$preset = $identifier;
		} else {
			throw new \InvalidArgumentException(__d('bz_utils', 'Must be string or array!'));
		}

		$url = self::_buildUrlArray($data, $preset);

		if (isset($options['string']) && $options['string'] === true) {
			$fullBase = (isset($options['fullBase']) && $options['fullBase'] === true);
			$url = Router::url($url, $fullBase);
			if (self::$_cacheConfig !== false) {
				Cache::write($cacheKey, $url, self::$_cacheConfig);
			}
			return $url;
		}

		if (self::$_cacheConfig !== false) {
			Cache::write($cacheKey, $url, self::$_cacheConfig);
		}
		return $url;
	}

/**
 * Builds the actual URL array.
 *
 * @param array $data Data for the URL params.
 * @param array $preset Preset array.
 * @return array
 */
	protected static function _buildUrlArray($data, $preset) {
		if (is_callable($preset['fieldMap'])) {
			return $preset['fieldMap']($data, $preset);
		} else {
			$urlVars = array();
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
			return Hash::merge($preset['preset'], $urlVars);
		}
	}

/**
 * Convenience method to get a string $url with full base path.
 *
 * @param array $data Data for the URL params.
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
